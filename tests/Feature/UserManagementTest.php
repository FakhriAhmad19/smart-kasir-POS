<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed();
        return User::where('email', 'owner@smartkasir.com')->first();
    }

    private function kasir(): User
    {
        return User::where('email', 'kasir@smartkasir.com')->first();
    }

    public function test_owner_can_view_user_management(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/users')->assertOk();
    }

    public function test_kasir_is_forbidden_from_user_management(): void
    {
        $owner = $this->owner();
        $kasir = $this->kasir();

        $this->actingAs($kasir)->get('/users')->assertForbidden();
        $this->actingAs($kasir)->post('/users', [])->assertForbidden();
    }

    public function test_admin_is_forbidden_from_user_management(): void
    {
        $this->owner();
        $admin = User::where('email', 'admin@smartkasir.com')->first();

        $this->actingAs($admin)->get('/users')->assertForbidden();
    }

    public function test_owner_can_create_a_new_kasir(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post('/users', [
            'name' => 'Siti Kasir',
            'email' => 'siti@smartkasir.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'kasir',
            'is_active' => '1',
        ])->assertRedirect('/users');

        $user = User::where('email', 'siti@smartkasir.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('kasir'));
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_create_user_validates_password_confirmation_and_role(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->post('/users', [
            'name' => 'Gagal',
            'email' => 'gagal@smartkasir.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda-sekali',
            'role' => 'superadmin', // not assignable
        ])->assertSessionHasErrors(['password', 'role']);

        $this->assertNull(User::where('email', 'gagal@smartkasir.com')->first());
    }

    public function test_owner_can_update_role_and_reset_password(): void
    {
        $owner = $this->owner();
        $kasir = $this->kasir();

        $this->actingAs($owner)->put("/users/{$kasir->id}", [
            'name' => $kasir->name,
            'email' => $kasir->email,
            'role' => 'admin',
            'password' => 'sandiBaru9',
            'password_confirmation' => 'sandiBaru9',
            'is_active' => '1',
        ])->assertRedirect('/users');

        $kasir->refresh();
        $this->assertTrue($kasir->hasRole('admin'));
        $this->assertFalse($kasir->hasRole('kasir'));
        $this->assertTrue(Hash::check('sandiBaru9', $kasir->password));
    }

    public function test_owner_cannot_change_own_role(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->put("/users/{$owner->id}", [
            'name' => $owner->name,
            'email' => $owner->email,
            'role' => 'kasir',
        ])->assertSessionHas('error');

        $owner->refresh();
        $this->assertTrue($owner->hasRole('owner'));
    }

    public function test_owner_cannot_delete_own_account(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->delete("/users/{$owner->id}")->assertSessionHas('error');

        $this->assertNotNull(User::find($owner->id));
    }

    public function test_user_without_transactions_is_hard_deleted(): void
    {
        $owner = $this->owner();
        $kasir = $this->kasir();

        $this->actingAs($owner)->delete("/users/{$kasir->id}")->assertRedirect('/users');

        $this->assertNull(User::find($kasir->id));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $owner = $this->owner();
        $kasir = $this->kasir();
        $kasir->update(['is_active' => false]);

        $this->post('/login', [
            'email' => $kasir->email,
            'password' => 'password',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }
}
