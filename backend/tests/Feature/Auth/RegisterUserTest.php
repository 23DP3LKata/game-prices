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
            'nickname' => 'player_one',
            'email' => 'player@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.nickname', 'player_one')
            ->assertJsonPath('user.email', 'player@example.com')
            ->assertJsonPath('user.role', 'user');

        $this->assertDatabaseHas('users', [
            'nickname' => 'player_one',
            'email' => 'player@example.com',
            'role' => 'user',
        ]);

        $user = User::where('email', 'player@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'player@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'nickname' => 'another_player',
            'email' => 'player@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}