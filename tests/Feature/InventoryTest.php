<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
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

    public function test_raw_material_can_be_created_and_restocked_without_appearing_in_pos(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');
        $category = Category::first();

        $this->actingAs($owner)->post('/raw-materials', [
            'app_category_id' => $category->id,
            'name' => 'Susu Oat',
            'unit' => 'ml',
            'purchase_price' => 25,
            'stock' => 1000,
            'min_stock' => 200,
            'is_active' => 1,
        ])->assertRedirect('/raw-materials');

        $material = Product::where('name', 'Susu Oat')->firstOrFail();
        $this->assertTrue($material->is_raw_material);
        $this->assertSame('ml', $material->unit);
        $this->assertEquals(0, (float) $material->selling_price);

        $this->actingAs($owner)->post('/stock-ins', [
            'app_product_id' => $material->id,
            'quantity' => 500,
            'purchase_price' => 24,
        ])->assertRedirect('/stock-ins');

        $this->assertEquals(1500, $material->fresh()->stock);
        $this->assertNotContains($material->id, Product::where('is_raw_material', false)->pluck('id')->all());
    }
}
