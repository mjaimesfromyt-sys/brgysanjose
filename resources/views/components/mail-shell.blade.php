{{--
    The single shell every outgoing HTML email is rendered inside — status
    updates, receipts, OTP codes and the framework's own password-reset mail all
    wrap themselves in it, so the branding is maintained in one place.

        <x-mail-shell title="Payment receipt">
            ...body...
            <x-slot:signoff>...</x-slot:signoff>   (optional)
        </x-mail-shell>

    A component rather than an @extends layout so it also works from inside
    resources/views/vendor/mail/html/branded.blade.php, which the notification
    mail view pulls in with @include.

    Table-based with inline styles because Gmail, Outlook and the mobile clients
    strip <style> blocks and ignore most modern CSS.
--}}
@props(['title' => null])
@php
    $barangay = config('barangay');

    // The seal is referenced by a fixed content id rather than a URL: an
    // emailed asset() link is unreachable from outside the app's domain, and
    // Gmail strips data: URIs. App\Providers\AppServiceProvider attaches the
    // matching inline part on the MessageSending event, so this works for
    // every mailer path — view mails, notifications and framework mail alike.
    $sealCid = file_exists(public_path('images/barangay-seal.png')) ? 'cid:barangay-seal.png' : null;

    $facebookName = $barangay['facebook']['name'] ?? null;
    $facebookUrl  = $barangay['facebook']['url'] ?? null;
    $footerCells  = collect([$facebookName, $barangay['email'] ?? null, $barangay['phone'] ?? null])
        ->filter()->count();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>{{ $title ?? $barangay['name'] }}</title>
</head>
<body style="margin:0;padding:0;background-color:#eef1ef;-webkit-text-size-adjust:100%;font-family:Helvetica,Arial,sans-serif;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1ef;">
<tr>
<td align="center" style="padding:28px 12px;">

<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border:1px solid #e2e7e3;border-radius:12px;">

    {{-- Masthead --}}
    <tr>
    <td align="center" style="padding:32px 32px 0;">
        @if ($sealCid)
            <img src="{{ $sealCid }}" width="44" height="44" alt="Seal of {{ $barangay['name'] }}"
                 style="display:block;margin:0 auto 12px;border:0;outline:none;text-decoration:none;">
        @endif
        <div style="font-size:24px;font-weight:700;color:#14281d;line-height:1.25;">{{ $barangay['name'] }}</div>
        <div style="font-size:14px;color:#5f6d63;padding-top:4px;">{{ $barangay['municipality'] }}</div>
    </td>
    </tr>

    <tr><td style="padding:24px 32px 0;"><div style="border-top:1px solid #e2e7e3;font-size:0;line-height:0;">&nbsp;</div></td></tr>

    {{-- Message --}}
    <tr>
    <td style="padding:24px 32px 0;">
        {{ $slot }}
    </td>
    </tr>

    <tr><td style="padding:24px 32px 0;"><div style="border-top:1px solid #e2e7e3;font-size:0;line-height:0;">&nbsp;</div></td></tr>

    {{-- Sign-off --}}
    <tr>
    <td style="padding:20px 32px 0;">
        @isset($signoff)
            {{ $signoff }}
        @else
            <p style="margin:0;font-size:15px;color:#2f3b34;line-height:1.6;">Regards,</p>
            <p style="margin:0;font-size:15px;font-weight:700;color:#14281d;line-height:1.6;">{{ $barangay['name'] }}</p>
        @endisset
    </td>
    </tr>

    {{-- Contact strip --}}
    @if ($footerCells)
        <tr>
        <td style="padding:24px 24px 12px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="background-color:#f2f6f2;border-radius:10px;">
            <tr>
                @if ($facebookName)
                    <td width="{{ intdiv(100, $footerCells) }}%" valign="middle" style="padding:16px 12px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="34" height="34" align="center" valign="middle"
                                style="background-color:#0b5e35;border-radius:17px;color:#ffffff;font-family:Georgia,'Times New Roman',serif;font-size:19px;font-weight:700;line-height:34px;">f</td>
                            <td style="padding-left:10px;font-size:12px;color:#3d4a42;line-height:1.4;">
                                @if ($facebookUrl)
                                    <a href="{{ $facebookUrl }}" style="color:#3d4a42;text-decoration:none;">{{ $facebookName }}</a>
                                @else
                                    {{ $facebookName }}
                                @endif
                                <div style="color:#6b7a71;">{{ $barangay['municipality'] }}</div>
                            </td>
                        </tr>
                        </table>
                    </td>
                @endif

                @if ($barangay['email'] ?? null)
                    <td width="{{ intdiv(100, $footerCells) }}%" valign="middle" style="padding:16px 12px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="34" align="center" valign="middle" style="color:#0b5e35;font-size:20px;line-height:34px;">&#9993;</td>
                            <td style="padding-left:10px;font-size:12px;color:#3d4a42;line-height:1.4;word-break:break-all;">
                                <a href="mailto:{{ $barangay['email'] }}" style="color:#3d4a42;text-decoration:none;">{{ $barangay['email'] }}</a>
                            </td>
                        </tr>
                        </table>
                    </td>
                @endif

                @if ($barangay['phone'] ?? null)
                    <td width="{{ intdiv(100, $footerCells) }}%" valign="middle" style="padding:16px 12px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="34" align="center" valign="middle" style="color:#0b5e35;font-size:19px;line-height:34px;">&#9742;</td>
                            <td style="padding-left:10px;font-size:12px;color:#3d4a42;line-height:1.4;">{{ $barangay['phone'] }}</td>
                        </tr>
                        </table>
                    </td>
                @endif
            </tr>
            </table>
        </td>
        </tr>
    @endif

    <tr>
    <td style="padding:0 32px 28px;">
        <p style="margin:0;font-size:11px;color:#9aa79f;text-align:center;line-height:1.5;">
            This is an automated message from {{ $barangay['name'] }}. Please do not reply to this email.
        </p>
    </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
