<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // selling_price keeps meaning "unit price actually charged" (base +
        // modifiers), so existing profit reports stay correct. The chosen
        // modifiers are snapshotted as JSON so receipts survive later edits
        // or deletion of the modifier master data.
        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->json('modifiers')->nullable()->after('selling_price');
            $table->decimal('modifier_total', 15, 2)->default(0)->after('modifiers');
            $table->string('notes', 255)->nullable()->after('modifier_total');
        });
    }

    public function down(): void
    {
        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->dropColumn(['modifiers', 'modifier_total', 'notes']);
        });
    }
};
