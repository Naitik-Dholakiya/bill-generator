<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchasemaster', function (Blueprint $table) {
            $table->string('voucher_section', 50)->nullable();
            $table->decimal('less_kasar', 10, 3)->nullable();
            $table->decimal('rate_cut_weight', 10, 3)->nullable();
            $table->decimal('other_convert_fine', 10, 3)->nullable();
            $table->decimal('opening_gold', 10, 3)->nullable();
            $table->decimal('closing_gold', 10, 3)->nullable();
            $table->text('footer_note')->nullable();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->unsignedInteger('pcs')->nullable();
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->decimal('touch', 6, 3)->nullable();
            $table->decimal('wastage', 6, 3)->nullable();
            $table->decimal('fine_weight', 10, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn([
                'pcs',
                'gross_weight',
                'net_weight',
                'touch',
                'wastage',
                'fine_weight',
            ]);
        });

        Schema::table('purchasemaster', function (Blueprint $table) {
            $table->dropColumn([
                'voucher_section',
                'less_kasar',
                'rate_cut_weight',
                'other_convert_fine',
                'opening_gold',
                'closing_gold',
                'footer_note',
            ]);
        });
    }
};
