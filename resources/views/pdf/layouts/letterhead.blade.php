<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $documentType }} - {{ $resident->name }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0;
        }

        body {
            font-family: "Times-Roman", "Times New Roman", Times, Georgia, serif;
            color: #111111;
            line-height: 1.5;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            background: transparent !important;
        }

        /* 270pt Watermark: Nahimutang sa tunga sa text, DILI motandog sa mga pirma o sa resibo box */
        .fixed-watermark {
            position: fixed;
            top: 270pt;
            left: 171pt;
            width: 270pt;
            height: 270pt;
            opacity: 0.065;
            z-index: -1000;
        }
        .fixed-watermark img {
            width: 270pt;
            height: 270pt;
            display: block;
        }

        .fixed-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 100;
            line-height: 0;
        }
        .fixed-header img {
            width: 100%;
            height: auto;
            display: block;
        }

        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 100;
            line-height: 0;
        }
        .fixed-footer img {
            width: 100%;
            height: auto;
            display: block;
        }

        .fixed-footer-contacts {
            position: fixed;
            bottom: 5pt;
            left: 20pt;
            right: 20pt;
            z-index: 200;
            font-size: 7.2pt;
            color: #ffffff;
            font-family: "Times-Roman", Times, serif;
            line-height: 1.2;
        }
        .fixed-footer-contacts table {
            width: 100%;
            border-collapse: collapse;
        }
        .fixed-footer-contacts td {
            color: #ffffff;
            font-family: "Times-Roman", Times, serif;
        }

        .page-content {
            margin-top: 230pt;
            margin-bottom: 25pt;
            padding-left: 0.75in;
            padding-right: 0.75in;
            position: relative;
            z-index: 10;
        }

        table, tr, td, tbody, thead, .or-box, .footer-meta-table, .sig-table {
            background: transparent !important;
            background-color: transparent !important;
        }

        .date-row {
            text-align: right;
            font-size: 10.5pt;
            font-weight: bold;
            color: #222;
            margin-bottom: 14px;
        }

        .doc-title {
            text-align: center;
            font-family: "Times-Bold", Times, serif;
            font-size: 17.5pt;
            font-weight: bold;
            letter-spacing: 1.2px;
            color: #111111;
            margin-bottom: 18px;
        }

        .salutation {
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            font-size: 11.5pt;
            margin-bottom: 12px;
        }

        .body-p {
            text-align: justify;
            text-indent: 36px;
            margin-bottom: 12px;
            line-height: 1.52;
            font-size: 11pt;
        }

        .highlight-name {
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            text-decoration: underline;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 26px;
            margin-bottom: 16px;
        }
        .sig-table td {
            vertical-align: top;
            width: 50%;
        }

        .sig-name {
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            font-size: 11.5pt;
            text-decoration: underline;
            color: #111;
            display: inline-block;
        }
        .sig-block {
            display: inline-block;
            text-align: center;
        }
        .sig-title {
            text-align: center;
            font-size: 9.5pt;
            color: #333;
            margin-top: 2px;
        }

        .footer-meta-table {
            margin-bottom: 40pt;
            position: relative;
            z-index: 900;
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .footer-meta-table td {
            vertical-align: middle;
        }

        .or-box {
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 1px solid #444;
            padding: 4px 8px;
            font-size: 8pt;
            line-height: 1.35;
            width: 225px;
        }
        .or-box table, .or-box tr, .or-box td {
            background: #ffffff !important;
            background-color: #ffffff !important;
        }

        .qr-code-box {
            text-align: right;
        }
        .qr-code-box img {
            width: 52px;
            height: 52px;
        }
        .qr-label {
            font-size: 6.5pt;
            font-weight: bold;
            color: #222;
            margin-top: 2px;
            letter-spacing: 0.2px;
        }

        .currency-symbol {
            font-family: "DejaVu Sans", sans-serif;
            font-weight: normal;
        }
    </style>
</head>
<body>

    @if (!empty($barangaySealBase64))
        <div class="fixed-watermark">
            <img src="{{ $barangaySealBase64 }}" alt="Watermark">
        </div>
    @endif

    @if (!empty($headerImgBase64))
        <div class="fixed-header">
            <img src="{{ $headerImgBase64 }}" alt="Official Barangay San Jose Header">
        </div>
    @endif

    @if (!empty($footerImgBase64))
        <div class="fixed-footer">
            <img src="{{ $footerImgBase64 }}" alt="Official Barangay San Jose Footer">
        </div>
    @endif

    <div class="fixed-footer-contacts">
        <table>
            <tr>
                <td style="text-align: left; padding-left: 20px;">
                    OFFICIAL FACEBOOK PAGE: <strong>{{ $barangayFacebook ?? 'Barangay San Jose - Official' }}</strong>
                </td>
                <td style="text-align: right; padding-right: 20px;">
                    Official Email Address: <strong>{{ $barangayEmail ?? 'blgusanjosetalibon1910@gmail.com' }}</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-content">

        @php
            $typeLower = strtolower($documentType ?? '');
            $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
            
            // Standardize official title
            if (str_contains($typeLower, 'clearance') && !str_contains($typeLower, 'business')) {
                $officialTitle = 'BARANGAY CLEARANCE';
            } elseif (str_contains($typeLower, 'indigency') || str_contains($typeLower, 'indigent')) {
                $officialTitle = 'CERTIFICATE OF INDIGENCY';
            } elseif (str_contains($typeLower, 'solo parent')) {
                $officialTitle = 'SOLO PARENT CERTIFICATION';
            } elseif (str_contains($typeLower, 'jobseeker')) {
                $officialTitle = 'FIRST TIME JOBSEEKERS CERTIFICATION (RA 11261)';
            } elseif (str_contains($typeLower, 'life') || str_contains($typeLower, 'still alive')) {
                $officialTitle = 'CERTIFICATE OF LIFE';
            } elseif (str_contains($typeLower, 'tree') || str_contains($typeLower, 'cutting')) {
                $officialTitle = 'PERMIT TO CUT TREES';
            } else {
                $officialTitle = 'BARANGAY CERTIFICATION';
            }

            $purposeText = !empty($requestModel->purpose) ? strtoupper($requestModel->purpose) : 'WHATEVER LEGAL PURPOSE/S IT MAY SERVE';
            $ageDisplay = !empty($residentAge) ? $residentAge . ' years old' : 'of legal age';
            $statusDisplay = !empty($civilStatus) ? ucfirst($civilStatus) : 'Single';
            $stayText = !empty($lengthOfStay) ? " (residing for " . $lengthOfStay . " " . ($lengthOfStay == 1 ? "year" : "years") . ")" : "";
        @endphp

        <div class="date-row">Date: {{ now()->format('m-d-Y') }}</div>
        <div class="doc-title">@yield('title')</div>
        <div class="salutation">@yield('salutation', 'TO WHOM IT MAY CONCERN:')</div>

        @yield('body')

        @yield('signatories')

        <!-- Receipt Box & Security QR Code (100% Transparent, No White Cutoff) -->
        <table class="footer-meta-table">
            <tr>
                <td style="width: 55%; text-align: center;">
                    <div class="or-box">
                        <table style="width: 100%; font-size: 8pt; border-collapse: collapse;">
                            <tr>
                                <td style="color: #333; width: 45%;">Official Receipt No.:</td>
                                <td style="font-weight: bold; font-family: monospace;">{{ $requestModel->or_number ?? 'OR-' . date('Ymd') . '-' . sprintf('%04d', $requestModel->id) }}</td>
                            </tr>
                            <tr>
                                <td style="color: #333;">Date of Issued:</td>
                                <td>{{ now()->format('F d, Y') }}</td>
                            </tr>
                            <tr>
                                <td style="color: #333;">Amount Paid:</td>
                                <td style="font-weight: bold;"><span class="currency-symbol">&#8369;</span>{{ number_format($requestModel->transactionType->fee ?? 180.00, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color: #333;">Place of Issue:</td>
                                <td>San Jose, Talibon, Bohol</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 45%; text-align: right;">
                    <div class="qr-code-box" style="display: inline-block; text-align: center; margin-top: -12px;">
                        @if (!empty($qrCodeSvg))
                            <div style="display: inline-block; padding: 3px; background: transparent; border: 1px solid #bbb; border-radius: 4px;">
                                <img src="data:image/svg+xml;base64,{{ base64_encode($qrCodeSvg) }}" style="width: 52px; height: 52px;">
                            </div>
                            <div class="qr-label">Scan to Verify Authenticity</div>
                        @endif
                        <div style="font-size: 6.8pt; color: #555; margin-top: 1px; font-family: monospace;">Control No: {{ $controlNumber ?? 'BC-' . date('Ymd') . '-' . sprintf('%04d', $requestModel->id) }}</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
