<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'quote' => fake()->sentence(12),
            'source' => fake()->optional()->words(3, true),
            'status' => fake()->randomElement(['active', 'inactive']),
            'reactions_count' => fake()->numberBetween(0, 500),
        ];
    }
}
