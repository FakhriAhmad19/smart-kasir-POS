<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TransactionDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_is_visible_only_on_its_station_and_status_can_change(): void
    {
        $this->seed();
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $owner = $this->demoUser('owner@smartkasir.com');
        $this->openShiftFor($kasir);
        $product = Product::first();
        $product->update(['station' => 'kitchen']);

        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [['id' => $product->id, 'quantity' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => $product->selling_price,
        ])->assertOk();

        $detail = TransactionDetail::latest('id')->firstOrFail();
        $this->actingAs($owner)->get('/tickets/kitchen')->assertOk()->assertSee($product->name);
        $this->actingAs($owner)->get('/tickets/barista')->assertOk()->assertDontSee($product->name);

        $this->actingAs($owner)->patch("/tickets/kitchen/{$detail->id}/status", [
            'ticket_status' => 'preparing',
        ])->assertRedirect();

        $this->assertSame('preparing', $detail->fresh()->ticket_status);
    }
}
