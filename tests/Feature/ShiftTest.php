<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_shift_open_and_close_cycle(): void
    {
        $this->seed();
        $kasir = $this->demoUser('kasir@smartkasir.com');

        // Open Shift
        $this->actingAs($kasir)->post('/shifts/open', [
            'starting_cash' => 200000,
            'notes' => 'Modal awal shift pagi',
        ])->assertRedirect('/pos');

        // Close Shift
        $this->actingAs($kasir)->post('/shifts/close', [
            'actual_ending_cash' => 200000,
            'notes' => 'Tutup shift pas',
        ])->assertRedirect('/shifts');

        $this->assertDatabaseHas('app_shifts', [
            'app_user_id' => $kasir->id,
            'status' => 'closed',
            'starting_cash' => 200000,
            'actual_ending_cash' => 200000,
        ]);
    }
}
