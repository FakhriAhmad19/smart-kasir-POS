<?php

namespace Database\Seeders;

use App\Models\CafeTable;
use Illuminate\Database\Seeder;

class CafeTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 8) as $n) {
            CafeTable::firstOrCreate(['name' => "Meja {$n}"], ['area' => 'Indoor', 'capacity' => 4, 'sort_order' => $n]);
        }
        foreach (range(1, 4) as $n) {
            CafeTable::firstOrCreate(['name' => "Outdoor {$n}"], ['area' => 'Outdoor', 'capacity' => 2, 'sort_order' => 10 + $n]);
        }
    }
}
