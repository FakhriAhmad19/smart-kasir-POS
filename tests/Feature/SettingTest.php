<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_settings_update(): void
    {
        $this->seed();
        $owner = $this->demoUser('owner@smartkasir.com');

        $this->actingAs($owner)->post('/settings', [
            'store_name' => 'TOKO SERBA ADA',
            'store_address' => 'Jl. Pemuda No. 123',
            'store_phone' => '0811-2233-4455',
            'store_email' => 'toko@example.com',
            'receipt_footer_note' => 'Terima kasih telah berbelanja!',
            'tax_rate' => 10,
            'service_charge_rate' => 5,
            'prices_include_tax' => 1,
        ])->assertRedirect('/settings');

        $this->assertEquals('TOKO SERBA ADA', Setting::get('store_name'));
        $this->assertEquals('10', Setting::get('tax_rate'));
        $this->assertEquals('1', Setting::get('prices_include_tax'));
    }
}
