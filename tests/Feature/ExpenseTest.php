<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_creation_is_recorded(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');

        $this->actingAs($owner)->post('/expenses', [
            'category' => 'Listrik & Air',
            'amount' => 100000,
            'expense_date' => date('Y-m-d'),
            'notes' => 'Token listrik toko 100rb',
        ])->assertRedirect('/expenses');

        $this->assertDatabaseHas('app_expenses', [
            'category' => 'Listrik & Air',
            'amount' => 100000,
        ]);
    }
}
