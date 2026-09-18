{{--
    Payment receipt, rendered inside the shared branded shell.

    Expects a $receipt array with: title, residentName, date (Carbon), intro
    (array of paragraphs), lines (array of ['label', 'value']), amount,
    paymentMethod, paymentChannel, paymentReference, claimCode, note,
    ctaLabel, ctaUrl.
--}}
@php
    $methodLabels  = ['cash' => 'Cash', 'gcash' => 'GCash', 'paymaya' => 'PayMaya', 'bank_transfer' => 'Bank Transfer'];
    $channelLabels = ['gcash' => 'GCash', 'paymaya' => 'Maya', 'dob' => 'Online Banking', 'brankas' => 'Online Banking', 'card' => 'Card'];

    $methodLabel  = $methodLabels[$receipt['paymentMethod']] ?? ucfirst($receipt['paymentMethod'] ?? '');
    $channelLabel = $channelLabels[$receipt['paymentChannel'] ?? ''] ?? ($receipt['paymentChannel'] ?? null);
@endphp

<x-mail-shell :title="$receipt['title']">
    <p style="margin:0 0 16px;font-size:17px;font-weight:700;color:#14281d;line-height:1.5;">Hi {{ $receipt['residentName'] }},</p>

    @foreach ($receipt['intro'] as $paragraph)
        <p style="margin:0 0 14px;font-size:15px;color:#2f3b34;line-height:1.6;">{{ $paragraph }}</p>
    @endforeach

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:10px;">
    <tr>
        <td width="50%" valign="top" style="padding-bottom:16px;">
            <div style="font-size:11px;color:#6b7a71;text-transform:uppercase;letter-spacing:.05em;">Resident</div>
            <div style="font-size:14px;font-weight:700;color:#14281d;padding-top:2px;">{{ $receipt['residentName'] }}</div>
        </td>
        <td width="50%" valign="top" align="right" style="padding-bottom:16px;">
            <div style="font-size:11px;color:#6b7a71;text-transform:uppercase;letter-spacing:.05em;">Date</div>
            <div style="font-size:14px;font-weight:700;color:#14281d;padding-top:2px;">{{ $receipt['date']->format('M d, Y g:i A') }}</div>
        </td>
    </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
    @foreach ($receipt['lines'] as $line)
        <tr>
            <td style="padding:9px 0;font-size:14px;color:#2f3b34;border-bottom:1px solid #eaeeeb;">{{ $line['label'] }}</td>
            <td align="right" style="padding:9px 0;font-size:14px;color:#2f3b34;border-bottom:1px solid #eaeeeb;">{{ $line['value'] }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="padding:12px 0 0;font-size:15px;font-weight:700;color:#14281d;">Total paid</td>
        <td align="right" style="padding:12px 0 0;font-size:15px;font-weight:700;color:#14281d;">&#8369;{{ number_format($receipt['amount'], 2) }}</td>
    </tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;">
    <tr>
        <td width="50%" valign="top">
            <div style="font-size:11px;color:#6b7a71;text-transform:uppercase;letter-spacing:.05em;">Payment method</div>
            <div style="font-size:14px;font-weight:700;color:#14281d;padding-top:2px;">
                {{ $methodLabel }}
                @if ($channelLabel && $channelLabel !== $methodLabel)
                    <span style="font-weight:400;color:#6b7a71;">({{ $channelLabel }})</span>
                @endif
            </div>
        </td>
        @if ($receipt['paymentReference'] ?? null)
            <td width="50%" valign="top" align="right">
                <div style="font-size:11px;color:#6b7a71;text-transform:uppercase;letter-spacing:.05em;">Payment ID</div>
                <div style="font-size:12px;font-weight:700;color:#14281d;padding-top:2px;word-break:break-all;">{{ $receipt['paymentReference'] }}</div>
            </td>
        @endif
    </tr>
    </table>

    @if ($receipt['claimCode'] ?? null)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
               style="background-color:#f2f6f2;border-radius:10px;margin-top:22px;">
        <tr>
        <td align="center" style="padding:18px 16px;">
            <div style="font-size:11px;color:#6b7a71;text-transform:uppercase;letter-spacing:.08em;">Claim code</div>
            <div style="font-size:28px;font-weight:700;color:#0b5e35;font-family:'Courier New',Courier,monospace;letter-spacing:.06em;padding-top:6px;">{{ $receipt['claimCode'] }}</div>
        </td>
        </tr>
        </table>
    @endif

    @if ($receipt['note'] ?? null)
        <p style="margin:16px 0 0;font-size:13px;color:#6b7a71;text-align:center;line-height:1.6;">{{ $receipt['note'] }}</p>
    @endif

    @if ($receipt['ctaUrl'] ?? null)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0 4px;">
        <tr>
        <td align="center">
            <a href="{{ $receipt['ctaUrl'] }}"
               style="display:inline-block;background-color:#0b5e35;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 32px;border-radius:6px;">{{ $receipt['ctaLabel'] ?? 'View details' }}</a>
        </td>
        </tr>
        </table>
    @endif
</x-mail-shell>
