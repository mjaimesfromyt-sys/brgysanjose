<?php

namespace App\Providers;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The content id the branded email shell points its <img> at.
     *
     * @see resources/views/components/mail-shell.blade.php
     */
    private const SEAL_CID = 'barangay-seal.png';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel ships Tailwind-flavoured pagination markup by default, which
        // renders unstyled in this Bootstrap app (see the news list).
        Paginator::useBootstrapFive();

        $this->embedSealInOutgoingMail();
    }

    /**
     * Attach the barangay seal to every email whose body asks for it.
     *
     * Emails are read outside this app's domain, so a plain asset() link to the
     * seal is unreachable, and Gmail drops data: URIs — the image has to travel
     * with the message as an inline part. Only mails rendered through the
     * mail-shell component reference the content id, so the attachment is added
     * on those and nothing else. Symfony rewrites the reference to the
     * generated content id when it assembles the message.
     */
    private function embedSealInOutgoingMail(): void
    {
        Event::listen(function (MessageSending $event): void {
            $path = public_path('images/barangay-seal.png');

            if (! is_file($path)) {
                return;
            }

            if (! str_contains((string) $event->message->getHtmlBody(), 'cid:' . self::SEAL_CID)) {
                return;
            }

            $event->message->embedFromPath($path, self::SEAL_CID, 'image/png');
        });
    }
}
