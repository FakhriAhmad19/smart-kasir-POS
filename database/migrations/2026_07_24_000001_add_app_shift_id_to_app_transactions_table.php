<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_transactions', function (Blueprint $table) {
            $table->foreignId('app_shift_id')
                ->nullable()
                ->after('app_user_id')
                ->constrained('app_shifts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('app_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('app_shift_id');
        });
    }
};
