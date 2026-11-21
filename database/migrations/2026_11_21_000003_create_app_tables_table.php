<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cafe tables (dine-in orders are delivered to a table).
        Schema::create('app_tables', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('area', 50)->nullable();
            $table->unsignedTinyInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_tables');
    }
};
