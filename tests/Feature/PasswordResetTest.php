<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_loads_for_guests(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot your password?');
    }

    public function test_reset_link_email_is_sent_to_existing_user(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        $response->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, \App\Notifications\ResetPasswordNotification::class);
    }

    public function test_unknown_email_gets_same_response_without_sending(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

        $response->assertRedirect()->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_via_signed_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        $tokenRow = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($tokenRow, 'A reset token row should be created');

        $notification = Notification::sent($user, \App\Notifications\ResetPasswordNotification::class)->first();

        // Signed reset URL exactly like the one inside the email.
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(60),
            ['token' => $notification->token, 'email' => $user->email],
        );

        $this->get($url)
            ->assertOk()
            ->assertSee('Choose a new password');

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-Secret-123',
            'password_confirmation' => 'new-Secret-123',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-Secret-123', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_tampered_token_is_rejected(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post(route('password.email'), ['email' => $user->email]);

        $notification = Notification::sent($user, \App\Notifications\ResetPasswordNotification::class)->first();

        $this->post(route('password.update'), [
            'token' => $notification->token . 'xx',
            'email' => $user->email,
            'password' => 'new-Secret-123',
            'password_confirmation' => 'new-Secret-123',
        ])->assertSessionHas('status');

        $this->assertFalse(Hash::check('now-Secret-123', $user->fresh()->password));
    }

    public function test_expired_reset_page_is_rejected(): void
    {
        // Unsigned link => InvalidSignatureException => 403 from the signed middleware.
        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'x@example.com']))
            ->assertForbidden();
    }
}
