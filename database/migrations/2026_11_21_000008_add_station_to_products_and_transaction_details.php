<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_products', function (Blueprint $table) {
            $table->string('station', 20)->default('barista')->after('unit');
        });

        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->string('station', 20)->default('barista')->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->dropColumn('station');
        });
        Schema::table('app_products', function (Blueprint $table) {
            $table->dropColumn('station');
        });
    }
};
