<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XBR3BKT3RT"></script>

<script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }

    gtag('js', new Date());

    gtag('config', 'G-XBR3BKT3RT');

    @if (session('ga_event') === 'sign_up')
        gtag('event', 'sign_up', {
            method: 'email_otp'
        });
    @endif
</script>