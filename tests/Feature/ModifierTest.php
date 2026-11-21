<?php

namespace Tests\Feature;

use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\TransactionDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModifierTest extends TestCase
{
    use RefreshDatabase;

    private function kopi(): Product
    {
        return Product::where('sku', 'MNM-001')->firstOrFail();
    }

    private function option(string $name): ModifierOption
    {
        return ModifierOption::where('name', $name)->firstOrFail();
    }

    private function checkout(Product $product, array $optionIds, array $extra = [])
    {
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($kasir);

        return $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [array_merge([
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 2,
                'modifier_option_ids' => $optionIds,
                // A tampered client price must be ignored by the server.
                'price' => 1,
            ], $extra)],
            'payment_method' => 'cash',
            'discount_amount' => 0,
            'paid_amount' => 1000000,
        ]);
    }

    public function test_checkout_prices_modifiers_on_server_and_snapshots_them(): void
    {
        $this->seed();
        $kopi = $this->kopi();

        $response = $this->checkout($kopi, [
            $this->option('Large')->id,         // +5000
            $this->option('Hot')->id,
            $this->option('Less Sugar')->id,
            $this->option('Boba')->id,          // +4000
            $this->option('Extra Shot Espresso')->id, // +5000
        ], ['notes' => 'jangan terlalu panas']);

        $response->assertOk()->assertJson(['success' => true]);

        $unit = (float) $kopi->selling_price + 14000;
        $detail = TransactionDetail::latest('id')->first();

        $this->assertEquals($unit, (float) $detail->selling_price);
        $this->assertEquals(14000, (float) $detail->modifier_total);
        $this->assertEquals($unit * 2, (float) $detail->subtotal);
        $this->assertSame('jangan terlalu panas', $detail->notes);
        $this->assertSame(
            ['Large', 'Hot', 'Less Sugar', 'Extra Shot Espresso', 'Boba'],
            array_column($detail->modifiers, 'option')
        );
        $this->assertEquals($unit * 2, (float) $detail->transaction->total_amount);

        // Receipt shows the chosen modifiers and the note.
        $this->assertStringContainsString('+ Large', $response->json('receipt_html'));
        $this->assertStringContainsString('jangan terlalu panas', $response->json('receipt_html'));
    }

    public function test_required_groups_fall_back_to_defaults_when_omitted(): void
    {
        $this->seed();
        $kopi = $this->kopi();

        $this->checkout($kopi, [])->assertOk();

        $detail = TransactionDetail::latest('id')->first();
        $this->assertSame(['Regular', 'Ice', 'Normal Sugar'], array_column($detail->modifiers, 'option'));
        $this->assertEquals((float) $kopi->selling_price, (float) $detail->selling_price);
    }

    public function test_required_group_without_default_must_be_chosen(): void
    {
        $this->seed();
        ModifierGroup::where('name', 'Ukuran')->first()->options()->update(['is_default' => false]);

        $this->checkout($this->kopi(), [])
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_two_options_in_single_choice_group_are_rejected(): void
    {
        $this->seed();

        $this->checkout($this->kopi(), [$this->option('Regular')->id, $this->option('Large')->id])
            ->assertStatus(422);
    }

    public function test_exceeding_group_maximum_is_rejected(): void
    {
        $this->seed();
        ModifierGroup::where('name', 'Topping')->update(['max_select' => 1]);

        $this->checkout($this->kopi(), [$this->option('Boba')->id, $this->option('Whipped Cream')->id])
            ->assertStatus(422);
    }

    public function test_option_not_attached_to_product_is_rejected(): void
    {
        $this->seed();
        // Es Teh only has Ukuran + Level Gula; Boba belongs to Topping.
        $esTeh = Product::where('sku', 'MNM-002')->firstOrFail();
        $stockBefore = $esTeh->stock;

        $this->checkout($esTeh, [$this->option('Boba')->id])->assertStatus(422);
        $this->assertSame($stockBefore, $esTeh->fresh()->stock);
    }

    public function test_inactive_option_is_rejected(): void
    {
        $this->seed();
        $this->option('Boba')->update(['is_active' => false]);

        $this->checkout($this->kopi(), [$this->option('Boba')->id])->assertStatus(422);
    }

    public function test_admin_can_create_update_and_delete_group(): void
    {
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');
        $kopi = $this->kopi();

        $this->actingAs($admin)->post('/modifiers', [
            'name' => 'Jenis Susu',
            'selection_type' => 'single',
            'is_required' => 1,
            'options' => [
                ['name' => 'Fresh Milk', 'price' => 0, 'is_default' => 1, 'is_active' => 1],
                ['name' => 'Oat Milk', 'price' => 6000, 'is_active' => 1],
            ],
            'product_ids' => [$kopi->id],
        ])->assertRedirect(route('modifiers.index'));

        $group = ModifierGroup::where('name', 'Jenis Susu')->firstOrFail();
        $this->assertSame(1, $group->min_select);
        $this->assertSame(1, $group->max_select);
        $this->assertCount(2, $group->options);
        $this->assertTrue($kopi->modifierGroups()->whereKey($group->id)->exists());

        $fresh = $group->options->firstWhere('name', 'Fresh Milk');
        $this->actingAs($admin)->put("/modifiers/{$group->id}", [
            'name' => 'Jenis Susu',
            'selection_type' => 'single',
            'is_required' => 1,
            'options' => [
                ['id' => $fresh->id, 'name' => 'Fresh Milk', 'price' => 0, 'is_default' => 1, 'is_active' => 1],
                ['name' => 'Almond Milk', 'price' => 7000, 'is_active' => 1],
            ],
            'product_ids' => [],
        ])->assertRedirect(route('modifiers.index'));

        $this->assertSame(['Fresh Milk', 'Almond Milk'], $group->fresh()->options->pluck('name')->all());
        $this->assertFalse($kopi->modifierGroups()->whereKey($group->id)->exists());

        $this->actingAs($admin)->delete("/modifiers/{$group->id}")->assertRedirect(route('modifiers.index'));
        $this->assertDatabaseMissing('app_modifier_groups', ['id' => $group->id]);
        $this->assertDatabaseMissing('app_modifier_options', ['app_modifier_group_id' => $group->id]);
    }

    public function test_option_ids_from_another_group_cannot_be_hijacked(): void
    {
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');
        $ukuran = ModifierGroup::where('name', 'Ukuran')->first();
        $boba = $this->option('Boba');

        $this->actingAs($admin)->put("/modifiers/{$ukuran->id}", [
            'name' => 'Ukuran',
            'selection_type' => 'single',
            'is_required' => 1,
            'options' => [['id' => $boba->id, 'name' => 'Hijacked', 'price' => 0, 'is_default' => 1, 'is_active' => 1]],
        ])->assertSessionHasErrors('options');

        $this->assertSame('Boba', $boba->fresh()->name);
    }

    public function test_cashier_cannot_manage_modifiers(): void
    {
        $this->seed();
        $kasir = $this->demoUser('kasir@smartkasir.com');

        $this->actingAs($kasir)->get('/modifiers')->assertForbidden();
        $this->actingAs($kasir)->post('/modifiers', [])->assertForbidden();
    }

    public function test_pages_render(): void
    {
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');
        $group = ModifierGroup::first();

        $this->actingAs($admin)->get('/modifiers')->assertOk()->assertSee('Topping');
        $this->actingAs($admin)->get('/modifiers/create')->assertOk();
        $this->actingAs($admin)->get("/modifiers/{$group->id}/edit")->assertOk();
        $this->actingAs($admin)->get("/products/{$this->kopi()->id}/edit")->assertOk()->assertSee('modifier_group_ids[]', false);

        $kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($kasir);
        $this->actingAs($kasir)->get('/pos')->assertOk()->assertSee('modifierModal', false);
    }
}
