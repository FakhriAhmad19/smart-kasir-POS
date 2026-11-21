<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $baristaCategory = DB::table('app_categories')->where('slug', 'minuman')->value('id');
        $kitchenCategory = DB::table('app_categories')->where('slug', 'makanan-snack')->value('id');

        if ($baristaCategory) {
            DB::table('app_products')->where('app_category_id', $baristaCategory)->update(['station' => 'barista']);
        }
        if ($kitchenCategory) {
            DB::table('app_products')->where('app_category_id', $kitchenCategory)->update(['station' => 'kitchen']);
        }

        // Existing ticket snapshots must follow the same station classification.
        DB::table('app_transaction_details')->whereIn('app_product_id', function ($query) use ($baristaCategory) {
            $query->select('id')->from('app_products')->where('app_category_id', $baristaCategory);
        })->update(['station' => 'barista']);
        DB::table('app_transaction_details')->whereIn('app_product_id', function ($query) use ($kitchenCategory) {
            $query->select('id')->from('app_products')->where('app_category_id', $kitchenCategory);
        })->update(['station' => 'kitchen']);
    }

    public function down(): void
    {
        // The previous default was barista; reverting is safer than guessing
        // historical manual station assignments.
    }
};
