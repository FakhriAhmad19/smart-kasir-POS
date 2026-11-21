<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_transactions', function (Blueprint $table) {
            $table->decimal('refunded_amount', 15, 2)->default(0)->after('change_amount');
            $table->timestamp('refunded_at')->nullable()->after('refunded_amount');
            $table->foreignId('refunded_by')->nullable()->after('refunded_at')->constrained('app_users')->nullOnDelete();
        });

        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->integer('voided_quantity')->default(0)->after('quantity');
            $table->timestamp('voided_at')->nullable()->after('voided_quantity');
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('app_users')->nullOnDelete();
            $table->string('void_reason', 255)->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_quantity', 'voided_at', 'void_reason']);
        });
        Schema::table('app_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropColumn(['refunded_amount', 'refunded_at']);
        });
    }
};
