<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_transaction_id')->constrained('app_transactions')->cascadeOnDelete();
            $table->foreignId('app_product_id')->constrained('app_products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_transaction_details');
    }
};
