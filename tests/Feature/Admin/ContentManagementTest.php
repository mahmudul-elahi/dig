<?php

namespace Tests\Feature\Admin;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_single_quote(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->withToken($admin->createToken('auth')->plainTextToken)->postJson('/quotes', [
            'quote' => 'The journey within is the most important journey you will ever take.',
            'source' => 'Reflection Collection',
            'status' => 'active',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'quotes')
            ->assertJsonPath('data.attributes.quote', 'The journey within is the most important journey you will ever take.')
            ->assertJsonPath('data.attributes.user_id', $admin->id)
            ->assertJsonPath('data.attributes.status', 'active');

        $this->assertDatabaseHas('quotes', [
            'user_id' => $admin->id,
            'quote' => 'The journey within is the most important journey you will ever take.',
            'status' => 'active',
        ]);
    }

    public function test_user_can_create_a_quote(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->withToken($user->createToken('auth')->plainTextToken)->postJson('/quotes', [
            'quote' => 'A future quote.',
            'source' => 'User submission',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.attributes.user_id', $user->id)
            ->assertJsonPath('data.attributes.quote', 'A future quote.');
    }

    public function test_unauthenticated_users_cannot_manage_quotes(): void
    {
        $response = $this->postJson('/quotes', [
            'quote' => 'A quote',
        ]);

        $response->assertUnauthorized();
    }

    public function test_admin_can_list_quotes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Quote::factory()->create(['user_id' => $admin->id, 'quote' => 'Inactive quote', 'status' => 'inactive']);
        Quote::factory()->create(['user_id' => $admin->id, 'quote' => 'Active quote', 'status' => 'active']);

        $response = $this->withToken($admin->createToken('auth')->plainTextToken)->getJson('/quotes?filter[status]=active');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.quote', 'Active quote');
    }

    public function test_owner_can_update_their_quote(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $quote = Quote::factory()->create(['user_id' => $user->id, 'quote' => 'Original']);

        $response = $this->withToken($user->createToken('auth')->plainTextToken)
            ->patchJson("/quotes/{$quote->id}", ['quote' => 'Updated']);

        $response->assertOk()
            ->assertJsonPath('data.attributes.quote', 'Updated')
            ->assertJsonPath('data.attributes.is_liked', false);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'quote' => 'Updated']);
    }

    public function test_admin_can_update_another_users_quote(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $quote = Quote::factory()->create(['user_id' => $owner->id, 'quote' => 'Original']);

        $response = $this->withToken($admin->createToken('auth')->plainTextToken)
            ->patchJson("/quotes/{$quote->id}", ['quote' => 'Moderated']);

        $response->assertOk()->assertJsonPath('data.attributes.quote', 'Moderated');
    }

    public function test_user_cannot_update_another_users_quote(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $intruder = User::factory()->create(['role' => 'user']);
        $quote = Quote::factory()->create(['user_id' => $owner->id, 'quote' => 'Original']);

        $response = $this->withToken($intruder->createToken('auth')->plainTextToken)
            ->patchJson("/quotes/{$quote->id}", ['quote' => 'Hijacked']);

        $response->assertForbidden();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'quote' => 'Original']);
    }

    public function test_owner_can_delete_their_quote(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $quote = Quote::factory()->create(['user_id' => $user->id]);

        $response = $this->withToken($user->createToken('auth')->plainTextToken)
            ->deleteJson("/quotes/{$quote->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }

    public function test_user_cannot_delete_another_users_quote(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $intruder = User::factory()->create(['role' => 'user']);
        $quote = Quote::factory()->create(['user_id' => $owner->id]);

        $response = $this->withToken($intruder->createToken('auth')->plainTextToken)
            ->deleteJson("/quotes/{$quote->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    public function test_quote_show_reports_like_state_for_authenticated_user(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $quote = Quote::factory()->create(['reactions_count' => 0]);

        $this->withToken($user->createToken('auth')->plainTextToken)
            ->postJson("/quotes/{$quote->id}/likes")
            ->assertCreated();

        $response = $this->withToken($user->createToken('auth')->plainTextToken)
            ->getJson("/quotes/{$quote->id}");

        $response->assertOk()
            ->assertJsonPath('data.attributes.is_liked', true)
            ->assertJsonPath('data.attributes.reactions_count', 1);
    }
}
