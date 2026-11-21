<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_transactions', function (Blueprint $table) {
            // open = bill running (pay later), paid = settled, cancelled = voided bill.
            $table->string('status', 20)->default('paid')->after('invoice_number');
            // Revenue is recognised when paid, not when the bill was opened.
            $table->timestamp('paid_at')->nullable()->after('status');
            // app_user_id is the cashier who took the money (drives shift
            // reconciliation); opened_by is who started the bill.
            $table->foreignId('opened_by')->nullable()->after('app_user_id')
                ->constrained('app_users')->nullOnDelete();
            // Set when part of a bill was paid separately (split bill).
            $table->foreignId('split_from_id')->nullable()->after('opened_by')
                ->constrained('app_transactions')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('app_users')->nullOnDelete();
            $table->string('cancel_reason', 255)->nullable();

            $table->index(['status', 'paid_at']);
            $table->index(['status', 'app_table_id']);
        });

        // Open bills have no payment method yet.
        Schema::table('app_transactions', function (Blueprint $table) {
            $table->string('payment_method', 20)->nullable()->default(null)->change();
        });

        // Every existing transaction was paid at checkout time.
        DB::table('app_transactions')->update([
            'paid_at' => DB::raw('created_at'),
            'opened_by' => DB::raw('app_user_id'),
        ]);
    }

    public function down(): void
    {
        DB::table('app_transactions')->where('status', '!=', 'paid')->delete();

        Schema::table('app_transactions', function (Blueprint $table) {
            $table->dropIndex(['status', 'paid_at']);
            $table->dropIndex(['status', 'app_table_id']);
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('split_from_id');
            $table->dropConstrainedForeignId('opened_by');
            $table->dropColumn(['status', 'paid_at', 'cancelled_at', 'cancel_reason']);
        });

        Schema::table('app_transactions', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'qris'])->default('cash')->change();
        });
    }
};
