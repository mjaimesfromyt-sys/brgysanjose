<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Barangay Business Clearance - {{ $businessName }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0;
        }

        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background: transparent !important;
        }

        body {
            font-family: "Times-Roman", "Times New Roman", Times, Georgia, serif;
            color: #111111;
            line-height: 1.35;
            font-size: 10.5pt;
            position: relative;
        }

        /* 👉 FULL-PAGE OPISYAL NGA BACKGROUND ARTWORK */
        .bg-template {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1000;
        }
        .bg-template img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* 👉 CONTENT BOX: Sakto kaayong gipahiluna sa sulod sa puti nga hawan sa template */
        .content-area {
            position: relative;
            margin-top: 213pt;    /* Ubos sa OFFICE OF THE PUNONG BARANGAY ribbon */
            margin-left: 125pt;   /* Tuo nga bahin sa vertical green sidebar */
            margin-right: 32pt;   /* Sulod sa tuo nga green border */
            margin-bottom: 45pt;  /* Ibabaw sa cancellation note banner */
            z-index: 10;
        }

        table, tr, td, tbody {
            background: transparent !important;
            background-color: transparent !important;
        }

        .date-row {
            margin-top: 14pt;
            text-align: right;
            font-size: 10.5pt;
            font-weight: bold;
            color: #222;
            margin-bottom: 8pt;
        }

        .salutation {
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            font-size: 10.5pt;
            margin-bottom: 6pt;
        }

        .body-p {
            text-align: justify;
            text-indent: 28pt;
            margin-bottom: 5pt;
            line-height: 1.28;
            font-size: 10.5pt;
        }

        /* 5 ka Business Information Table */
        .biz-table {
            width: 96%;
            margin: 6pt auto 8pt auto;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .biz-table td {
            padding: 2pt 4pt;
            vertical-align: top;
        }
        .biz-lbl {
            width: 32%;
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            color: #111;
        }
        .biz-sep {
            width: 3%;
            font-weight: bold;
            text-align: center;
        }
        .biz-val {
            width: 65%;
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            color: #111;
        }

        /* Attestation & Signature */
        .sig-section {
            width: 100%;
            margin-top: 14pt;
            margin-bottom: 8pt;
        }
        .sig-box {
            float: left;
            width: 220pt;
            text-align: center;
        }
        .sig-name {
            font-family: "Times-Bold", Times, serif;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            color: #111;
        }
        .sig-title {
            font-size: 9pt;
            color: #222;
            margin-top: 1pt;
        }
        .or-box {
            border: 1px solid #444;
            padding: 4px 8px;
            font-size: 8pt;
            line-height: 1.35;
            width: 225px;
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

        /* Bottom Metadata & Security QR Code */
        .bottom-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8pt;
            padding-top: 4pt;
            font-size: 7.5pt;
            color: #444;
        }

        .currency-symbol {
            font-family: "DejaVu Sans", sans-serif;
            font-weight: normal;
        }
    </style>
</head>
<body>

    {{-- 👉 1. FULL PAGE OPISYAL NGA TEMPLATE --}}
    @if (!empty($bizBgBase64))
        <div class="bg-template">
            <img src="{{ $bizBgBase64 }}" alt="Barangay Business Clearance Official Template">
        </div>
    @endif

    {{-- 👉 2. UNOD NGA HAOM SA PUTI NGA HAWAN --}}
    <div class="content-area">

        @php
            $purokFormatted = str_starts_with(strtolower($resident->purok ?? ''), 'purok') ? $resident->purok : 'Purok ' . ($resident->purok ?? '1');
        @endphp

        <div class="date-row">Date: {{ now()->format('m-d-Y') }}</div>

        <div class="salutation">To whom it may concern:</div>

        <div class="body-p">
            This is to certify that the <strong style="color: #C00000;">{{ $businessName }}</strong> located at San Jose, Talibon, Bohol, is duly registered and authorized to operate within our jurisdiction. The business is compliant with the necessary requirements and regulations set by barangay.
        </div>

        {{-- 5 ka Business Fields --}}
        <table class="biz-table">
            <tr>
                <td class="biz-lbl">Business Name</td>
                <td class="biz-sep">:</td>
                <td class="biz-val" style="color: #C00000;">{{ $businessName }}</td>
            </tr>
            <tr>
                <td class="biz-lbl">Business Address</td>
                <td class="biz-sep">:</td>
                <td class="biz-val">{{ !empty($requestModel->business_address) ? strtoupper($requestModel->business_address) : strtoupper($purokFormatted) . ', SAN JOSE, TALIBON, BOHOL' }}</td>
            </tr>
            <tr>
                <td class="biz-lbl">Nature of Business</td>
                <td class="biz-sep">:</td>
                <td class="biz-val">{{ $businessNature }}</td>
            </tr>
            <tr>
                <td class="biz-lbl">Owner’s Name</td>
                <td class="biz-sep">:</td>
                <td class="biz-val">{{ strtoupper($resident->name) }}</td>
            </tr>
            <tr>
                <td class="biz-lbl">Owner’s Address</td>
                <td class="biz-sep">:</td>
                <td class="biz-val">{{ strtoupper($purokFormatted) }}, SAN JOSE, TALIBON, BOHOL</td>
            </tr>
        </table>

        <div class="body-p">
            The above-mentioned business has undergone the necessary inspections and assessments conducted by the barangay authorities.
        </div>

        <div class="body-p">
            The business premise has been found to comply with safety and sanitation standards.
        </div>

        <div class="body-p">
            This Barangay Business Clearance Certificate is only considered valid for the <strong>calendar year</strong> in which it is issued. It serves as proof that the business has met all the requirements necessary to operate within the barangay legally.
        </div>

        <div class="body-p">
            Please note that this certificate only covers compliance with barangay regulations. It does not exempt the business from complying with national, provincial, or municipal requirements.
        </div>

        <div class="body-p">
            For any concerns or inquiries regarding this certificate, please feel free to contact the barangay office during office hours.
        </div>

        <div class="body-p" style="margin-bottom: 10pt;">
            Issued this <strong>{{ now()->format('d') . substr(now()->format('S'), 0) }}</strong> day of <strong>{{ now()->format('F, Y') }}</strong> at Barangay San Jose, Talibon, Bohol.
        </div>

        {{-- Attested by: Punong Barangay --}}
        <div class="sig-section">
            <div class="sig-box">
                <div style="font-size: 9pt; color: #333; margin-bottom: 16pt;">Attested by:</div>
                <div class="sig-name">{{ $captainName ?? 'JOSEFINA C. GURREA' }}</div>
                <div class="sig-title">Punong Barangay</div>
            </div>
        </div>

        {{-- Metadata & Security QR Code --}}
        <div style="clear: both;">
            <table class="bottom-meta-table">
            <tr>
                <td style="width: 62%; vertical-align: top;">
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
                <td style="width: 38%; text-align: right; padding-right: 8px; vertical-align: top;">
                    <div class="qr-code-box" style="display: inline-block; text-align: center;">
                        <div style="color: #C00000; font-weight: bold; font-size: 7.5pt; margin-bottom: 4pt; letter-spacing: 0.3px;">BARANGAY OFFICIAL SEAL</div>
                        @if (!empty($qrPngBase64))
                            <div style="display: inline-block; padding: 3px; background: #ffffff; border: 1px solid #bbb; border-radius: 4px;">
                                <img src="{{ $qrPngBase64 }}" style="width: 52px; height: 52px;">
                            </div>
                            <div class="qr-label">Scan to Verify Authenticity</div>
                        @elseif (!empty($qrCodeSvg))
                            <div style="display: inline-block; padding: 3px; background: #ffffff; border: 1px solid #bbb; border-radius: 4px;">
                                {!! $qrCodeSvg !!}
                            </div>
                            <div class="qr-label">Scan to Verify Authenticity</div>
                        @endif
                        <div style="font-size: 6.8pt; color: #555; margin-top: 1px; font-family: monospace;">Control No: {{ $controlNumber ?? 'BC-' . date('Ymd') . '-' . sprintf('%04d', $requestModel->id) }}</div>
                    </div>
                </td>
            </tr>
        </table>
        </div>

    </div>

</body>
</html>
