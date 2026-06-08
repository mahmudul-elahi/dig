<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_a_quote_and_liking_twice_is_idempotent(): void
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create(['reactions_count' => 0]);

        $firstResponse = $this->withToken($user->createToken('auth')->plainTextToken)
            ->postJson("/quotes/{$quote->id}/likes");

        $firstResponse->assertCreated()
            ->assertJsonPath('data.attributes.reactions_count', 1)
            ->assertJsonPath('data.attributes.is_liked', true);

        $secondResponse = $this->withToken($user->createToken('auth')->plainTextToken)
            ->postJson("/quotes/{$quote->id}/likes");

        $secondResponse->assertOk()
            ->assertJsonPath('data.attributes.reactions_count', 1)
            ->assertJsonPath('data.attributes.is_liked', true);

        $this->assertDatabaseCount('quote_likes', 1);
        $this->assertDatabaseHas('quote_likes', [
            'user_id' => $user->id,
            'quote_id' => $quote->id,
        ]);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'reactions_count' => 1,
        ]);
    }

    public function test_user_can_unlike_a_quote(): void
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create(['reactions_count' => 0]);

        $this->withToken($user->createToken('auth')->plainTextToken)
            ->postJson("/quotes/{$quote->id}/likes")
            ->assertCreated();

        $response = $this->withToken($user->createToken('auth')->plainTextToken)
            ->deleteJson("/quotes/{$quote->id}/likes");

        $response->assertOk()
            ->assertJsonPath('data.attributes.reactions_count', 0)
            ->assertJsonPath('data.attributes.is_liked', false);

        $this->assertDatabaseMissing('quote_likes', [
            'user_id' => $user->id,
            'quote_id' => $quote->id,
        ]);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'reactions_count' => 0,
        ]);
    }

    public function test_unauthenticated_users_cannot_manage_quote_likes(): void
    {
        $quote = Quote::factory()->create();

        $this->postJson("/quotes/{$quote->id}/likes")->assertUnauthorized();
        $this->deleteJson("/quotes/{$quote->id}/likes")->assertUnauthorized();
    }
}
