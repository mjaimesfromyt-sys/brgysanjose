{{--
    Overrides Laravel's built-in notification mail view so that *every*
    MailMessage — the barangay's booking / request / rental / refund updates and
    the framework's own password-reset and verification mail — goes out in the
    barangay's branding, without each notification class having to know about it.

    @include (rather than @component) so the whole MailMessage payload — the
    greeting, lines and action — carries through. "mail::" resolves to
    vendor/mail/html when Laravel renders the HTML part and vendor/mail/text
    when it renders the plain-text alternative, so one line covers both.
--}}
@include('mail::branded')
