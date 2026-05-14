<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_rename_a_user_via_api(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'nickname' => 'OldNick',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/admin/users/'.$user->id.'/rename', [
            'nickname' => 'NewNick',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.nickname', 'NewNick')
            ->assertJsonPath('message', 'User renamed successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nickname' => 'NewNick',
        ]);
    }

    public function test_admin_can_unblock_a_user_via_api(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'account_status' => 'inactive',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/admin/users/'.$user->id.'/unblock');

        $response
            ->assertOk()
            ->assertJsonPath('user.account_status', 'active')
            ->assertJsonPath('message', 'User unblocked successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_status' => 'active',
        ]);
    }

    public function test_admin_can_delete_a_user_via_api(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($admin)->deleteJson('/api/admin/users/'.$user->id);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}