<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Minuman',
                'slug' => 'minuman',
                'description' => 'Berbagai jenis minuman dingin dan hangat',
                'products' => [
                    [
                        'sku' => 'MNM-001',
                        'barcode' => '899100100101',
                        'name' => 'Kopi Susu Gula Aren 250ml',
                        'purchase_price' => 10000,
                        'selling_price' => 18000,
                        'stock' => 35,
                        'min_stock' => 10,
                    ],
                    [
                        'sku' => 'MNM-002',
                        'barcode' => '899100100102',
                        'name' => 'Es Teh Manis Jumbo',
                        'purchase_price' => 2000,
                        'selling_price' => 6000,
                        'stock' => 50,
                        'min_stock' => 15,
                    ],
                    [
                        'sku' => 'MNM-003',
                        'barcode' => '899100100103',
                        'name' => 'Air Mineral 600ml',
                        'purchase_price' => 2500,
                        'selling_price' => 4000,
                        'stock' => 4, // Low stock demo
                        'min_stock' => 10,
                    ],
                ]
            ],
            [
                'name' => 'Makanan & Snack',
                'slug' => 'makanan-snack',
                'description' => 'Makanan ringan dan camilan gurih',
                'products' => [
                    [
                        'sku' => 'MKN-001',
                        'barcode' => '899200200201',
                        'name' => 'Nasi Goreng Spesial Kasir',
                        'purchase_price' => 14000,
                        'selling_price' => 22000,
                        'stock' => 20,
                        'min_stock' => 5,
                    ],
                    [
                        'sku' => 'MKN-002',
                        'barcode' => '899200200202',
                        'name' => 'Keripik Kentang Balado 100g',
                        'purchase_price' => 7000,
                        'selling_price' => 12500,
                        'stock' => 25,
                        'min_stock' => 8,
                    ],
                    [
                        'sku' => 'MKN-003',
                        'barcode' => '899200200203',
                        'name' => 'Roti Bakar Cokelat Keju',
                        'purchase_price' => 9000,
                        'selling_price' => 15000,
                        'stock' => 12,
                        'min_stock' => 5,
                    ]
                ]
            ],
            [
                'name' => 'Sembako & Kebutuhan',
                'slug' => 'sembako',
                'description' => 'Kebutuhan harian rumah tangga',
                'products' => [
                    [
                        'sku' => 'SBK-001',
                        'barcode' => '899300300301',
                        'name' => 'Minyak Goreng Pouch 1L',
                        'purchase_price' => 14000,
                        'selling_price' => 17500,
                        'stock' => 18,
                        'min_stock' => 5,
                    ],
                    [
                        'sku' => 'SBK-002',
                        'barcode' => '899300300302',
                        'name' => 'Gula Pasir 1kg',
                        'purchase_price' => 13500,
                        'selling_price' => 16000,
                        'stock' => 3, // Low stock demo
                        'min_stock' => 10,
                    ]
                ]
            ]
        ];

        foreach ($categories as $catData) {
            $products = $catData['products'];
            unset($catData['products']);

            $category = Category::firstOrCreate(
                ['slug' => $catData['slug']],
                $catData
            );

            foreach ($products as $prodData) {
                Product::firstOrCreate(
                    ['sku' => $prodData['sku']],
                    array_merge($prodData, [
                        'app_category_id' => $category->id,
                        'is_active' => true,
                        'station' => $category->slug === 'minuman' ? 'barista' : ($category->slug === 'makanan-snack' ? 'kitchen' : 'barista'),
                    ])
                );
            }
        }
    }
}
