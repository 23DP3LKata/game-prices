<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'playerone',
            'email' => 'player@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.nickname', 'player_one')
            ->assertJsonPath('user.email', 'player@example.com')
            ->assertJsonPath('user.role', 'user');

        $this->assertDatabaseHas('users', [
            'nickname' => 'playerone',
            'email' => 'player@example.com',
            'role' => 'user',
        ]);

        $user = User::where('email', 'player@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('Secret123!', $user->password));
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'player@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'nickname' => 'anotherplayer',
            'email' => 'player@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_requires_complex_password(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'weak_player',
            'email' => 'weak@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_requires_valid_email_format(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'playerone',
            'email' => 'invalid-email',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Please enter a valid email.');
    }

    public function test_registration_requires_alphanumeric_username(): void
    {
        $response = $this->postJson('/api/register', [
            'nickname' => 'player_one',
            'email' => 'player2@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.nickname.0', 'Usernames must only contain alphanumeric characters.');
    }

    public function test_registration_requires_username_to_be_available(): void
    {
        User::factory()->create([
            'nickname' => 'playerone',
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'nickname' => 'playerone',
            'email' => 'player2@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.nickname.0', 'This username is unavailable.');
    }
}