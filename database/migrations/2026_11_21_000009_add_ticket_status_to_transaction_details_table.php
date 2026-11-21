<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->string('ticket_status', 20)->default('pending')->after('station');
        });
    }

    public function down(): void
    {
        Schema::table('app_transaction_details', function (Blueprint $table) {
            $table->dropColumn('ticket_status');
        });
    }
};
