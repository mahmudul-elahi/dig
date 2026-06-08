<?php

namespace Database\Seeders;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::query()->pluck('id');

        if ($userIds->isEmpty()) {
            $userIds = collect([User::factory()->create()->id]);
        }

        Quote::factory()
            ->count(20)
            ->state(fn (): array => [
                'user_id' => $userIds->random(),
            ])
            ->create();
    }
}
