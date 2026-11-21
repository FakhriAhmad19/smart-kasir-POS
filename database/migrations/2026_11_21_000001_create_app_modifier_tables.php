<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A modifier group is a question asked when a menu item is ordered,
        // e.g. "Ukuran" (pick exactly one) or "Topping" (pick any).
        // Variants such as size / hot-iced are modelled as required
        // single-choice groups, so there is one concept instead of two.
        Schema::create('app_modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_required')->default(false);
            $table->unsignedTinyInteger('min_select')->default(0);
            $table->unsignedTinyInteger('max_select')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('app_modifier_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_modifier_group_id')->constrained('app_modifier_groups')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['app_modifier_group_id', 'sort_order']);
        });

        Schema::create('app_product_modifier_group', function (Blueprint $table) {
            $table->foreignId('app_product_id')->constrained('app_products')->cascadeOnDelete();
            $table->foreignId('app_modifier_group_id')->constrained('app_modifier_groups')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['app_product_id', 'app_modifier_group_id']);
            $table->index('app_modifier_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_product_modifier_group');
        Schema::dropIfExists('app_modifier_options');
        Schema::dropIfExists('app_modifier_groups');
    }
};
