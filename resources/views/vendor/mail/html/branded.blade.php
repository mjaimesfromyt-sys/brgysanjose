{{--
    HTML body for a notification's MailMessage.

    Laravel points the "mail" view namespace at html/ when it renders the HTML
    part of a message and at text/ when it renders the plain-text part, so this
    file and its sibling in ../text/ are picked automatically — see
    resources/views/vendor/notifications/email.blade.php.

    From MailMessage::data(): $level, $greeting, $salutation, $introLines,
    $outroLines, $actionText, $actionUrl.
--}}
<x-mail-shell :title="$subject ?? null">
    <p style="margin:0 0 16px;font-size:17px;font-weight:700;color:#14281d;line-height:1.5;">
        {{ $greeting ?? (($level ?? 'info') === 'error' ? 'Whoops!' : 'Hello!') }}
    </p>

    @foreach ($introLines ?? [] as $line)
        <p style="margin:0 0 14px;font-size:15px;color:#2f3b34;line-height:1.6;">{{ $line }}</p>
    @endforeach

    @isset($actionUrl)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:8px 0 18px;">
        <tr>
        <td align="center">
            <a href="{{ $actionUrl }}"
               style="display:inline-block;background-color:#0b5e35;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 32px;border-radius:6px;">{{ $actionText }}</a>
        </td>
        </tr>
        </table>
    @endisset

    @foreach ($outroLines ?? [] as $line)
        <p style="margin:0 0 14px;font-size:15px;color:#2f3b34;line-height:1.6;">{{ $line }}</p>
    @endforeach

    @if (! empty($salutation))
        <x-slot:signoff>
            <p style="margin:0;font-size:15px;color:#2f3b34;line-height:1.6;white-space:pre-line;">{{ $salutation }}</p>
        </x-slot:signoff>
    @endif
</x-mail-shell>
