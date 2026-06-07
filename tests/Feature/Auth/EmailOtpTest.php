<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use App\Notifications\SendOtpVerification;

class EmailOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_and_verify_otp_flow(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'otpuser@example.com', 'email_verified_at' => null]);

        // send OTP — use the service so we can fetch the otp value synchronously in tests
        $otp = app(\App\Services\EmailOtpService::class)->sendFor($user);

        Notification::assertSentTo($user, SendOtpVerification::class, function ($notification) use (&$otp) {
            return $notification->otp === $otp;
        });

        // verify with wrong otp
        $this->postJson('email/otp/verify', ['email' => $user->email, 'otp' => '00000'])
            ->assertStatus(422);

        // verify with correct otp
        $this->postJson('email/otp/verify', ['email' => $user->email, 'otp' => $otp])
            ->assertOk();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_resend_otp(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'resend@example.com', 'email_verified_at' => null]);

        $this->postJson('email/otp/resend', ['email' => $user->email])->assertOk();

        Notification::assertSentTo($user, SendOtpVerification::class);
    }
}
