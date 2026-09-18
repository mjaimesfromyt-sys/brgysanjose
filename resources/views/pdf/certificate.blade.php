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
            top: 195pt;
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
            margin-top: 245pt;
            margin-bottom: 75pt;
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
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .footer-meta-table td {
            vertical-align: middle;
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

        <div class="doc-title">{{ $officialTitle }}</div>

        <div class="salutation">TO WHOM IT MAY CONCERN:</div>

        @if (!empty($hasIncomeCert))
            {{-- 👉 100% EXACT GOOGLE DOC TEMPLATE (PROOF OF INCOME / LOAN APPLICATION) --}}
            <div class="body-p">
                THIS IS TO CERTIFY that according to the record available on file in this office, <span class="highlight-name">{{ strtoupper($resident->name) }}</span>, {{ $citizenship ?? 'Filipino' }}, {{ $ageDisplay }}, a bonafide resident of <strong>{{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol</strong>{{ $stayText }}, has no derogatory record filed against him/her having been validated in this office.
            </div>

            <div class="body-p">
                THIS IS TO CERTIFY FURTHER that he/she has engaged in <strong>{{ strtoupper($occupation) }}</strong> {{ !empty($employedSince) ? 'since ' . $employedSince . ' to present' : 'to present' }} with a monthly income of <strong>{{ $monthlyIncomeWords }} (P{{ number_format($monthlyIncome, 2) }})</strong>.
            </div>

            <div class="body-p">
                This Barangay Certification is being issued upon the request of the above mentioned name to support his/her <strong>{{ strtoupper($purposeText) }}</strong>.
            </div>

        @else

            {{-- Standard General Certification --}}
            <div class="body-p">
                THIS IS TO CERTIFY that <span class="highlight-name">{{ strtoupper($resident->name) }}</span>, 
                {{ $ageDisplay }}, {{ $citizenship ?? 'Filipino' }}, {{ $statusDisplay }}, and a bonafide resident of 
                <strong>{{ $purokFormatted }}, Barangay San Jose, Talibon, Bohol, Philippines</strong>{{ $stayText }}.
            </div>

            @if (str_contains($typeLower, 'indigency') || str_contains($typeLower, 'indigent'))
                <div class="body-p">
                    THIS IS TO CERTIFY FURTHER that he/she is known to this office to be a person of good moral character and a law-abiding citizen with no derogatory record nor pending criminal complaints in this Barangay.
                </div>
                <div class="body-p">
                    THIS IS TO CERTIFY FURTHERMORE that as per family census records on file in this barangay, his/her family belongs to a low-income household or <strong>"INDIGENT"</strong>, with income insufficient to sustain daily basic necessities due to economic challenges and inflation.
                </div>
                <div class="body-p">
                    This certification is being issued upon the request of the above-named person to support his/her application for 
                    <strong>{{ $purposeText }}</strong> (Financial, Medical, DSWD, or Educational Assistance).
                </div>

            @elseif (str_contains($typeLower, 'solo parent'))
                <div class="body-p">
                    THIS IS TO CERTIFY FURTHER that <span class="highlight-name">{{ strtoupper($resident->name) }}</span> is a registered <strong>SOLO PARENT</strong> up to the present, bearing sole parental responsibility for his/her household, is of good moral character, and has no derogatory record in this barangay.
                </div>
                <div class="body-p">
                    This certification is being issued upon request to support his/her application for <strong>{{ $purposeText }}</strong> pursuant to the Solo Parents Welfare Act (RA 8972 / RA 11861).
                </div>

            @elseif (str_contains($typeLower, 'good moral') || str_contains($typeLower, 'unlawful'))
                <div class="body-p">
                    THIS is to certify further that based on available records and testimonies from the community, the aforementioned person is personally known to this office as a peace-loving and law-abiding citizen, not involved in any cult or any group engaged in unlawful activities, and has no pending dispute or criminal derogatory record.
                </div>
                <div class="body-p">
                    This certification is being issued upon the request of the above-named person to support his/her application for <strong>{{ $purposeText }}</strong>, or for whatever legal purpose it may serve him/her best.
                </div>

            @elseif (str_contains($typeLower, 'life') || str_contains($typeLower, 'still alive') || str_contains($typeLower, 'osca'))
                <div class="body-p">
                    THIS IS TO CERTIFY FURTHER that according to barangay records and physical validation, <span class="highlight-name">{{ strtoupper($resident->name) }}</span> is a senior citizen and is <strong>STILL ALIVE</strong> and residing in this barangay.
                </div>
                <div class="body-p">
                    This certification is being issued upon request for <strong>{{ $purposeText }}</strong> (SSS / GSIS / OSCA Social Pension Requirement).
                </div>

            @elseif (str_contains($typeLower, 'jobseeker'))
                <div class="body-p">
                    THIS IS TO FURTHER CERTIFY that the bearer is a qualified availer of Republic Act 11261, otherwise known as the <em>First Time Jobseekers Assistance Act of 2019</em>, and has been informed of rights, duties, and responsibilities through an Oath of Undertaking executed before barangay authorities.
                </div>
                <div class="body-p">
                    This certification is issued for <strong>{{ $purposeText }}</strong> and is valid for one (1) year from date of issuance.
                </div>

            @elseif (str_contains($typeLower, 'cutting') || str_contains($typeLower, 'tree'))
                <div class="body-p">
                    THIS IS TO CERTIFY that following an on-site barangay inspection conducted on the premises of <span class="highlight-name">{{ strtoupper($resident->name) }}</span> at {{ $purokFormatted }}, Barangay San Jose, the designated tree(s) were determined to be hazardous and posing imminent risk to public safety and property.
                </div>
                <div class="body-p">
                    For this reason, the Sangguniang Barangay of San Jose <strong>INTERPOSES NO OBJECTION</strong> for the cutting of the specified hazardous tree(s).
                </div>

            @elseif (str_contains($typeLower, 'dswd') || str_contains($typeLower, 'burial') || str_contains($typeLower, 'boreden') || str_contains($typeLower, 'burden'))
                <div class="body-p">
                    THIS IS TO CERTIFY FURTHER that the bearer is personally known to this office to belong to an economically burdened and low-income household in this barangay, with income insufficient to sustain basic necessities, medical needs, or funeral expenses.
                </div>
                <div class="body-p">
                    This certification is being issued upon the request of the above-named person to support his/her application for <strong>{{ $purposeText }}</strong> (DSWD Assistance / Burial / Crisis Intervention Program).
                </div>

            @else
                <div class="body-p">
                    Based on the records available in this office, the aforementioned individual is known to be of good moral character, a law-abiding citizen in the community, and has <strong>NO DEROGATORY RECORD</strong> or pending criminal charges filed against him/her in this Barangay.
                </div>
                <div class="body-p">
                    This certification is being issued upon the request of the above-named person to support his/her application for 
                    <strong>{{ $purposeText }}</strong>, or for whatever legal purpose/s it may serve him/her best.
                </div>
            @endif

        @endif

        <div class="body-p">
            IN WITNESS HEREOF, we have hereunto set our signatures this 
            <strong>{{ now()->format('jS') }}</strong> day of 
            <strong>{{ now()->format('F, Y') }}</strong>, at Barangay San Jose, Talibon, Bohol, Philippines.
        </div>

        <!-- Signatures Section -->
        <table class="sig-table">
                <td style="padding-left: 5px;">
                    <div style="font-size: 9.5pt; color: #333; margin-bottom: 28px;">Records verified by:</div>
                    <div class="sig-block"><div class="sig-name">{{ $secretaryName ?? "HANNAH JOY B. CREDO" }}</div><div class="sig-title">Barangay Secretary</div></div>
                </td>
                <td style="padding-left: 50px;">
                    <div style="font-size: 9.5pt; color: #333; margin-bottom: 28px;">Noted by:</div>
                    <div class="sig-block"><div class="sig-name">{{ $captainName ?? "JOSEFINA C. GURREA" }}</div><div class="sig-title">Punong Barangay</div></div>
                </td>
                </td>
            </tr>
        </table>

        <!-- Receipt Box & Security QR Code (100% Transparent, No White Cutoff) -->
        <table class="footer-meta-table">
            <tr>
                <td style="width: 55%;">
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
                    <div class="qr-code-box" style="display: inline-block; text-align: center;">
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
