<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateProfilePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_password_via_api(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/password', [
            'current_password' => 'secret123',
            'new_password' => 'newsecret123',
            'new_password_confirmation' => 'newsecret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Password updated successfully.');

        $user->refresh();

        $this->assertTrue(Hash::check('newsecret123', $user->password));
    }

    public function test_password_update_rejects_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'newsecret123',
            'new_password_confirmation' => 'newsecret123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'The current password is incorrect.');
    }
}
