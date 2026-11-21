<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_transactions', function (Blueprint $table) {
            // Existing (retail) rows become take-away, which matches how they were sold.
            $table->string('order_type', 20)->default('take_away')->after('app_shift_id');
            $table->string('online_platform', 30)->nullable()->after('order_type');
            $table->foreignId('app_table_id')->nullable()->after('online_platform')
                ->constrained('app_tables')->nullOnDelete();
            // Snapshot so receipts stay correct if the table is renamed/deleted.
            $table->string('table_name', 50)->nullable()->after('app_table_id');
            $table->string('customer_name', 100)->nullable()->after('table_name');
            // Daily queue number shown to the customer / barista.
            $table->unsignedSmallInteger('queue_number')->nullable()->after('customer_name');

            $table->index(['order_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('app_transactions', function (Blueprint $table) {
            $table->dropIndex(['order_type', 'created_at']);
            $table->dropConstrainedForeignId('app_table_id');
            $table->dropColumn(['order_type', 'online_platform', 'table_name', 'customer_name', 'queue_number']);
        });
    }
};
