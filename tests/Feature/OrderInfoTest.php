<?php

namespace Tests\Feature;

use App\Models\CafeTable;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderInfoTest extends TestCase
{
    use RefreshDatabase;

    private function checkout(array $orderInfo)
    {
        $product = Product::where('sku', 'MNM-003')->firstOrFail(); // no modifiers

        return $this->postJson('/pos/checkout', array_merge([
            'items' => [['id' => $product->id, 'name' => $product->name, 'quantity' => 1]],
            'payment_method' => 'cash',
            'discount_amount' => 0,
            'paid_amount' => 100000,
        ], $orderInfo));
    }

    private function actingAsCashier(): void
    {
        $kasir = $this->demoUser('kasir@smartkasir.com');
        $this->openShiftFor($kasir);
        $this->actingAs($kasir);
    }

    public function test_dine_in_order_stores_table_snapshot_and_customer(): void
    {
        $this->seed();
        $this->actingAsCashier();
        $table = CafeTable::where('name', 'Meja 3')->firstOrFail();

        $response = $this->checkout([
            'order_type' => 'dine_in',
            'table_id' => $table->id,
            'customer_name' => '  Budi ',
        ])->assertOk();

        $trx = Transaction::latest('id')->first();
        $this->assertSame('dine_in', $trx->order_type);
        $this->assertSame($table->id, $trx->app_table_id);
        $this->assertSame('Meja 3', $trx->table_name);
        $this->assertSame('Budi', $trx->customer_name);
        $this->assertSame(1, $trx->queue_number);

        $html = $response->json('receipt_html');
        $this->assertStringContainsString('Meja 3', $html);
        $this->assertStringContainsString('Budi', $html);
        $this->assertStringContainsString('NO. ANTREAN', $html);

        // Renaming the table later does not rewrite history.
        $table->update(['name' => 'VIP 1']);
        $this->assertSame('Meja 3', $trx->fresh()->table_name);
    }

    public function test_dine_in_requires_an_active_table(): void
    {
        $this->seed();
        $this->actingAsCashier();

        $this->checkout(['order_type' => 'dine_in'])
            ->assertStatus(422)->assertJsonValidationErrors('table_id');

        $inactive = CafeTable::first();
        $inactive->update(['is_active' => false]);
        $this->checkout(['order_type' => 'dine_in', 'table_id' => $inactive->id])
            ->assertStatus(422)->assertJsonValidationErrors('table_id');

        $this->assertSame(0, Transaction::count());
    }

    public function test_online_requires_known_platform_and_ignores_table(): void
    {
        $this->seed();
        $this->actingAsCashier();

        $this->checkout(['order_type' => 'online'])
            ->assertStatus(422)->assertJsonValidationErrors('online_platform');
        $this->checkout(['order_type' => 'online', 'online_platform' => 'Hacker'])
            ->assertStatus(422)->assertJsonValidationErrors('online_platform');

        $this->checkout([
            'order_type' => 'online',
            'online_platform' => 'GoFood',
            'table_id' => CafeTable::first()->id,
        ])->assertOk();

        $trx = Transaction::latest('id')->first();
        $this->assertSame('GoFood', $trx->online_platform);
        $this->assertNull($trx->app_table_id);
        $this->assertNull($trx->table_name);
    }

    public function test_missing_order_type_defaults_to_take_away(): void
    {
        $this->seed();
        $this->actingAsCashier();

        $this->checkout([])->assertOk();
        $this->assertSame('take_away', Transaction::latest('id')->first()->order_type);
    }

    public function test_queue_number_increments_and_resets_daily(): void
    {
        $this->seed();
        $this->actingAsCashier();

        $this->checkout(['order_type' => 'take_away'])->assertOk();
        $this->checkout(['order_type' => 'take_away'])->assertOk();
        $this->assertSame([1, 2], Transaction::orderBy('id')->pluck('queue_number')->all());

        Carbon::setTestNow(now()->addDay());
        $this->checkout(['order_type' => 'take_away'])->assertOk();
        $this->assertSame(1, Transaction::latest('id')->first()->queue_number);
        Carbon::setTestNow();
    }

    public function test_admin_can_manage_tables(): void
    {
        $this->seed();
        $admin = $this->demoUser('admin@smartkasir.com');
        $this->actingAs($admin);

        $this->get('/tables')->assertOk()->assertSee('Outdoor 1');

        $this->post('/tables', ['name' => 'Sofa 1', 'area' => 'Lounge', 'capacity' => 6, 'is_active' => 1])
            ->assertRedirect(route('tables.index'));
        $sofa = CafeTable::where('name', 'Sofa 1')->firstOrFail();

        $this->post('/tables', ['name' => 'Sofa 1'])->assertSessionHasErrors('name');

        $this->put("/tables/{$sofa->id}", ['name' => 'Sofa A', 'area' => 'Lounge'])
            ->assertRedirect(route('tables.index'));
        $this->assertSame('Sofa A', $sofa->fresh()->name);
        $this->assertFalse($sofa->fresh()->is_active); // unchecked switch = inactive

        $this->post('/tables/bulk', ['prefix' => 'Bar', 'from' => 1, 'to' => 3, 'area' => 'Bar'])
            ->assertRedirect(route('tables.index'));
        $this->assertSame(3, CafeTable::where('area', 'Bar')->count());

        $this->delete("/tables/{$sofa->id}")->assertRedirect(route('tables.index'));
        $this->assertDatabaseMissing('app_tables', ['id' => $sofa->id]);
    }

    public function test_table_with_history_is_deactivated_not_deleted(): void
    {
        $this->seed();
        $this->actingAsCashier();
        $table = CafeTable::first();
        $this->checkout(['order_type' => 'dine_in', 'table_id' => $table->id])->assertOk();

        $this->actingAs($this->demoUser('admin@smartkasir.com'))
            ->delete("/tables/{$table->id}")->assertRedirect();

        $this->assertDatabaseHas('app_tables', ['id' => $table->id, 'is_active' => false]);
    }

    public function test_cashier_cannot_manage_tables(): void
    {
        $this->seed();
        $this->actingAs($this->demoUser('kasir@smartkasir.com'));

        $this->get('/tables')->assertForbidden();
        $this->post('/tables', ['name' => 'X'])->assertForbidden();
    }

    public function test_csv_export_neutralises_formulas_in_customer_name(): void
    {
        $this->seed();
        $this->actingAsCashier();
        $this->checkout(['order_type' => 'take_away', 'customer_name' => '=HYPERLINK("http://evil")'])->assertOk();

        $csv = $this->actingAs($this->demoUser('owner@smartkasir.com'))
            ->get('/reports/export-csv')->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString('Take Away', $csv);
    }
}
