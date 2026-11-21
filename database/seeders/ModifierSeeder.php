<?php

namespace Database\Seeders;

use App\Models\ModifierGroup;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ModifierSeeder extends Seeder
{
    /**
     * Demo cafe modifiers attached to the coffee / tea menu.
     */
    public function run(): void
    {
        $groups = [
            [
                'group' => ['name' => 'Ukuran', 'is_required' => true, 'min_select' => 1, 'max_select' => 1, 'sort_order' => 1],
                'options' => [
                    ['name' => 'Regular', 'price' => 0, 'is_default' => true],
                    ['name' => 'Large', 'price' => 5000],
                ],
            ],
            [
                'group' => ['name' => 'Penyajian', 'is_required' => true, 'min_select' => 1, 'max_select' => 1, 'sort_order' => 2],
                'options' => [
                    ['name' => 'Ice', 'price' => 0, 'is_default' => true],
                    ['name' => 'Hot', 'price' => 0],
                ],
            ],
            [
                'group' => ['name' => 'Level Gula', 'is_required' => true, 'min_select' => 1, 'max_select' => 1, 'sort_order' => 3],
                'options' => [
                    ['name' => 'Normal Sugar', 'price' => 0, 'is_default' => true],
                    ['name' => 'Less Sugar', 'price' => 0],
                    ['name' => 'No Sugar', 'price' => 0],
                ],
            ],
            [
                'group' => ['name' => 'Topping', 'is_required' => false, 'min_select' => 0, 'max_select' => 3, 'sort_order' => 4],
                'options' => [
                    ['name' => 'Extra Shot Espresso', 'price' => 5000],
                    ['name' => 'Boba', 'price' => 4000],
                    ['name' => 'Whipped Cream', 'price' => 3000],
                ],
            ],
        ];

        $groupIds = [];
        foreach ($groups as $def) {
            $group = ModifierGroup::updateOrCreate(['name' => $def['group']['name']], $def['group']);
            foreach ($def['options'] as $i => $opt) {
                $group->options()->updateOrCreate(
                    ['name' => $opt['name']],
                    $opt + ['sort_order' => $i, 'is_default' => false, 'is_active' => true]
                );
            }
            $groupIds[$def['group']['name']] = $group->id;
        }

        $attach = fn (string $sku, array $names) => Product::where('sku', $sku)->first()?->modifierGroups()->syncWithoutDetaching(
            collect($names)->mapWithKeys(fn ($n, $i) => [$groupIds[$n] => ['sort_order' => $i]])->all()
        );

        // Coffee gets the full set; iced tea only size and sugar level.
        $attach('MNM-001', ['Ukuran', 'Penyajian', 'Level Gula', 'Topping']);
        $attach('MNM-002', ['Ukuran', 'Level Gula']);
    }
}
