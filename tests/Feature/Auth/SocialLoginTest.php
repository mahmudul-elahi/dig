<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_google(): void
    {
        $socialUser = $this->fakeSocialiteUser([
            'id' => 'google-123',
            'name' => 'Google One',
            'email' => 'google@example.com',
            'token' => 'valid-google-token',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(
                Mockery::mock(['stateless' => Mockery::mock(['userFromToken' => $socialUser])])
            );

        $response = $this->postJson('/social/login', [
            'provider' => 'google',
            'id_token' => 'valid-google-token',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['data' => ['id', 'type', 'attributes' => ['first_name', 'last_name', 'email']]],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'provider' => 'google',
            'provider_id' => 'google-123',
            'first_name' => 'Google',
            'last_name' => 'One',
        ]);
    }

    public function test_user_can_login_with_apple(): void
    {
        $socialUser = $this->fakeSocialiteUser([
            'id' => 'apple-456',
            'name' => 'Apple User',
            'email' => 'apple@example.com',
            'token' => 'valid-apple-token',
        ]);

        Socialite::shouldReceive('driver')
            ->with('apple')
            ->andReturn(
                Mockery::mock(['stateless' => Mockery::mock(['userFromToken' => $socialUser])])
            );

        $response = $this->postJson('/social/login', [
            'provider' => 'apple',
            'id_token' => 'valid-apple-token',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['data' => ['id', 'type', 'attributes' => ['first_name', 'last_name', 'email']]],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'apple@example.com',
            'provider' => 'apple',
            'provider_id' => 'apple-456',
        ]);
    }

    public function test_returning_user_gets_token_without_duplicate(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Existing',
            'last_name' => 'User',
            'email' => 'existing@example.com',
            'provider' => 'google',
            'provider_id' => 'google-existing',
        ]);

        $socialUser = $this->fakeSocialiteUser([
            'id' => 'google-existing',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'token' => 'new-token',
        ]);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(
                Mockery::mock(['stateless' => Mockery::mock(['userFromToken' => $socialUser])])
            );

        $response = $this->postJson('/social/login', [
            'provider' => 'google',
            'id_token' => 'new-token',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['data' => ['id', 'type', 'attributes' => ['first_name', 'last_name', 'email']]],
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_fails_with_invalid_provider(): void
    {
        $response = $this->postJson('/social/login', [
            'provider' => 'github',
            'id_token' => 'some-token',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider']);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/social/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['provider', 'id_token']);
    }

    private function fakeSocialiteUser(array $data): SocialiteUser
    {
        $user = new SocialiteUser;

        $user->id = $data['id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->token = $data['token'];
        $user->refreshToken = null;
        $user->expiresIn = 3600;

        return $user;
    }
}
