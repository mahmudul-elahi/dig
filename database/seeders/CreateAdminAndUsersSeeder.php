<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            ['first_name' => 'Admin', 'last_name' => 'User', 'email_verified_at' => now(), 'role' => 'admin', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'user1@example.com'],
            ['first_name' => 'User', 'last_name' => 'One', 'email_verified_at' => now(), 'role' => 'user', 'password' => Hash::make('password')]
        );

        User::firstOrCreate(
            ['email' => 'user2@example.com'],
            ['first_name' => 'User', 'last_name' => 'Two', 'email_verified_at' => now(), 'role' => 'user', 'password' => Hash::make('password')]
        );
    }
}
