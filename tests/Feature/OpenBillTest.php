<?php

namespace Tests\Feature;

use App\Models\CafeTable;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenBillTest extends TestCase
{
    use RefreshDatabase;

    private User $kasir;
    private Product $air;   // Rp4.000, no modifiers
    private Product $esTeh; // Rp6.000, modifiers with free defaults
    private CafeTable $meja;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($this->kasir);
        $this->air = Product::where('sku', 'MNM-003')->firstOrFail();
        $this->esTeh = Product::where('sku', 'MNM-002')->firstOrFail();
        $this->meja = CafeTable::where('name', 'Meja 2')->firstOrFail();
    }

    private function item(Product $p, int $qty): array
    {
        return ['id' => $p->id, 'name' => $p->name, 'quantity' => $qty];
    }

    private function openBill(array $items, array $info = [])
    {
        return $this->actingAs($this->kasir)->postJson('/pos/bills', array_merge([
            'items' => $items,
            'order_type' => 'dine_in',
            'table_id' => $this->meja->id,
        ], $info));
    }

    private function pay(Transaction $bill, array $data = [], ?User $as = null)
    {
        return $this->actingAs($as ?? $this->kasir)->postJson("/pos/bills/{$bill->id}/pay", array_merge([
            'payment_method' => 'cash',
            'discount_amount' => 0,
            'paid_amount' => 1000000,
        ], $data));
    }

    public function test_open_bill_deducts_stock_but_is_not_revenue(): void
    {
        $stock = $this->air->stock;

        $response = $this->openBill([$this->item($this->air, 2)])->assertOk();
        $bill = Transaction::findOrFail($response->json('transaction.id'));

        $this->assertSame('open', $bill->status);
        $this->assertNull($bill->paid_at);
        $this->assertNull($bill->payment_method);
        $this->assertEquals(8000, (float) $bill->total_amount);
        $this->assertSame($stock - 2, $this->air->fresh()->stock);

        $owner = $this->demoUser('owner@smartkasir.com');
        $dashboard = $this->actingAs($owner)->get('/dashboard')->assertOk();
        $this->assertEquals(0, $dashboard->viewData('todayRevenue'));
        $this->assertEquals(0, $dashboard->viewData('todayTransactionsCount'));

        $this->actingAs($owner)->get('/reports')->assertOk()->assertDontSee($bill->invoice_number);
        $this->actingAs($owner)->get('/reports/export-csv')->assertOk();

        $this->actingAs($this->kasir)->get('/shifts/close')->assertOk()->assertViewHas('cashSales', 0);
    }

    public function test_items_can_be_added_and_bill_listed(): void
    {
        $bill = Transaction::find($this->openBill([$this->item($this->air, 1)])->json('transaction.id'));

        $this->actingAs($this->kasir)->postJson("/pos/bills/{$bill->id}/items", [
            'items' => [$this->item($this->esTeh, 2)],
        ])->assertOk();

        $bill->refresh();
        $this->assertCount(2, $bill->details);
        $this->assertEquals(4000 + 12000, (float) $bill->total_amount);

        $this->actingAs($this->kasir)->getJson('/pos/bills')
            ->assertOk()
            ->assertJsonPath('bills.0.id', $bill->id)
            ->assertJsonPath('bills.0.table_name', 'Meja 2');

        $this->actingAs($this->kasir)->getJson("/pos/bills/{$bill->id}")
            ->assertOk()
            ->assertJsonCount(2, 'bill.details');
    }

    public function test_table_can_only_have_one_open_bill(): void
    {
        $this->openBill([$this->item($this->air, 1)])->assertOk();

        $this->openBill([$this->item($this->air, 1)])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertSame(1, Transaction::open()->count());

        // Paying upfront for another group at the same table is still allowed.
        $this->actingAs($this->kasir)->postJson('/pos/checkout', [
            'items' => [$this->item($this->air, 1)],
            'order_type' => 'dine_in',
            'table_id' => $this->meja->id,
            'payment_method' => 'qris',
            'paid_amount' => 0,
        ])->assertOk();
    }

    public function test_take_away_bill_needs_customer_name(): void
    {
        $this->openBill([$this->item($this->air, 1)], ['order_type' => 'take_away', 'table_id' => null])
            ->assertStatus(422)->assertJsonValidationErrors('customer_name');

        $this->openBill([$this->item($this->air, 1)], ['order_type' => 'take_away', 'table_id' => null, 'customer_name' => 'Rina'])
            ->assertOk();
    }

    public function test_paying_whole_bill_credits_the_paying_cashiers_shift(): void
    {
        $bill = Transaction::find($this->openBill([$this->item($this->air, 3)])->json('transaction.id'));

        // A different cashier (admin) with their own shift takes the payment.
        $admin = $this->demoUser('admin@smartkasir.com');
        $adminShift = $this->openShiftFor($admin);

        $response = $this->pay($bill, ['discount_amount' => 2000, 'paid_amount' => 20000], $admin)->assertOk();
        $this->assertStringContainsString('Kembali', $response->json('receipt_html'));

        $bill->refresh();
        $this->assertSame('paid', $bill->status);
        $this->assertNotNull($bill->paid_at);
        $this->assertSame($admin->id, $bill->app_user_id);
        $this->assertSame($this->kasir->id, $bill->opened_by);
        $this->assertSame($adminShift->id, $bill->app_shift_id);
        $this->assertEquals(10000, (float) $bill->final_amount);
        $this->assertEquals(10000, (float) $bill->change_amount);

        $this->actingAs($admin)->get('/shifts/close')->assertViewHas('cashSales', fn ($v) => (float) $v === 10000.0);
        $this->actingAs($this->kasir)->get('/shifts/close')->assertViewHas('cashSales', fn ($v) => (float) $v === 0.0);

        // Closed bill can no longer be paid or extended.
        $this->pay($bill)->assertStatus(422);
        $this->actingAs($this->kasir)->postJson("/pos/bills/{$bill->id}/items", ['items' => [$this->item($this->air, 1)]])
            ->assertStatus(422);
    }

    public function test_underpayment_keeps_bill_open(): void
    {
        $bill = Transaction::find($this->openBill([$this->item($this->air, 3)])->json('transaction.id'));

        $this->pay($bill, ['paid_amount' => 5000])->assertStatus(422);
        $this->assertSame('open', $bill->fresh()->status);
    }

    public function test_split_bill_pays_selected_quantities_only(): void
    {
        $bill = Transaction::find($this->openBill([
            $this->item($this->air, 3),   // 12.000
            $this->item($this->esTeh, 1), //  6.000
        ])->json('transaction.id'));
        $airLine = $bill->details->firstWhere('app_product_id', $this->air->id);
        $tehLine = $bill->details->firstWhere('app_product_id', $this->esTeh->id);

        // Pay 1 of 3 Air + the Es Teh.
        $response = $this->pay($bill, [
            'payment_method' => 'qris',
            'lines' => [
                ['detail_id' => $airLine->id, 'quantity' => 1],
                ['detail_id' => $tehLine->id, 'quantity' => 1],
            ],
        ])->assertOk();

        $part = Transaction::findOrFail($response->json('transaction.id'));
        $this->assertNotSame($bill->id, $part->id);
        $this->assertSame('paid', $part->status);
        $this->assertSame($bill->id, $part->split_from_id);
        $this->assertSame($bill->queue_number, $part->queue_number);
        $this->assertSame('Meja 2', $part->table_name);
        $this->assertEquals(4000 + 6000, (float) $part->final_amount);

        $bill->refresh();
        $this->assertSame('open', $bill->status);
        $this->assertEquals(8000, (float) $bill->total_amount);
        $this->assertSame(2, $bill->details->firstWhere('id', $airLine->id)->quantity);
        $this->assertNull($bill->details->firstWhere('id', $tehLine->id));

        // Paying the rest with an explicit full selection closes the bill itself.
        $this->pay($bill, ['lines' => [['detail_id' => $airLine->id, 'quantity' => 2]]])->assertOk();
        $this->assertSame('paid', $bill->fresh()->status);
        $this->assertSame(0, Transaction::open()->count());

        // Queue numbers stay unique for new orders after a split reused one.
        $next = $this->openBill([$this->item($this->air, 1)])->json('transaction');
        $this->assertSame($bill->queue_number + 1, $next['queue_number']);
    }

    public function test_split_rejects_lines_from_another_bill_or_excess_quantity(): void
    {
        $bill = Transaction::find($this->openBill([$this->item($this->air, 2)])->json('transaction.id'));
        $other = Transaction::find($this->openBill([$this->item($this->air, 1)], [
            'order_type' => 'take_away', 'table_id' => null, 'customer_name' => 'Joko',
        ])->json('transaction.id'));

        $this->pay($bill, ['lines' => [['detail_id' => $other->details->first()->id, 'quantity' => 1]]])->assertStatus(422);
        $this->pay($bill, ['lines' => [['detail_id' => $bill->details->first()->id, 'quantity' => 5]]])->assertStatus(422);
        $this->pay($bill, ['lines' => [['detail_id' => $bill->details->first()->id, 'quantity' => 0]]])->assertStatus(422);

        $this->assertSame(2, Transaction::open()->count());
        $this->assertSame(0, Transaction::paid()->count());
    }

    public function test_cancel_restores_stock_and_requires_reason(): void
    {
        $stock = $this->air->stock;
        $bill = Transaction::find($this->openBill([$this->item($this->air, 2)])->json('transaction.id'));

        $this->actingAs($this->kasir)->postJson("/pos/bills/{$bill->id}/cancel", [])
            ->assertStatus(422)->assertJsonValidationErrors('reason');

        $this->actingAs($this->kasir)->postJson("/pos/bills/{$bill->id}/cancel", ['reason' => 'Pelanggan batal'])
            ->assertOk();

        $bill->refresh();
        $this->assertSame('cancelled', $bill->status);
        $this->assertSame('Pelanggan batal', $bill->cancel_reason);
        $this->assertSame($this->kasir->id, $bill->cancelled_by);
        $this->assertSame($stock, $this->air->fresh()->stock);

        $this->pay($bill)->assertStatus(422);
        // Table is free again.
        $this->openBill([$this->item($this->air, 1)])->assertOk();
    }

    public function test_temporary_bill_receipt_is_marked_unpaid(): void
    {
        $bill = Transaction::find($this->openBill([$this->item($this->air, 1)])->json('transaction.id'));

        // Any cashier may print an open bill.
        $admin = $this->demoUser('admin@smartkasir.com');
        $this->actingAs($admin)->get("/pos/receipt/{$bill->id}")
            ->assertOk()
            ->assertSee('BILL SEMENTARA')
            ->assertDontSee('Kembali:');
    }
}
