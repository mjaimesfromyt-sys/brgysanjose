{{--
    Email-verification code, rendered inside the shared branded shell.

    Expects: $residentName, $code.
--}}
<x-mail-shell title="Your OTP verification code">
    <p style="margin:0 0 16px;font-size:17px;font-weight:700;color:#14281d;line-height:1.5;">Hi {{ $residentName }},</p>

    <p style="margin:0 0 14px;font-size:15px;color:#2f3b34;line-height:1.6;">
        Thank you for registering with {{ config('barangay.name') }}.
    </p>
    <p style="margin:0 0 14px;font-size:15px;color:#2f3b34;line-height:1.6;">
        Use the code below to verify your email address:
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#f2f6f2;border-radius:10px;margin:4px 0 18px;">
    <tr>
    <td align="center" style="padding:18px 16px;">
        <div style="font-size:11px;color:#6b7a71;text-transform:uppercase;letter-spacing:.08em;">Verification code</div>
        <div style="font-size:30px;font-weight:700;color:#0b5e35;letter-spacing:.22em;font-family:'Courier New',Courier,monospace;padding-top:6px;">{{ $code }}</div>
    </td>
    </tr>
    </table>

    <p style="margin:0 0 14px;font-size:15px;color:#2f3b34;line-height:1.6;">This code expires in 10 minutes.</p>
    <p style="margin:0;font-size:13px;color:#6b7a71;line-height:1.6;">
        If you did not request this code, you can safely ignore this email.
    </p>
</x-mail-shell>
