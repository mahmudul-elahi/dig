<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateAdminAndUsersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use updateOrCreate so the seeder is idempotent
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'role' => 'admin']
        );

        User::updateOrCreate(
            ['email' => 'user1@example.com'],
            ['name' => 'User One', 'role' => 'user']
        );

        User::updateOrCreate(
            ['email' => 'user2@example.com'],
            ['name' => 'User Two', 'role' => 'user']
        );
    }
}
