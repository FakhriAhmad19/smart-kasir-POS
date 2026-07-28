<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_product_id')->constrained('app_products')->onDelete('cascade');
            $table->foreignId('app_user_id')->constrained('app_users')->onDelete('cascade');
            $table->enum('type', ['opname', 'damaged', 'expired', 'lost'])->default('opname');
            $table->integer('quantity_before');
            $table->integer('quantity_adjusted'); // e.g. -2 for damaged, or new total for opname
            $table->integer('quantity_after');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_stock_adjustments');
    }
};
