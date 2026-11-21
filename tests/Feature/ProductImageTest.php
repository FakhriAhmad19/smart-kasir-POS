<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'app_category_id' => Category::first()->id,
            'name' => 'Kopi Susu Gula Aren',
            'purchase_price' => 8000,
            'selling_price' => 15000,
            'stock' => 20,
            'min_stock' => 5,
            'is_active' => 1,
        ], $overrides);
    }

    public function test_product_can_be_created_with_image_and_shows_in_pos(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');

        $this->actingAs($admin)->post('/products', $this->productPayload([
            'image' => UploadedFile::fake()->image('kopi.jpg', 400, 400),
        ]))->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Kopi Susu Gula Aren')->firstOrFail();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
        $this->assertSame(asset('storage/' . $product->image), $product->image_url);

        $kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($kasir);
        $this->actingAs($kasir)->get('/pos')
            ->assertOk()
            ->assertSee($product->image_url, false);

        $this->actingAs($kasir)->getJson('/pos/search?keyword=Kopi Susu')
            ->assertOk()
            ->assertJsonFragment(['image_url' => $product->image_url]);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');

        $this->actingAs($admin)->post('/products', $this->productPayload([
            'image' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('image');
    }

    public function test_image_can_be_replaced_and_removed(): void
    {
        Storage::fake('public');
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');
        $product = Product::first();

        $base = [
            'app_category_id' => $product->app_category_id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'purchase_price' => $product->purchase_price,
            'selling_price' => $product->selling_price,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'is_active' => 1,
        ];

        $this->actingAs($admin)->put("/products/{$product->id}", $base + [
            'image' => UploadedFile::fake()->image('a.png'),
        ]);
        $first = $product->fresh()->image;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($admin)->put("/products/{$product->id}", $base + [
            'image' => UploadedFile::fake()->image('b.png'),
        ]);
        $second = $product->fresh()->image;
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->actingAs($admin)->put("/products/{$product->id}", $base + ['remove_image' => 1]);
        $this->assertNull($product->fresh()->image);
        $this->assertNull($product->fresh()->image_url);
        Storage::disk('public')->assertMissing($second);
    }
}
