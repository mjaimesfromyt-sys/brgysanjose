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
        $qrCodeSvg = QrCode::size(85)->generate($verifyUrl);

        // Seals to Base64
        $talibonPath = public_path('images/talibon-seal.png');
        $barangayPath = public_path('images/barangay-seal.png');
        $talibonSealBase64 = file_exists($talibonPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($talibonPath)) : null;
        $barangaySealBase64 = file_exists($barangayPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($barangayPath)) : null;

        $searchDirs = [
            public_path('images'),
            base_path('../public_html/images'),
            public_path(),
            base_path('../public_html'),
        ];

        $headerImgBase64 = null;
        foreach ($searchDirs as $dir) {
            $files = glob($dir . '/*header*.png') ?: [];
            if (!empty($files)) {
                $headerImgBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($files[0]));
                break;
            }
        }

        $footerImgBase64 = null;
        foreach ($searchDirs as $dir) {
            $files = glob($dir . '/*footer*.png') ?: [];
            if (!empty($files)) {
                $footerImgBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($files[0]));
                break;
            }
        }

        $bizBgBase64 = null;
        foreach ($searchDirs as $dir) {
            $files = glob($dir . '/*business-clearance*.png') ?: [];
            if (!empty($files)) {
                $bizBgBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($files[0]));
                break;
            }
        }

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
            $pdf = Pdf::loadView("pdf.certificates." . $slug, $data);
            $fileName = preg_replace("/[^A-Za-z0-9 _.-]/", "", ($requestModel->transactionType->name ?? "Certificate") . "-" . $resident->last_name);
            return $pdf->stream($fileName . ".pdf");
        }


        if (str_contains($typeLower, 'business')) {
            $pdf = Pdf::loadView('pdf.business-clearance', $data);
            return $pdf->stream('Barangay-Business-Clearance-' . $resident->last_name . '.pdf');
        }

        $pdf = Pdf::loadView('pdf.certificate', $data);
        return $pdf->stream('Barangay-Certificate-' . $resident->last_name . '.pdf');
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
