<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateProfileEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_email_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/email', [
            'email' => 'new@example.com',
            'current_password' => 'secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.email', 'new@example.com')
            ->assertJsonPath('message', 'Email updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    public function test_email_update_requires_unique_email(): void
    {
        $user = User::factory()->create([
            'email' => 'first@example.com',
            'email_verified_at' => now(),
            'password' => 'secret123',
        ]);

        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/email', [
            'email' => 'taken@example.com',
            'current_password' => 'secret123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_unverified_account_cannot_update_email(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
            'email_verified_at' => null,
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/email', [
            'email' => 'new@example.com',
            'current_password' => 'secret123',
        ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'Please verify your account before changing your email address.');
    }

    public function test_email_update_requires_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'email_verified_at' => now(),
            'password' => 'secret123',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/email', [
            'email' => 'new@example.com',
            'current_password' => 'wrong-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }
}
