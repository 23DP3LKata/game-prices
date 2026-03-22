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
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/email', [
            'email' => 'new@example.com',
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
        ]);

        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this->actingAs($user)->patchJson('/api/profile/email', [
            'email' => 'taken@example.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
