<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_products', function (Blueprint $table) {
            $table->boolean('is_raw_material')->default(false)->after('is_active');
            $table->string('unit', 20)->default('pcs')->after('is_raw_material');
        });
    }

    public function down(): void
    {
        Schema::table('app_products', function (Blueprint $table) {
            $table->dropColumn(['is_raw_material', 'unit']);
        });
    }
};
