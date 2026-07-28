<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_renders_successfully(): void
    {
        $this->seed();
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($kasir);
        $this->actingAs($kasir)->get('/pos')->assertStatus(200);
    }

    public function test_stocks_endpoint_returns_live_stock_for_active_products(): void
    {
        $this->seed();
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $product = Product::where('is_active', true)->first();

        $response = $this->actingAs($kasir)->getJson('/pos/stocks');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'stocks' => [['id', 'stock', 'min_stock']]]);

        $this->assertContains(
            $product->id,
            collect($response->json('stocks'))->pluck('id')->all()
        );
    }

    public function test_pos_checkout_deducts_stock_atomically(): void
    {
        $this->seed();

        $kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($kasir);
        $product = Product::first();

        $initialStock = $product->stock;

        $response = $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 2,
                    'price' => $product->selling_price,
                    'stock' => $initialStock,
                ]
            ],
            'payment_method' => 'cash',
            'discount_amount' => 0,
            'paid_amount' => $product->selling_price * 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals($initialStock - 2, $product->fresh()->stock);
    }
}
