<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentPdfController extends Controller
{
    public function generate(Request $request, $token)
    {
        // Ang URL mo-gamit sa random verification_code, dili sa sequential ID.
        // Ang numeric ID gi-support gihapon para sa daan nga link.
        $requestModel = DocumentRequest::with(['user', 'transactionType'])
            ->where('verification_code', $token)
            ->first();

        if (! $requestModel && is_numeric($token)) {
            $requestModel = DocumentRequest::with(['user', 'transactionType'])->find($token);
        }

        abort_if(! $requestModel, 404);
        $resident = $requestModel->user;

        // Ang resident maka-access ra sa iyang kaugalingon nga dokumento.
        // Ang admin ug super admin maka-access sa tanan.
        $user = $request->user();
        $isStaff = in_array($user->role ?? '', ['admin', 'super_admin'], true);

        if (! $isStaff && $requestModel->user_id !== $user->id) {
            abort(403, 'You are not authorized to view this document.');
        }

        if (empty($requestModel->verification_code) || str_starts_with($requestModel->verification_code, 'VRF-')) {
            $cryptoToken = 'v1_' . bin2hex(random_bytes(20));
            $controlNo = 'BC-' . date('Ymd') . '-' . sprintf('%04d', $requestModel->id);

            $requestModel->update([
                'verification_code' => $cryptoToken,
                'control_number'    => $controlNo,
                'issued_at'         => now(),
            ]);
            $requestModel->refresh();
        }

        $verifyUrl = route('document.verify', ['code' => $requestModel->verification_code]);

        // 👉 QR strategy (capability-based, no GD/Imagick required):
        //   1. simple-qrcode PNG (needs Imagick) — crisp raster, always embeds.
        //   2. Bacon encoder + GD raster — crisp raster via our own pixel painter.
        //   3. simple-qrcode SVG (pure PHP) — rasterized with php-svg-lib.
        // Either way the verification QR always appears on the document.
        $qrPngBase64 = null;
        $qrCodeSvg = null;

        if (extension_loaded('imagick')) {
            try {
                $qrPngBase64 = 'data:image/png;base64,' . base64_encode(
                    QrCode::format('png')->size(300)->generate($verifyUrl)
                );
            } catch (\Throwable $e) {
                $qrPngBase64 = null; // Imagick present but not usable
            }
        }

        if ($qrPngBase64 === null && function_exists('imagecreatetruecolor') && class_exists(\BaconQrCode\Encoder\Encoder::class)) {
            $qrPngBase64 = $this->qrPngDataUri($verifyUrl);
        }

        if ($qrPngBase64 === null) {
            // Last resort: pure-PHP SVG markup from simple-qrcode, inlined by the
            // view — DomPDF draws it through php-svg-lib as vector rectangles,
            // which needs no PHP extensions at all. Sized 52px to match the
            // raster variant's box exactly; the XML declaration is stripped
            // because it is illegal inside an HTML document body.
            $qrCodeSvg = preg_replace(
                '/^\s*<\?xml[^>]*\?>/i',
                '',
                (string) QrCode::size(52)->generate($verifyUrl)
            );
        }

        // 👉 Locate artwork across every plausible deployment layout.
        // Shared hosts (InfinityFree/000webhost style) place the web root NEXT TO
        // the app:  /htdocs/laravel_app (base_path) + /htdocs/images (web root).
        $imageDirs = array_values(array_unique(array_filter([
            public_path('images'),
            base_path('public' . DIRECTORY_SEPARATOR . 'images'),
            base_path('..' . DIRECTORY_SEPARATOR . 'images'),
            base_path('..' . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'images'),
            public_path(),
            base_path('..' . DIRECTORY_SEPARATOR . 'public_html'),
        ], fn ($dir) => is_dir($dir))));

        // JPEG-first: JPEG embeds straight into the PDF *without* the GD
        // extension, so the letterhead keeps rendering even on GD-less hosts.
        // Exception: the footer wave carries transparency — a JPEG's white
        // background would paint over the receipt box and QR control number
        // sitting just above it, so the alpha PNG is preferred there.
        $findImage = function (string $stem, array $extOrder = ['jpg', 'jpeg', 'png']) use ($imageDirs): ?string {
            foreach ($extOrder as $ext) {
                foreach ($imageDirs as $dir) {
                    $path = $dir . DIRECTORY_SEPARATOR . $stem . '.' . $ext;
                    if (is_file($path)) {
                        return $path;
                    }
                }
            }

            return null;
        };

        $artwork = function (string $stem, array $extOrder = ['jpg', 'jpeg', 'png']) use ($findImage): ?string {
            $path = $findImage($stem, $extOrder);
            if ($path === null) {
                return null;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if (in_array($ext, ['jpg', 'jpeg'], true)) {
                return $this->jpegDataUri($path);
            }

            // PNG artwork: try a clean GD re-encode first, then Imagick; raw PNG
            // bytes are never used because DomPDF's Cpdf engine hard-fails on
            // GD-less servers and silently blanks RGBA PNGs.
            return $this->pngDataUri($path) ?? $this->jpegDataUri($path);
        };

        // Letterhead artwork (header / footer / full-page background) + seals.
        $headerImgBase64    = $artwork('barangay-san-jose-header');
        $footerImgBase64    = $artwork('barangay-san-jose-footer', ['png', 'jpg', 'jpeg']);
        $bizBgBase64        = $artwork('business-clearance-bg');
        $barangaySealBase64 = $artwork('barangay-seal');
        $talibonSealBase64  = $artwork('talibon-seal');

        $settings = DB::table('barangay_settings')->pluck('value', 'key')->toArray();

        // Business extraction
        $rawPurpose = $requestModel->purpose ?? '';
        $businessName = '__________';
        $businessNature = 'ADVERTISING';

        if (!empty($rawPurpose)) {
            if (str_contains($rawPurpose, '(') && str_contains($rawPurpose, ')')) {
                $bNamePart = strstr($rawPurpose, '(', true);
                $bNaturePart = strstr(substr(strstr($rawPurpose, '('), 1), ')', true);
                $businessName = strtoupper(trim($bNamePart ?: $rawPurpose));
                $businessNature = strtoupper(trim($bNaturePart ?: 'ADVERTISING'));
            } elseif (str_contains($rawPurpose, ':')) {
                $parts = explode(':', $rawPurpose);
                $businessName = strtoupper(trim(current($parts)));
                $businessNature = strtoupper(trim(end($parts)));
            } else {
                $businessName = strtoupper(trim($rawPurpose));
            }
        }

        // Occupation & Income Resolution
        $occupation = $requestModel->occupation ?: ($resident->employment_status ?? null);
        $monthlyIncome = $requestModel->monthly_income ?: ($resident->monthly_income ?? null);
        $employedSince = $requestModel->employed_since ?: null;
        $hasIncomeCert = !empty($occupation) && !empty($monthlyIncome);

        $data = [
            'requestModel'       => $requestModel,
            'resident'           => $resident,
            'residentAge'        => $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->age : null,
            'civilStatus'        => $resident->civil_status ?? 'Single',
            'citizenship'        => $resident->citizenship ?? 'Filipino',
            'lengthOfStay'       => $resident->length_of_stay ?? null,
            'documentType'       => $requestModel->transactionType->name ?? 'BARANGAY CERTIFICATION',
            'controlNumber'      => $requestModel->control_number,
            'qrCodeSvg'          => $qrCodeSvg,
            'qrPngBase64'        => $qrPngBase64,
            'talibonSealBase64'  => $talibonSealBase64,
            'barangaySealBase64' => $barangaySealBase64,
            'headerImgBase64'    => $headerImgBase64,
            'footerImgBase64'    => $footerImgBase64,
            'bizBgBase64'        => $bizBgBase64,
            'captainName'        => $settings['captain_name'] ?? 'JOSEFINA C. GURREA',
            'secretaryName'      => $settings['secretary_name'] ?? 'HANNAH JOY B. CREDO',
            'barangayAddress'    => $settings['barangay_address'] ?? 'PUROK 5, SAN JOSE, TALIBON, BOHOL',
            'barangayEmail'      => $settings['barangay_email'] ?? 'blgusanjosetalibon1910@gmail.com',
            'barangayFacebook'   => $settings['barangay_facebook'] ?? 'Barangay San Jose - Official',
            'businessName'       => $businessName,
            'businessNature'     => $businessNature,
            'hasIncomeCert'      => $hasIncomeCert,
            'occupation'         => $occupation,
            'monthlyIncome'      => (float) $monthlyIncome,
            'monthlyIncomeWords' => $monthlyIncome ? $this->numberToWords((float)$monthlyIncome) : '',
            'employedSince'      => $employedSince,
        ];

        $typeLower = strtolower($requestModel->transactionType->name ?? '');

        // 👉 BAG-ONG CERTIFICATES (slug-based). Kung walay template, mo-fall through sa daan.
        $slug = $requestModel->transactionType->slug ?? null;
        if (!empty($slug) && view()->exists("pdf.certificates." . $slug)) {
            $pdf = $this->makePdf("pdf.certificates." . $slug, $data);
            $fileName = preg_replace("/[^A-Za-z0-9 _.-]/", "", ($requestModel->transactionType->name ?? "Certificate") . "-" . $resident->last_name);
            return $pdf->stream($fileName . ".pdf");
        }


        if (str_contains($typeLower, 'business')) {
            return $this->makePdf('pdf.business-clearance', $data)
                ->stream('Barangay-Business-Clearance-' . $resident->last_name . '.pdf');
        }

        return $this->makePdf('pdf.certificate', $data)
            ->stream('Barangay-Certificate-' . $resident->last_name . '.pdf');
    }

    /**
     * Build the PDF with DomPDF options tuned for restrictive shared hosts.
     * Key fix: temp_dir must be writable — data-URI and converted images are
     * materialized through temporary files, and on free hosts (InfinityFree /
     * wuaze) sys_get_temp_dir() (/tmp) is NOT writable, which made every image
     * render as alt text. Fall back to the app's own storage, then the system
     * temp dir, whichever is actually writable.
     */
    private function makePdf(string $view, array $data): \Barryvdh\DomPDF\Pdf
    {
        return Pdf::loadView($view, $data)->setOptions(self::dompdfOptions(), true);
    }

    /**
     * Shared DomPDF options (writable temp dir for InfinityFree/wuaze etc.),
     * reused by other PDF-generating controllers (e.g. cash summary).
     */
    public static function dompdfOptions(): array
    {
        $candidates = [
            storage_path('app/dompdf-tmp'),
            storage_path('framework/dompdf-tmp'),
            sys_get_temp_dir() . '/dompdf-' . md5(base_path()),
        ];

        $tempDir = null;
        foreach ($candidates as $dir) {
            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                continue;
            }
            if (is_writable($dir)) {
                $tempDir = $dir;
                break;
            }
        }

        $options = config('dompdf.defines', []);

        if ($tempDir !== null) {
            $options['tempDir'] = $tempDir;
            $options['fontDir'] = $options['fontDir'] ?? $tempDir;
            $options['font_cache'] = $options['font_cache'] ?? $tempDir;
        }

        return $options;
    }

    /**
     * Load an image file and re-encode it as a clean PNG data URI.
     * Returns null when the file is missing or no encoder is available, so
     * views can gracefully fall back instead of printing broken alt text.
     */
    private function pngDataUri(?string $path): ?string
    {
        if (empty($path) || !is_file($path)) {
            return null;
        }

        try {
            if (filesize($path) > 6 * 1024 * 1024) {
                return null;
            }

            // Prefer the GD round-trip (guaranteed DomPDF-compatible output).
            if (function_exists('imagecreatefrompng')) {
                $img = @imagecreatefrompng($path);
                if ($img !== false) {
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                    ob_start();
                    $ok = @imagepng($img, null, 6);
                    imagedestroy($img);
                    if ($ok) {
                        return 'data:image/png;base64,' . base64_encode((string) ob_get_clean());
                    }
                    ob_end_clean();
                }
            }

            // Imagick fallback for hosts without GD.
            if (extension_loaded('imagick')) {
                try {
                    $im = new \Imagick($path);
                    $im->setImageFormat('png');
                    $data = $im->getImageBlob();
                    $im->clear();

                    return 'data:image/png;base64,' . base64_encode($data);
                } catch (\Throwable $e) {
                    // fall through
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    /**
     * Load an image as a JPEG data URI. JPEG is the one raster format DomPDF's
     * Cpdf engine embeds with zero PHP extensions, so artwork converted to
     * JPEG renders on every host — GD or not.
     * PNG inputs are flattened onto white first (transparency would print black).
     */
    private function jpegDataUri(?string $path, int $quality = 90): ?string
    {
        if (empty($path) || !is_file($path)) {
            return null;
        }

        try {
            if (filesize($path) > 6 * 1024 * 1024) {
                return null;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            // Native JPEG: pass the bytes straight through.
            if (in_array($ext, ['jpg', 'jpeg'], true)) {
                $raw = @file_get_contents($path);

                if ($raw !== false && str_starts_with($raw, "\xFF\xD8")) {
                    return 'data:image/jpeg;base64,' . base64_encode($raw);
                }

                return null;
            }

            // PNG input: flatten onto a white matte so alpha never turns black.
            if (function_exists('imagecreatefrompng')) {
                $img = @imagecreatefrompng($path);

                if ($img !== false) {
                    $w = imagesx($img);
                    $h = imagesy($img);
                    $flat = imagecreatetruecolor($w, $h);
                    $white = imagecolorallocate($flat, 255, 255, 255);
                    imagefilledrectangle($flat, 0, 0, $w, $h, $white);
                    imagealphablending($flat, true);
                    imagecopy($flat, $img, 0, 0, 0, 0, $w, $h);

                    ob_start();
                    $ok = @imagejpeg($flat, null, $quality);
                    imagedestroy($flat);
                    imagedestroy($img);

                    if ($ok) {
                        return 'data:image/jpeg;base64,' . base64_encode((string) ob_get_clean());
                    }
                    ob_end_clean();
                }
            }

            // Imagick fallback for hosts without GD.
            if (extension_loaded('imagick')) {
                try {
                    $im = new \Imagick($path);
                    $im->setImageBackgroundColor('white');
                    $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                    $im->setImageFormat('jpeg');
                    $im->setImageCompressionQuality($quality);
                    $data = $im->getImageBlob();
                    $im->clear();

                    return 'data:image/jpeg;base64,' . base64_encode($data);
                } catch (\Throwable $e) {
                    // fall through
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    /**
     * Render a QR code as a true PNG data URI using the Bacon encoder + GD,
     * because DomPDF's SVG support is too limited to draw the SVG variant.
     */
    private function qrPngDataUri(string $content): ?string
    {
        try {
            if (!function_exists('imagecreatetruecolor') || !class_exists(\BaconQrCode\Encoder\Encoder::class)) {
                return null;
            }

            $qr      = \BaconQrCode\Encoder\Encoder::encode(
                $content,
                \BaconQrCode\Common\ErrorCorrectionLevel::forBits(0) // M — good balance of density/robustness
            );
            $matrix  = $qr->getMatrix();
            $modules = $matrix->getWidth();
            $quiet   = 4; // quiet-zone modules around the code
            $scale   = 8; // px per module
            $size    = ($modules + $quiet * 2) * $scale;

            $img = imagecreatetruecolor($size, $size);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 17, 17, 17);
            imagefilledrectangle($img, 0, 0, $size, $size, $white);

            for ($y = 0; $y < $matrix->getHeight(); $y++) {
                for ($x = 0; $x < $modules; $x++) {
                    if ((int) $matrix->get($x, $y) === 1) {
                        imagefilledrectangle(
                            $img,
                            ($x + $quiet) * $scale,
                            ($y + $quiet) * $scale,
                            ($x + $quiet + 1) * $scale - 1,
                            ($y + $quiet + 1) * $scale - 1,
                            $black
                        );
                    }
                }
            }

            ob_start();
            $ok = @imagepng($img, null, 6);
            imagedestroy($img);

            return $ok ? 'data:image/png;base64,' . base64_encode((string) ob_get_clean()) : null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function numberToWords(float $amount): string
    {
        $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens  = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $num = (int) round($amount);
        if ($num <= 0) return 'Zero Pesos';

        $convert = function ($n) use (&$convert, $units, $tens) {
            $str = '';
            if ($n >= 100) {
                $str .= $units[(int)($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $str .= $tens[(int)($n / 10)] . ($n % 10 !== 0 ? '-' . $units[$n % 10] : '');
            } elseif ($n > 0) {
                $str .= $units[$n];
            }
            return trim($str);
        };

        $parts = [];
        if ($num >= 1000000) {
            $parts[] = $convert((int)($num / 1000000)) . ' Million';
            $num %= 1000000;
        }
        if ($num >= 1000) {
            $parts[] = $convert((int)($num / 1000)) . ' Thousand';
            $num %= 1000;
        }
        if ($num > 0) {
            $parts[] = $convert($num);
        }

        return implode(' ', $parts) . ' Pesos';
    }
}
