<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_role_protected_route(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('auth')->plainTextToken;

        // register a temporary route for testing
        Route::get('/role-test-admin', function () {
            return response()->json(['ok' => true]);
        })->middleware(['auth:sanctum', 'role:admin']);

        $response = $this->withToken($token)->getJson('/role-test-admin');

        $response->assertOk()->assertJson(['ok' => true]);
    }

    public function test_non_admin_gets_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('auth')->plainTextToken;

        Route::get('/role-test-admin-2', function () {
            return response()->json(['ok' => true]);
        })->middleware(['auth:sanctum', 'role:admin']);

        $response = $this->withToken($token)->getJson('/role-test-admin-2');

        $response->assertForbidden();
    }
}
