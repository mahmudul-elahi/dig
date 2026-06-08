<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJson(['message' => 'Verification OTP sent.']);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        // registration should NOT create a personal access token
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'auth',
        ]);
    }

    public function test_user_can_register_with_a_device_name(): void
    {
        $response = $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJson(['message' => 'Verification OTP sent.']);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        // device_name is no longer supported on registration
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone',
        ]);
    }

    public function test_registered_user_receives_verification_email(): void
    {
        $this->skipUnlessUserMustVerifyEmail();

        Notification::fake();

        $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_fails_without_required_fields(): void
    {
        $response = $this->postJson(route('register'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'email', 'password']);
    }

    public function test_registration_fails_with_invalid_email(): void
    {
        $response = $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_mismatched_password_confirmation(): void
    {
        $response = $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson(route('register'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_endpoint_allows_up_to_five_attempts_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson(route('register'), [
                'first_name' => "User {$i}",
                'last_name' => null,
                'email' => "user{$i}@example.com",
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $this->assertNotSame(429, $response->status());
        }
    }

    public function test_register_endpoint_is_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson(route('register'), [
                'first_name' => "User {$i}",
                'last_name' => null,
                'email' => "user{$i}@example.com",
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);
        }

        $response = $this->postJson(route('register'), [
            'first_name' => 'Final',
            'last_name' => 'User',
            'email' => 'final@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertTooManyRequests();
    }

    public function test_register_throttle_keys_per_ip(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->postJson(route('register'), [
                'first_name' => "User {$i}",
                'last_name' => null,
                'email' => "user{$i}@example.com",
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);
        }

        $this->postJson(route('register'), [
            'first_name' => 'Same',
            'last_name' => 'IP',
            'email' => 'same@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertTooManyRequests();

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.99'])
            ->postJson(route('register'), [
                'first_name' => 'Different',
                'last_name' => 'IP',
                'email' => 'different@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertCreated();
    }
}
