<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed();
        return User::where('email', 'owner@smartkasir.com')->first();
    }

    public function test_reports_index_loads(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/reports')->assertOk();
    }

    public function test_reconciliation_loads(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get('/reports/reconciliation')->assertOk();
    }

    public function test_export_csv_returns_csv_response(): void
    {
        $owner = $this->owner();

        $response = $this->actingAs($owner)->get('/reports/export-csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_summary_figures_reflect_transactions(): void
    {
        $owner = $this->owner();
        $today = now()->today()->format('Y-m-d');

        $trx = Transaction::create([
            'invoice_number' => 'INV-TEST-1',
            'app_user_id' => $owner->id,
            'app_shift_id' => null,
            'total_amount' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'final_amount' => 50000,
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'change_amount' => 0,
            'notes' => null,
        ]);

        $product = Product::first();

        TransactionDetail::create([
            'app_transaction_id' => $trx->id,
            'app_product_id' => $product->id,
            'quantity' => 2,
            'purchase_price' => 10000,
            'selling_price' => 25000,
            'subtotal' => 50000,
        ]);

        // Profit = (25000 - 10000) * 2 = 30000
        $response = $this->actingAs($owner)->get('/reports?start_date=' . $today . '&end_date=' . $today);

        $response->assertOk();
        $response->assertSee('INV-TEST-1');
        $response->assertSee(number_format(50000, 0, ',', '.'));
        $response->assertSee(number_format(30000, 0, ',', '.'));
    }

    public function test_owner_can_export_any_shift_pdf(): void
    {
        $owner = $this->owner();
        $kasir = User::where('email', 'kasir@smartkasir.com')->first();
        $shift = $this->openShiftFor($kasir);

        // Owner memegang izin view-financial-reports, boleh mengekspor shift kasir lain.
        $this->actingAs($owner)->get("/reports/shift-pdf/{$shift->id}")->assertOk();
    }

    public function test_kasir_cannot_export_shift_pdf(): void
    {
        $this->owner(); // seed
        $kasir = User::where('email', 'kasir@smartkasir.com')->first();
        $shift = $this->openShiftFor($kasir);

        // Kasir tidak punya izin view-financial-reports → ditolak (IDOR/otorisasi).
        $this->actingAs($kasir)->get("/reports/shift-pdf/{$shift->id}")->assertForbidden();
    }
}
