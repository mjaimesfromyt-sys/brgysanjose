<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegisterPayMongoWebhook extends Command
{
    protected $signature = 'paymongo:webhook
                            {--url= : Webhook URL (defaults to APP_URL . /webhooks/paymongo)}
                            {--events=checkout_session.payment.paid,payment.paid : Comma-separated event list}
                            {--list : List registered webhook endpoints instead}
                            {--disable= : Disable the given webhook endpoint id}';

    protected $description = 'Register/list/disable the PayMongo webhook endpoint for payment confirmations';

    public function handle(): int
    {
        $secret = config('services.paymongo.secret');

        if (! $secret) {
            $this->error('PAYMONGO_SECRET_KEY is not set in .env');

            return self::FAILURE;
        }

        $http = Http::withBasicAuth($secret, '')
            ->baseUrl('https://api.paymongo.com/v1')
            ->acceptJson();

        if ($this->option('list')) {
            return $this->list($http);
        }

        if ($disable = $this->option('disable')) {
            return $this->disable($http, $disable);
        }

        return $this->register($http);
    }

    private function register($http): int
    {
        $url = $this->option('url') ?: rtrim(config('app.url'), '/') . '/webhooks/paymongo';
        $events = array_filter(array_map('trim', explode(',', (string) $this->option('events'))));

        $this->info("Registering webhook: {$url}");
        $this->info('Events: ' . implode(', ', $events));

        $response = $http->post('/webhooks', [
            'data' => [
                'attributes' => [
                    'url' => $url,
                    'events' => $events,
                ],
            ],
        ]);

        if ($response->failed()) {
            $this->error('Registration failed: ' . $response->body());
            Log::error('PayMongo webhook registration failed', ['body' => $response->body()]);

            return self::FAILURE;
        }

        $data = $response->json('data');

        $this->newLine();
        $this->info('✅ Webhook registered!');
        $this->line('   Endpoint ID:    ' . ($data['id'] ?? '?'));
        $this->line('   URL:            ' . ($data['attributes']['url'] ?? $url));
        $this->line('   Secret key:     ' . ($data['attributes']['secret_key'] ?? '?'));
        $this->newLine();
        $this->warn('Add this line to .env on the server, then run: php artisan config:clear');
        $this->line('PAYMONGO_WEBHOOK_SECRET=' . ($data['attributes']['secret_key'] ?? '?'));

        return self::SUCCESS;
    }

    private function list($http): int
    {
        $response = $http->get('/webhooks');

        if ($response->failed()) {
            $this->error('Listing failed: ' . $response->body());

            return self::FAILURE;
        }

        $webhooks = $response->json('data') ?? [];

        if ($webhooks === []) {
            $this->line('No webhook endpoints registered.');

            return self::SUCCESS;
        }

        foreach ($webhooks as $wh) {
            $this->line($wh['id'] . '  ' . ($wh['attributes']['url'] ?? '?'));
            $this->line('   events: ' . implode(', ', $wh['attributes']['events'] ?? []));
            $this->line('   status: ' . (! empty($wh['attributes']['enabled']) ? 'enabled' : 'disabled'));
        }

        return self::SUCCESS;
    }

    private function disable($http, string $id): int
    {
        $response = $http->post("/webhooks/{$id}/disable");

        if ($response->failed()) {
            $this->error('Disable failed: ' . $response->body());

            return self::FAILURE;
        }

        $this->info("Webhook {$id} disabled.");

        return self::SUCCESS;
    }
}
