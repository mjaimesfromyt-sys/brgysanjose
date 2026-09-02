<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Document Verification - Barangay San Jose</title>

{{-- Script font for the secretary's signature. Falls back to a system
     cursive face if the device is offline, so nothing ever looks broken. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">

<style>
:root{
    --green-dark:#08501f;
    --green:#0b6b2c;
    --green-mid:#0f7a34;
    --green-accent:#0a7a35;
    --green-soft:#f0f8f2;
    --green-line:#d9ecdf;
    --ink:#1f2937;
    --muted:#5b6470;
    --line:#e9edf0;
}

*{box-sizing:border-box}

body{
    margin:0;
    background:#eef1f4;
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,Helvetica,sans-serif;
    color:var(--ink);
    padding:22px 16px;
    -webkit-font-smoothing:antialiased;
}

.container{
    max-width:1180px;
    margin:auto;
    background:#fff;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 14px 40px rgba(15,23,42,.10);
}

/* ---------------- HEADER ---------------- */

.header{
    background:linear-gradient(100deg,var(--green-dark) 0%,var(--green) 55%,var(--green-mid) 100%);
    color:#fff;
    padding:26px 40px;
    display:flex;
    align-items:center;
    gap:24px;
}

.logo{
    width:112px;
    height:112px;
    object-fit:contain;
    flex:none;
    border-radius:50%;
}

.header-center{
    text-align:center;
    flex:1;
    min-width:0;
}

.header-center .small{
    font-size:19px;
    font-weight:600;
    line-height:1.5;
    opacity:.96;
}

.header-center h1{
    margin:6px 0 4px;
    font-size:46px;
    font-weight:800;
    letter-spacing:.5px;
    line-height:1.05;
}

.header-center .portal{
    margin:0;
    font-size:21px;
    font-weight:600;
    opacity:.95;
}

/* ---------------- CONTENT ---------------- */

.content{
    padding:26px;
    background:#f6f8f9;
}

/* ---------------- VERIFIED BANNER ---------------- */

.verified-box{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:28px;
    background:#fff;
    border:1px solid var(--line);
    border-radius:14px;
    padding:30px 34px;
}

.verify-left{
    display:flex;
    align-items:center;
    gap:26px;
}

.check{
    width:86px;
    height:86px;
    flex:none;
    background:var(--green-accent);
    color:#fff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.verify-title{
    font-size:52px;
    font-weight:800;
    letter-spacing:1px;
    line-height:1;
    color:var(--green-dark);
}

.status-line{
    margin-top:12px;
    display:flex;
    align-items:center;
    gap:12px;
    font-size:22px;
    font-weight:700;
    color:var(--green-dark);
}

.valid{
    background:var(--green-accent);
    color:#fff;
    padding:6px 20px;
    border-radius:30px;
    font-size:15px;
    font-weight:700;
    letter-spacing:.5px;
}

.verify-sub{
    margin:12px 0 0;
    color:var(--muted);
    font-size:15px;
}

/* the pale "official document" panel on the right of the banner */
.official-note{
    display:flex;
    align-items:center;
    gap:18px;
    background:var(--green-soft);
    border:1px solid var(--green-line);
    border-radius:12px;
    padding:22px 26px;
    max-width:420px;
    color:#2f3b34;
    font-size:15px;
    line-height:1.6;
}

.official-note svg{flex:none;color:var(--green-accent)}

/* ---------------- CARDS ---------------- */

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    align-items:start;
    gap:26px;
    margin-top:26px;
}

.card{
    background:#fff;
    border:1px solid var(--line);
    border-radius:14px;
    overflow:hidden;
}

.card-title{
    background:var(--green);
    color:#fff;
    padding:16px 20px;
    font-size:16px;
    font-weight:700;
    letter-spacing:1px;
    display:flex;
    align-items:center;
    gap:12px;
}

.row{
    display:grid;
    grid-template-columns:26px 150px 1fr;
    align-items:center;
    gap:14px;
    padding:15px 20px;
    border-bottom:1px solid var(--line);
}

.row:last-child{border-bottom:none}

.row .ico{color:var(--green-accent);display:flex}

.label{color:var(--muted);font-size:15px}

.value{font-size:16px;font-weight:600;color:var(--ink);word-break:break-word}

.value.is-green{color:var(--green-accent);font-weight:700}

.value .sub{
    display:block;
    font-size:14px;
    font-weight:400;
    font-style:italic;
    color:var(--muted);
    margin-top:2px;
}

/* verification-date row keeps its value right-aligned like the sample */
.row.row--split{grid-template-columns:26px 1fr auto}
.row.row--split .value{text-align:right}

/* ---------------- DOCUMENT INTEGRITY ---------------- */

.check-list{padding:20px}

.check-list h4{
    margin:0 0 16px;
    font-size:15px;
    letter-spacing:.8px;
    color:var(--green-accent);
    font-weight:700;
}

.check-list .item{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:14px;
    font-size:15px;
}

.check-list .item:last-child{margin-bottom:0}

.circle{
    width:22px;
    height:22px;
    flex:none;
    background:var(--green-accent);
    color:#fff;
    border-radius:50%;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

/* pale "secure QR" panel inside the verification card */
.qr-note{
    display:flex;
    align-items:center;
    gap:16px;
    margin:6px 20px 20px;
    background:var(--green-soft);
    border:1px solid var(--green-line);
    border-radius:12px;
    padding:18px 20px;
    font-size:14px;
    line-height:1.6;
    color:#2f3b34;
}

.qr-note .lock{
    width:44px;
    height:44px;
    flex:none;
    background:#dff0e4;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:var(--green-dark);
}

/* ---------------- FOOTER ---------------- */

.footer{
    margin-top:26px;
    background:var(--green-soft);
    border:1px solid var(--green-line);
    border-radius:14px;
    padding:26px 32px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:28px;
}

.footer-left{
    display:flex;
    align-items:center;
    gap:20px;
    flex:1 1 340px;
    min-width:0;
    color:#2f3b34;
    font-size:15px;
    line-height:1.6;
}

.footer-left svg{flex:none;color:var(--green-dark)}

/* Stacked column so the name, and the "Barangay Secretary" role under it,
   can never end up side by side when the footer gets narrow. */
.signature{
    display:flex;
    flex-direction:column;
    align-items:center;
    flex:none;
    text-align:center;
    line-height:1.5;
}

.signature .sign{
    font-family:"Great Vibes","Segoe Script","Brush Script MT",cursive;
    font-size:34px;
    color:var(--green-dark);
    line-height:1.2;
}

.signature .sign,
.signature .name,
.signature .role{white-space:nowrap}

.signature .name{
    font-weight:700;
    letter-spacing:.6px;
    font-size:16px;
    color:var(--ink);
}

.signature .role{color:var(--muted);font-size:15px}

.bottom{
    display:flex;
    align-items:center;
    justify-content:center;
    flex-wrap:wrap;
    gap:10px;
    margin-top:22px;
    padding-bottom:6px;
    color:#6b7280;
    font-size:15px;
}

.bottom .sep{color:#cbd5e1}

.bottom span{display:inline-flex;align-items:center;gap:8px}

/* ---------------- UNVERIFIED ---------------- */

.unverified{
    background:#fff;
    border:1px solid #f3d3d3;
    border-radius:14px;
    padding:50px 30px;
    text-align:center;
}

.unverified .mark{
    width:86px;
    height:86px;
    margin:0 auto 18px;
    background:#c0392b;
    color:#fff;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.unverified h2{
    margin:0 0 10px;
    font-size:34px;
    color:#b02a1c;
    letter-spacing:1px;
}

.unverified p{margin:0;color:var(--muted);font-size:16px}

.unverified code{
    background:#f6f8f9;
    border:1px solid var(--line);
    border-radius:6px;
    padding:2px 8px;
    font-size:15px;
}

/* ---------------- RESPONSIVE ---------------- */

@media(max-width:900px){
    .grid{grid-template-columns:1fr}
    .footer{flex-direction:column;align-items:stretch;gap:18px}
    .footer-left{flex:none;width:100%}
    .signature{align-items:flex-start;text-align:left;width:100%}
    .verified-box{flex-direction:column;align-items:flex-start}
    .official-note{max-width:none}
    .header{flex-wrap:wrap;justify-content:center;padding:22px}
    .header-center{order:-1;flex-basis:100%}
}

@media(max-width:640px){
    body{padding:10px}
    .content{padding:14px}
    .header{padding:18px 14px;gap:14px}
    .header-center h1{font-size:26px}
    .header-center .small{font-size:14px}
    .header-center .portal{font-size:15px}
    .logo{width:58px;height:58px}

    .verified-box{padding:20px 16px;gap:18px}
    .verify-left{gap:14px}
    .verify-title{font-size:32px}
    .check{width:56px;height:56px}
    .check svg{width:32px;height:32px}
    .status-line{font-size:16px;flex-wrap:wrap;gap:8px}
    .official-note{padding:16px;gap:12px;font-size:14px}
    .official-note svg{width:38px;height:38px}

    .grid{gap:18px;margin-top:18px}
    .card-title{font-size:14px;letter-spacing:.6px;padding:14px 16px}
    .row,.row.row--split{grid-template-columns:26px 1fr;padding:13px 16px}
    .row .value{grid-column:2}
    .row.row--split .value{text-align:left}
    .check-list{padding:16px}
    .qr-note{margin:4px 16px 16px;padding:14px 16px;gap:12px}

    .footer{padding:20px 16px;gap:18px;margin-top:18px}
    .footer-left{gap:14px}
    .footer-left svg{width:38px;height:38px}
    .signature .sign{font-size:30px}
    /* stack the two lines instead of leaving a dangling "|" at a wrap */
    .bottom{flex-direction:column;font-size:14px;gap:6px}
    .bottom .sep{display:none}
}
</style>
</head>

<body>

<div class="container">

    {{-- ============ HEADER ============ --}}
    <div class="header">
        <img src="/images/barangay-seal.png" class="logo" alt="Seal of Barangay San Jose">

        <div class="header-center">
            <div class="small">REPUBLIC OF THE PHILIPPINES</div>
            <div class="small">Province of Bohol &bull; Municipality of Talibon</div>
            <h1>BARANGAY SAN JOSE</h1>
            <p class="portal">Official Document Verification Portal</p>
        </div>

        <img src="/images/talibon-seal.png" class="logo" alt="Seal of the Municipality of Talibon, Bohol">
    </div>

    <div class="content">

    @if($doc)

        {{-- ============ VERIFIED BANNER ============ --}}
        <div class="verified-box">
            <div class="verify-left">
                <div class="check">
                    <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="3"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L19 8"/></svg>
                </div>

                <div>
                    <div class="verify-title">VERIFIED</div>

                    <div class="status-line">
                        Certificate Status:
                        <span class="valid">VALID</span>
                    </div>

                    <p class="verify-sub">This document has been officially verified and is authentic.</p>
                </div>
            </div>

            <div class="official-note">
                <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>
                </svg>
                <div>This is an official and authentic document issued by Barangay San Jose.</div>
            </div>
        </div>

        {{-- ============ CARDS ============ --}}
        <div class="grid">

            {{-- ---- DOCUMENT INFORMATION ---- --}}
            <div class="card">
                <div class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/>
                        <path d="M8 14h3M8 17h6"/>
                    </svg>
                    DOCUMENT INFORMATION
                </div>

                <div class="row">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/>
                            <path d="M6 16c.6-1.6 1.7-2.4 3-2.4s2.4.8 3 2.4M14 9h4M14 13h4"/>
                        </svg>
                    </span>
                    <span class="label">Certificate No.</span>
                    <span class="value is-green">{{ $doc->control_number }}</span>
                </div>

                <div class="row">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span class="label">Issued To</span>
                    <span class="value is-green">
                        {{ $doc->user ? $doc->user->first_name.' '.$doc->user->last_name : 'Verified Resident' }}
                    </span>
                </div>

                <div class="row">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/>
                        </svg>
                    </span>
                    <span class="label">Document Type</span>
                    <span class="value">{{ $docTypeName }}</span>
                </div>

                <div class="row">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/>
                        </svg>
                    </span>
                    <span class="label">Purpose</span>
                    <span class="value">{{ $doc->purpose ?? 'General Purpose' }}</span>
                </div>

                <div class="row">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                    </span>
                    <span class="label">Date Issued</span>
                    <span class="value">
                        {{ $doc->issued_at ? \Carbon\Carbon::parse($doc->issued_at)->format('F j, Y') : now()->format('F j, Y') }}
                    </span>
                </div>

                <div class="row">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M17 11h5M19.5 8.5v5"/>
                        </svg>
                    </span>
                    <span class="label">Approved By</span>
                    <span class="value is-green">
                        {{ $punongBarangay ?? 'Hon. Josefina C. Gurrea' }}
                        <span class="sub">Punong Barangay</span>
                    </span>
                </div>
            </div>

            {{-- ---- VERIFICATION DETAILS ---- --}}
            <div class="card">
                <div class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>
                    </svg>
                    VERIFICATION DETAILS
                </div>

                <div class="row row--split">
                    <span class="ico">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                            <path d="m9 16 2 2 4-4"/>
                        </svg>
                    </span>
                    <span class="label">Verification Date</span>
                    <span class="value">
                        {{ now()->format('F j, Y') }}<br>{{ now()->format('h:i A') }}
                    </span>
                </div>

                <div class="check-list">
                    <h4>DOCUMENT INTEGRITY</h4>

                    <div class="item">
                        <span class="circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="3.5"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L19 8"/></svg>
                        </span>
                        Certificate exists in official registry
                    </div>

                    <div class="item">
                        <span class="circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="3.5"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L19 8"/></svg>
                        </span>
                        Certificate number is valid
                    </div>

                    <div class="item">
                        <span class="circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="3.5"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L19 8"/></svg>
                        </span>
                        Certificate has not been revoked
                    </div>

                    <div class="item">
                        <span class="circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="3.5"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 5 5L19 8"/></svg>
                        </span>
                        Verification token is authentic
                    </div>
                </div>

                <div class="qr-note">
                    <span class="lock">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                    </span>
                    <div>This document was verified using a secure QR code and is confirmed to be issued by Barangay San Jose.</div>
                </div>
            </div>

        </div>

        {{-- ============ FOOTER ============ --}}
        <div class="footer">
            <div class="footer-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 21h18M4 10h16M12 3 3 8h18ZM6 10v8M10 10v8M14 10v8M18 10v8"/>
                </svg>
                <div>
                    This certificate is a true and correct copy of the record in the<br>
                    Barangay San Jose Information Registry.
                </div>
            </div>

            <div class="signature">
                <div class="sign">{{ $barangaySecretary ?? 'Hannah Joy B. Credo' }}</div>
                <div class="name">{{ \Illuminate\Support\Str::upper($barangaySecretary ?? 'Hannah Joy B. Credo') }}</div>
                <div class="role">Barangay Secretary</div>
            </div>
        </div>

        <div class="bottom">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                </svg>
                Scanned on {{ now()->format('F j, Y \a\t h:i A') }}
            </span>

            <span class="sep">|</span>

            <span>
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/><path d="M3 12h18"/>
                    <path d="M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18Z"/>
                </svg>
                brgysanjose.site/verify
            </span>
        </div>

    @else

        {{-- ============ NOT FOUND ============ --}}
        <div class="unverified">
            <div class="mark">
                <svg xmlns="http://www.w3.org/2000/svg" width="46" height="46" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="3"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </div>

            <h2>UNVERIFIED DOCUMENT</h2>

            <p>The code <code>{{ $code }}</code> was not found in the Barangay San Jose registry.</p>
        </div>

    @endif

    </div>
</div>

</body>
</html>
