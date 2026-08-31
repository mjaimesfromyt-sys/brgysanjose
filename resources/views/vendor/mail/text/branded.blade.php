{{--
    Plain-text body for a notification's MailMessage — the alternative part that
    text-only clients show and that spam filters expect to find alongside the
    HTML. The HTML sibling lives in ../html/branded.blade.php.
--}}
@php($barangay = config('barangay'))
{{ $barangay['name'] }}
{{ $barangay['municipality'] }}

{{ $greeting ?? (($level ?? 'info') === 'error' ? 'Whoops!' : 'Hello!') }}
@foreach ($introLines ?? [] as $line)

{{ $line }}
@endforeach
@isset($actionUrl)

{{ $actionText }}: {{ $actionUrl }}
@endisset
@foreach ($outroLines ?? [] as $line)

{{ $line }}
@endforeach

@if (! empty($salutation))
{{ $salutation }}
@else
Regards,
{{ $barangay['name'] }}
@endif

--
{{ $barangay['name'] }}, {{ $barangay['municipality'] }}
@if ($barangay['email'] ?? null)
{{ $barangay['email'] }}
@endif
@if ($barangay['phone'] ?? null)
{{ $barangay['phone'] }}
@endif

This is an automated message. Please do not reply to this email.
