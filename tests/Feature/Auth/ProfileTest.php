<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_their_info(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->getJson(route('profile.show'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'type', 'attributes' => ['name', 'email', 'email_verified_at', 'created_at', 'updated_at']],
            ])
            ->assertJsonPath('data.id', (string) $user->id)
            ->assertJsonPath('data.attributes.email', $user->email);
    }

    public function test_user_can_update_their_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson(route('profile.update'), [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'Updated Name');

        $this->assertSame('Updated Name', $user->fresh()->name);
    }

    public function test_user_can_update_their_email(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson(route('profile.update'), [
            'email' => 'new@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.email', 'new@example.com');

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_email_verification_is_reset_when_email_changes(): void
    {
        $this->skipUnlessUserMustVerifyEmail();

        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $this->withToken($token)->patchJson(route('profile.update'), [
            'email' => 'new@example.com',
        ])->assertOk();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_is_unchanged(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        $response->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_update_requires_authentication(): void
    {
        $response = $this->patchJson(route('profile.update'), [
            'name' => 'Updated Name',
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_fails_with_invalid_email(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson(route('profile.update'), [
            'email' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson(route('profile.update'), [
            'email' => 'taken@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_fails_with_empty_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson(route('profile.update'), [
            'name' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_user_can_delete_their_account(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }

    public function test_account_deletion_removes_all_personal_access_tokens(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }

    public function test_previous_token_is_invalid_after_account_deletion(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }

    public function test_account_deletion_requires_authentication(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }

    public function test_account_deletion_fails_with_wrong_password(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }

    public function test_account_deletion_fails_without_password(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }

    public function test_account_deletion_is_rejected_for_unverified_user(): void
    {
        $this->markTestSkipped('Account deletion endpoint removed.');
    }
}
