@php
    $methodLabels  = ['cash' => 'Cash', 'gcash' => 'GCash', 'paymaya' => 'PayMaya', 'bank_transfer' => 'Bank Transfer'];
    $channelLabels = ['gcash' => 'GCash', 'paymaya' => 'Maya', 'dob' => 'Online Banking', 'brankas' => 'Online Banking', 'card' => 'Card'];

    $methodLabel  = $methodLabels[$receipt['paymentMethod']] ?? ucfirst($receipt['paymentMethod'] ?? '');
    $channelLabel = $channelLabels[$receipt['paymentChannel'] ?? ''] ?? ($receipt['paymentChannel'] ?? null);

    // Emails are read outside this app's local dev domain (so a plain
    // asset() URL is unreachable), and Gmail strips data: URI images — so
    // the seal is embedded as a CID attachment via the injected $message
    // (Illuminate\Mail\Message, available here because this view is
    // rendered through Mailer::send()).
    $sealPath = public_path('images/barangay-seal.png');
    $sealCid  = (isset($message) && file_exists($sealPath)) ? $message->embed($sealPath) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $receipt['title'] }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f2f0;font-family:Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f2f0;padding:24px 12px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e3e6e3;">

<tr>
<td style="background-color:#0b5e35;padding:20px 32px;text-align:center;">
@if ($sealCid)
<img src="{{ $sealCid }}" width="56" height="56" alt="Seal of Barangay San Jose" style="display:block;margin:0 auto 8px;border-radius:50%;background:#ffffff;">
@endif
<div style="color:#ffffff;font-size:16px;font-weight:700;line-height:1.3;">Barangay San Jose</div>
<div style="color:#cfe3d6;font-size:12px;">Talibon, Bohol</div>
</td>
</tr>

<tr>
<td style="padding:28px 32px 8px;text-align:center;">
<div style="font-size:18px;font-weight:700;color:#1a1a1a;">{{ $receipt['title'] }}</div>
</td>
</tr>

<tr>
<td style="padding:8px 32px 0;">
<p style="margin:0 0 4px;font-size:14px;color:#1a1a1a;">Hi {{ $receipt['residentName'] }},</p>
@foreach ($receipt['intro'] as $paragraph)
<p style="margin:0 0 4px;font-size:14px;color:#3d3d3d;line-height:1.5;">{{ $paragraph }}</p>
@endforeach
</td>
</tr>

<tr>
<td style="padding:20px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="width:50%;padding-bottom:16px;">
<div style="font-size:11px;color:#767676;text-transform:uppercase;letter-spacing:.04em;">Resident</div>
<div style="font-size:14px;font-weight:700;color:#1a1a1a;">{{ $receipt['residentName'] }}</div>
</td>
<td style="width:50%;padding-bottom:16px;text-align:right;">
<div style="font-size:11px;color:#767676;text-transform:uppercase;letter-spacing:.04em;">Date</div>
<div style="font-size:14px;font-weight:700;color:#1a1a1a;">{{ $receipt['date']->format('M d, Y g:i A') }}</div>
</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:0 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
@foreach ($receipt['lines'] as $line)
<tr>
<td style="padding:8px 0;font-size:14px;color:#3d3d3d;border-bottom:1px solid #eef1ee;">{{ $line['label'] }}</td>
<td style="padding:8px 0;font-size:14px;color:#3d3d3d;text-align:right;border-bottom:1px solid #eef1ee;">{{ $line['value'] }}</td>
</tr>
@endforeach
<tr>
<td style="padding:10px 0 0;font-size:14px;font-weight:700;color:#1a1a1a;">Total paid</td>
<td style="padding:10px 0 0;font-size:14px;font-weight:700;color:#1a1a1a;text-align:right;">&#8369;{{ number_format($receipt['amount'], 2) }}</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:20px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="width:50%;">
<div style="font-size:11px;color:#767676;text-transform:uppercase;letter-spacing:.04em;">Payment method</div>
<div style="font-size:14px;font-weight:700;color:#1a1a1a;">
{{ $methodLabel }}
@if ($channelLabel && $channelLabel !== $methodLabel)
<span style="font-weight:400;color:#767676;">({{ $channelLabel }})</span>
@endif
</div>
</td>
@if ($receipt['paymentReference'] ?? null)
<td style="width:50%;text-align:right;">
<div style="font-size:11px;color:#767676;text-transform:uppercase;letter-spacing:.04em;">Payment ID</div>
<div style="font-size:12px;font-weight:700;color:#1a1a1a;word-break:break-all;">{{ $receipt['paymentReference'] }}</div>
</td>
@endif
</tr>
</table>
</td>
</tr>

@if ($receipt['claimCode'] ?? null)
<tr>
<td style="padding:24px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7f5;border-radius:8px;">
<tr>
<td style="padding:16px;text-align:center;">
<div style="font-size:11px;color:#767676;text-transform:uppercase;letter-spacing:.07em;">Claim code</div>
<div style="font-size:26px;font-weight:700;color:#0b5e35;font-family:'Courier New',Courier,monospace;letter-spacing:.05em;">{{ $receipt['claimCode'] }}</div>
</td>
</tr>
</table>
</td>
</tr>
@endif

@if ($receipt['note'] ?? null)
<tr>
<td style="padding:16px 32px 0;">
<p style="margin:0;font-size:12px;color:#767676;text-align:center;line-height:1.5;">{{ $receipt['note'] }}</p>
</td>
</tr>
@endif

@if ($receipt['ctaUrl'] ?? null)
<tr>
<td style="padding:24px 32px 4px;text-align:center;">
<a href="{{ $receipt['ctaUrl'] }}" style="display:inline-block;background-color:#0b5e35;color:#ffffff;text-decoration:none;font-size:14px;font-weight:700;padding:12px 28px;border-radius:6px;">{{ $receipt['ctaLabel'] ?? 'View details' }}</a>
</td>
</tr>
@endif

<tr>
<td style="padding:28px 32px 24px;">
<p style="margin:0;font-size:11px;color:#a3a3a3;text-align:center;">This is an automated message from Barangay San Jose. Please do not reply to this email.</p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>
