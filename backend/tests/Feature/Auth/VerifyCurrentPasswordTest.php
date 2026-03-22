<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyCurrentPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_verify_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->postJson('/api/profile/password/verify', [
            'current_password' => 'secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Current password verified.');
    }

    public function test_verify_current_password_rejects_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->postJson('/api/profile/password/verify', [
            'current_password' => 'wrong-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The current password is incorrect.');
    }
}
