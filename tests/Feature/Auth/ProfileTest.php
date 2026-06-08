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

        $response = $this->withToken($token)->getJson('/me');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'type', 'attributes' => ['first_name', 'last_name', 'email', 'email_verified_at', 'created_at', 'updated_at']],
            ])
            ->assertJsonPath('data.id', (string) $user->id)
            ->assertJsonPath('data.attributes.email', $user->email);
    }

    public function test_user_can_update_their_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/user', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.first_name', 'Updated')
            ->assertJsonPath('data.attributes.last_name', 'Name');

        $this->assertSame('Updated', $user->fresh()->first_name);
        $this->assertSame('Name', $user->fresh()->last_name);
    }

    public function test_user_can_update_their_email(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/user', [
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

        $this->withToken($token)->patchJson('/user', [
            'email' => 'new@example.com',
        ])->assertOk();

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_is_unchanged(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/user', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => $user->email,
        ]);

        $response->assertOk();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_update_requires_authentication(): void
    {
        $response = $this->patchJson('/user', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_fails_with_invalid_email(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/user', [
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

        $response = $this->withToken($token)->patchJson('/user', [
            'email' => 'taken@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_fails_with_empty_first_name(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth')->plainTextToken;

        $response = $this->withToken($token)->patchJson('/user', [
            'first_name' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name']);
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
