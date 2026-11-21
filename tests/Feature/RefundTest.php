<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_refund_paid_transaction_and_restore_stock(): void
    {
        $this->seed();
        Setting::set('supervisor_pin_hash', Hash::make('2468'));
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->openShiftFor($kasir);
        $product = Product::first();
        $initial = $product->stock;

        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => $product->selling_price,
        ])->assertOk();
        $transaction = Transaction::latest('id')->firstOrFail();

        $this->actingAs($owner)->post("/transactions/{$transaction->id}/refund", [
            'supervisor_pin' => '2468',
            'reason' => 'Pesanan dibatalkan pelanggan',
        ])->assertRedirect();

        $this->assertSame(Transaction::STATUS_CANCELLED, $transaction->fresh()->status);
        $this->assertEquals($initial, $product->fresh()->stock);
    }

    public function test_wrong_pin_cannot_void_item(): void
    {
        $this->seed();
        Setting::set('supervisor_pin_hash', Hash::make('2468'));
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->openShiftFor($kasir);
        $product = Product::first();
        $initial = $product->stock;

        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [['id' => $product->id, 'quantity' => 2]],
            'payment_method' => 'cash',
            'paid_amount' => $product->selling_price * 2,
        ])->assertOk();
        $detail = TransactionDetail::latest('id')->firstOrFail();

        $this->actingAs($owner)->post("/transaction-details/{$detail->id}/void", [
            'supervisor_pin' => '0000',
            'quantity' => 1,
            'reason' => 'Salah input',
        ])->assertForbidden();

        $this->assertEquals(2, $detail->fresh()->quantity);
        $this->assertEquals($initial - 2, $product->fresh()->stock);
    }
}
