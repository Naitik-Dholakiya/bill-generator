<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usermaster', function (Blueprint $table) {
            $table->boolean('is_jewelry')->default(false);
        });

        Schema::table('productmaster', function (Blueprint $table) {
            $table->decimal('touch', 6, 3)->nullable();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('touch', 6, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('touch');
        });

        Schema::table('productmaster', function (Blueprint $table) {
            $table->dropColumn('touch');
        });

        Schema::table('usermaster', function (Blueprint $table) {
            $table->dropColumn('is_jewelry');
        });
    }
};
