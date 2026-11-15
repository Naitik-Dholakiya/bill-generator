<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usermaster', function (Blueprint $table): void {
            $table->string('stock_negative_policy', 20)->default('warn_allow');
        });

        Schema::table('productmaster', function (Blueprint $table): void {
            $table->decimal('reorder_weight_level', 12, 3)->default(0);
        });

        Schema::table('inventorymaster', function (Blueprint $table): void {
            $table->decimal('weight_in_stock', 12, 3)->default(0);
        });

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->decimal('weight_delta', 12, 3)->nullable();
            $table->decimal('weight_after', 12, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropColumn(['weight_delta', 'weight_after']);
        });

        Schema::table('inventorymaster', function (Blueprint $table): void {
            $table->dropColumn('weight_in_stock');
        });

        Schema::table('productmaster', function (Blueprint $table): void {
            $table->dropColumn('reorder_weight_level');
        });

        Schema::table('usermaster', function (Blueprint $table): void {
            $table->dropColumn('stock_negative_policy');
        });
    }
};
