<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Perform a checkout of one unit of the given product for the given cashier.
     */
    private function checkoutOnce(User $cashier, Product $product)
    {
        return $this->actingAs($cashier)->postJson('/pos/checkout', [
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 1,
                    'price' => $product->selling_price,
                    'stock' => $product->stock,
                ],
            ],
            'payment_method' => 'cash',
            'discount_amount' => 0,
            'paid_amount' => $product->selling_price,
        ]);
    }

    public function test_cashier_cannot_view_other_cashiers_receipt(): void
    {
        $this->seed();

        $kasirA = User::where('email', 'kasir@smartkasir.com')->first();

        // A second cashier who owns the transaction.
        $kasirB = User::create([
            'name' => 'Siti (Kasir 2)',
            'email' => 'kasir2@smartkasir.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $kasirB->assignRole('kasir');

        $this->openShiftFor($kasirB);
        $product = Product::first();
        $checkout = $this->checkoutOnce($kasirB, $product);
        $checkout->assertStatus(200);
        $transaction = Transaction::first();

        // Kasir A (not the owner, no financial-reports permission) is forbidden.
        $response = $this->actingAs($kasirA)->get('/pos/receipt/' . $transaction->id);
        $response->assertStatus(403);
    }

    public function test_owner_of_transaction_can_view_receipt(): void
    {
        $this->seed();

        $kasir = User::where('email', 'kasir@smartkasir.com')->first();
        $this->openShiftFor($kasir);
        $product = Product::first();
        $this->checkoutOnce($kasir, $product)->assertStatus(200);
        $transaction = Transaction::first();

        $response = $this->actingAs($kasir)->get('/pos/receipt/' . $transaction->id);
        $response->assertStatus(200);
    }

    public function test_owner_with_financial_reports_permission_can_view_any_receipt(): void
    {
        $this->seed();

        $kasir = User::where('email', 'kasir@smartkasir.com')->first();
        $this->openShiftFor($kasir);
        $product = Product::first();
        $this->checkoutOnce($kasir, $product)->assertStatus(200);
        $transaction = Transaction::first();

        // Owner has the view-financial-reports permission.
        $owner = User::where('email', 'owner@smartkasir.com')->first();
        $this->assertTrue($owner->can('view-financial-reports'));

        $response = $this->actingAs($owner)->get('/pos/receipt/' . $transaction->id);
        $response->assertStatus(200);
    }

    public function test_sequential_checkouts_produce_distinct_invoice_numbers(): void
    {
        $this->seed();

        $kasir = User::where('email', 'kasir@smartkasir.com')->first();
        $this->openShiftFor($kasir);
        $product = Product::first();

        $this->checkoutOnce($kasir, $product->fresh())->assertStatus(200);
        $this->checkoutOnce($kasir, $product->fresh())->assertStatus(200);

        $invoices = Transaction::orderBy('id')->pluck('invoice_number')->all();

        $this->assertCount(2, $invoices);
        $this->assertNotEquals($invoices[0], $invoices[1]);

        // Format preserved: INV/<Ymd>/<4-digit sequence>.
        $todayStr = now()->format('Ymd');
        foreach ($invoices as $invoice) {
            $this->assertMatchesRegularExpression(
                '#^INV/' . $todayStr . '/\d{4}$#',
                $invoice
            );
        }
    }
}
