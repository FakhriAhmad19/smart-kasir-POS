<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_stock_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_product_id')->constrained('app_products')->onDelete('cascade');
            $table->foreignId('app_user_id')->constrained('app_users')->onDelete('cascade');
            $table->string('supplier_name')->nullable();
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_stock_ins');
    }
};
