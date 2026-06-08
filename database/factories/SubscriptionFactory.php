<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'revenuecat_event_type' => 'INITIAL_PURCHASE',
            'product_id' => 'com.example.premium.monthly',
            'transaction_id' => fake()->uuid(),
            'store' => 'app_store',
            'period_type' => 'normal',
            'purchased_at' => now(),
            'expiration_at' => now()->addMonth(),
        ];
    }
}
