<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Daily Collection Summary</title>
<style>
    @page { margin: 14mm 12mm; }
    * { box-sizing: border-box; }
    body { font-family: "DejaVu Sans", Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1e293b; }
    .head { text-align: center; border-bottom: 2px solid #166534; padding-bottom: 6px; margin-bottom: 10px; }
    .head .rep { font-size: 9px; letter-spacing: .5px; color: #475569; text-transform: uppercase; }
    .head .brgy { font-size: 15px; font-weight: bold; color: #166534; letter-spacing: .8px; text-transform: uppercase; }
    .head .title { display: inline-block; margin-top: 4px; padding: 2px 10px; background: #dcfce7; border: 1px solid #86efac; border-radius: 3px; font-weight: bold; font-size: 10px; color: #14532d; }
    .title-wrap { text-align: center; margin: 6px 0 10px; }
    .title { display: inline-block; padding: 2px 12px; background: #dcfce7; border: 1px solid #86efac; border-radius: 3px; font-weight: bold; font-size: 11px; color: #14532d; }
    .meta-note { font-size: 8.5px; color: #64748b; margin-top: 3px; }
    .meta { width: 100%; margin-bottom: 8px; }
    .meta td { font-size: 10px; padding: 1px 0; }
    table.list { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.list th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 6px; font-size: 8.5px; text-transform: uppercase; letter-spacing: .4px; text-align: left; }
    table.list td { border: 1px solid #e2e8f0; padding: 3.5px 6px; }
    .num { text-align: right; }
    .sec-label { font-weight: bold; color: #166534; font-size: 11px; margin: 8px 0 3px; }
    .sub { color: #64748b; font-weight: normal; font-size: 9.5px; }
    .grand { width: 52%; margin-left: auto; margin-top: 8px; }
    .grand td { padding: 3px 6px; font-size: 10.5px; }
    .grand .label { font-weight: bold; text-align: right; }
    .grand .amount { font-weight: bold; text-align: right; }
    .grand .gtotal { font-size: 12.5px; color: #166534; border-top: 2px solid #166534; }
    .sign { margin-top: 24px; width: 100%; }
    .sign td { text-align: center; font-size: 9.5px; color: #475569; }
    .sign .line { border-top: 1px solid #334155; margin: 26px 30px 3px; }
    .foot { margin-top: 14px; font-size: 8.5px; color: #94a3b8; text-align: center; }
    .empty { text-align: center; color: #94a3b8; padding: 8px; font-style: italic; }
</style>
</head>
<body>

@if (!empty($headerImgBase64))
    <img src="{{ $headerImgBase64 }}" alt="Official Barangay San Jose Letterhead" style="width: 100%; display: block; margin-bottom: 6px;">
@else
<div class="head">
    <div class="rep">Republic of the Philippines &middot; Province of Bohol &middot; Municipality of Talibon</div>
    <div class="brgy">Barangay San Jose</div>
</div>
@endif
<div class="title-wrap">
    <div class="title">DAILY COLLECTION SUMMARY — CASH &amp; CASHLESS</div>
    <div class="meta-note">Official financial report &middot; Barangay San Jose, Talibon, Bohol</div>
</div>

<table class="meta">
    <tr>
        <td><strong>Date:</strong> {{ $date }}</td>
        <td class="num"><strong>Generated:</strong> {{ $generatedAt }}</td>
    </tr>
</table>

{{-- ================= CASH COLLECTIONS (counter) ================= --}}
<div class="sec-label">💵 Cash collections <span class="sub">(received at the barangay counter)</span></div>
@if ($cashRows->isEmpty())
    <div class="empty">No cash collections recorded on this date.</div>
@else
    <table class="list">
        <thead>
            <tr>
                <th style="width:55px">Time</th>
                <th>Paid by</th>
                <th>For</th>
                <th style="width:95px">Collected by</th>
                <th class="num" style="width:78px">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashRows as $row)
                <tr>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['who'] }}<div class="sub">{{ $row['what'] }}</div></td>
                    <td>{{ $row['service'] }}</td>
                    <td>{{ $row['collector'] }}</td>
                    <td class="num">₱{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ================= CASHLESS (online) ================= --}}
<div class="sec-label">💳 Cashless / online payments <span class="sub">(GCash · PayMaya · bank via PayMongo — no cash handled)</span></div>
@if ($cashlessRows->isEmpty())
    <div class="empty">No online payments settled on this date.</div>
@else
    <table class="list">
        <thead>
            <tr>
                <th style="width:55px">Time</th>
                <th>Paid by</th>
                <th>For</th>
                <th style="width:120px">Reference</th>
                <th class="num" style="width:78px">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashlessRows as $row)
                <tr>
                    <td>{{ $row['time'] }}</td>
                    <td>{{ $row['who'] }}<div class="sub">{{ $row['what'] }}</div></td>
                    <td>{{ $row['service'] }}</td>
                    <td style="font-size:9px">{{ \Illuminate\Support\Str::limit($row['reference'] ?? 'online', 26) }}</td>
                    <td class="num">₱{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ================= PER-COLLECTOR (cash accountability) ================= --}}
@if ($perCollector->isNotEmpty())
    <div class="sec-label">Cash accountability per collector</div>
    <table class="list" style="width:62%">
        <thead><tr><th>Collected by</th><th class="num" style="width:60px">Payments</th><th class="num" style="width:90px">Total</th></tr></thead>
        <tbody>
            @foreach ($perCollector as $c)
                <tr>
                    <td>{{ $c['collector'] }}</td>
                    <td class="num">{{ $c['count'] }}</td>
                    <td class="num">₱{{ number_format($c['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="font-weight:bold; background:#f8fafc">Cash subtotal</td>
                <td class="num" style="font-weight:bold; background:#f8fafc">{{ $cashRows->count() }}</td>
                <td class="num" style="font-weight:bold; background:#f8fafc">₱{{ number_format($cashTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endif

<table class="grand">
    <tr>
        <td class="label">Cash (counter):</td>
        <td class="amount">₱{{ number_format($cashTotal, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Cashless (online):</td>
        <td class="amount">₱{{ number_format($cashlessTotal, 2) }}</td>
    </tr>
    <tr>
        <td class="label gtotal">GRAND TOTAL — {{ $date }}:</td>
        <td class="amount gtotal">₱{{ number_format($grandTotal, 2) }}</td>
    </tr>
</table>

@if ($grandTotal == 0 && $pendingThatDay > 0)
    <div class="empty" style="margin-top:6px; font-style:normal; color:#475569">
        Note: {{ $pendingThatDay }} request(s) were filed on this date but no payment has been received yet —
        they will appear on the summary for the day they are paid.
    </div>
@endif

<table class="sign">
    <tr>
        <td style="width:45%"><div class="line"></div>Prepared by (cash collector)</td>
        <td style="width:10%"></td>
        <td style="width:45%"><div class="line"></div>Received by (Punong Barangay / Treasurer)</td>
    </tr>
</table>

<div class="foot">
    Barangay San Jose — Talibon, Bohol &middot; auto-generated report &middot; {{ $generatedAt }}
</div>

</body>
</html>
