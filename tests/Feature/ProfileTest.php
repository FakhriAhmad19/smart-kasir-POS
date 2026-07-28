<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        $this->seed();
        return User::where('email', 'kasir@smartkasir.com')->first();
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->get('/profile')->assertOk();
    }

    public function test_user_can_update_name_and_email(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->put('/profile', [
            'name' => 'Budi Diperbarui',
            'email' => 'budi.baru@smartkasir.com',
        ])->assertRedirect('/profile');

        $kasir->refresh();
        $this->assertSame('Budi Diperbarui', $kasir->name);
        $this->assertSame('budi.baru@smartkasir.com', $kasir->email);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->put('/profile', [
            'name' => $kasir->name,
            'email' => $kasir->email,
            'current_password' => 'password',
            'password' => 'sandiBaru9',
            'password_confirmation' => 'sandiBaru9',
        ])->assertRedirect('/profile');

        $kasir->refresh();
        $this->assertTrue(Hash::check('sandiBaru9', $kasir->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->put('/profile', [
            'name' => $kasir->name,
            'email' => $kasir->email,
            'current_password' => 'sandi-salah',
            'password' => 'sandiBaru9',
            'password_confirmation' => 'sandiBaru9',
        ])->assertSessionHasErrors('current_password');

        $kasir->refresh();
        $this->assertTrue(Hash::check('password', $kasir->password));
    }

    public function test_email_must_be_unique_across_other_users(): void
    {
        $kasir = $this->kasir();

        $this->actingAs($kasir)->put('/profile', [
            'name' => $kasir->name,
            'email' => 'owner@smartkasir.com',
        ])->assertSessionHasErrors('email');

        $kasir->refresh();
        $this->assertSame('kasir@smartkasir.com', $kasir->email);
    }
}
