<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'store_name' => 'SMART KASIR UMKM',
            'store_address' => 'Jl. Raya Utama No. 88, Indonesia',
            'store_phone' => '0812-3456-7890',
            'store_email' => 'info@smartkasir.com',
            'receipt_footer_note' => '*** TERIMA KASIH ***' . PHP_EOL . 'Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.',
            'tax_rate' => '0',
            'service_charge_rate' => '0',
            'prices_include_tax' => '0',
            'supervisor_pin_hash' => null,
        ];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
