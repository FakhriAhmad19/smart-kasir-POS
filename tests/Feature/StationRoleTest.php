<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_barista_and_kitchen_roles_are_limited_to_their_station(): void
    {
        $this->seed();
        $barista = User::factory()->create(['email' => 'barista@example.com']);
        $barista->assignRole('barista');
        $kitchen = User::factory()->create(['email' => 'kitchen@example.com']);
        $kitchen->assignRole('kitchen');

        $this->actingAs($barista)->get('/tickets/barista')->assertOk();
        $this->actingAs($barista)->get('/tickets/kitchen')->assertForbidden();
        $this->actingAs($kitchen)->get('/tickets/kitchen')->assertOk();
        $this->actingAs($kitchen)->get('/tickets/barista')->assertForbidden();
    }

    public function test_owner_can_assign_station_roles(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');

        $this->actingAs($owner)->post('/users', [
            'name' => 'Petugas Bar',
            'email' => 'petugas.bar@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'barista',
            'is_active' => 1,
        ])->assertRedirect('/users');

        $this->assertTrue(User::where('email', 'petugas.bar@example.com')->firstOrFail()->hasRole('barista'));
    }
}
