<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_a_user_and_returns_a_token(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'Aung Aung',
            'email' => 'aung@example.com',
            'phone' => '+959123456789',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Registration successful')
            ->assertJsonPath('user.email', 'aung@example.com')
            ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $user = User::where('email', 'aung@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_register_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/register', [
            'name' => 'Aung Aung',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_register_requires_a_confirmed_password(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Aung Aung',
            'email' => 'aung@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'different',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_login_returns_a_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'aung@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'aung@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['token']);
    }

    public function test_login_rejects_a_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'aung@example.com',
            'password' => Hash::make('secret-password'),
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'aung@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_the_user_endpoint_requires_a_token(): void
    {
        $this->getJson('/api/v1/user')->assertUnauthorized();
    }

    public function test_the_user_endpoint_returns_the_profile(): void
    {
        $user = User::factory()->create();
        UserProfile::factory()->for($user)->create(['goal' => 'build_muscle']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/user')
            ->assertOk()
            ->assertJsonPath('id', $user->id)
            ->assertJsonPath('profile.goal', 'build_muscle');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully');

        $this->assertSame(0, $user->tokens()->count());

        // The guard caches the resolved user for the lifetime of the test
        // application, so forget it before replaying the revoked token.
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/user')
            ->assertUnauthorized();
    }
}
