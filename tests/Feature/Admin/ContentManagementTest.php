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
}
