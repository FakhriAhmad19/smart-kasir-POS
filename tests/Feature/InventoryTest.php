<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_increments_product_stock(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $product = Product::first();
        $initialStock = $product->stock;

        $this->actingAs($owner)->post('/stock-ins', [
            'app_product_id' => $product->id,
            'supplier_name' => 'PT Sumber Rezeki',
            'quantity' => 10,
            'purchase_price' => 5000,
            'update_hpp' => 1,
        ])->assertRedirect('/stock-ins');

        $this->assertEquals($initialStock + 10, $product->fresh()->stock);
        $this->assertEquals(5000, $product->fresh()->purchase_price);
    }

    public function test_stock_opname_adjustment_updates_stock(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $product = Product::first();

        $this->actingAs($owner)->post('/adjustments', [
            'app_product_id' => $product->id,
            'type' => 'opname',
            'quantity_actual' => 50,
            'notes' => 'Hasil opname bulanan',
        ])->assertRedirect('/adjustments');

        $this->assertEquals(50, $product->fresh()->stock);
    }
}
