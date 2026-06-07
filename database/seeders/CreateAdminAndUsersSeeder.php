<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        // Use firstOrCreate so the seeder is idempotent and doesn't overwrite existing passwords
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'role' => 'admin', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'user1@example.com'],
            ['name' => 'User One', 'role' => 'user', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'user2@example.com'],
            ['name' => 'User Two', 'role' => 'user', 'password' => Hash::make('password')]
        );
    }
}
