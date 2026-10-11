<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productmaster', function (Blueprint $table) {
            $table->decimal('default_wastage', 6, 3)->nullable();
        });

        Schema::table('invoicemaster', function (Blueprint $table) {
            $table->string('voucher_section', 50)->default('SALES');
            $table->decimal('less_kasar', 10, 3)->nullable();
            $table->decimal('rate_cut_weight', 10, 3)->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->decimal('rate_cut_amount', 12, 2)->nullable();
            $table->decimal('other_convert_fine', 10, 3)->nullable();
            $table->decimal('opening_balance', 12, 2)->nullable();
            $table->decimal('opening_gold', 10, 3)->nullable();
            $table->decimal('closing_balance', 12, 2)->nullable();
            $table->decimal('closing_gold', 10, 3)->nullable();
            $table->string('closing_gold_type', 2)->default('Dr');
            $table->text('footer_note')->nullable();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->unsignedInteger('pcs')->nullable();
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->decimal('wastage', 6, 3)->nullable();
            $table->decimal('fine_weight', 10, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'pcs',
                'gross_weight',
                'net_weight',
                'wastage',
                'fine_weight',
            ]);
        });

        Schema::table('invoicemaster', function (Blueprint $table) {
            $table->dropColumn([
                'voucher_section',
                'less_kasar',
                'rate_cut_weight',
                'rate',
                'rate_cut_amount',
                'other_convert_fine',
                'opening_balance',
                'opening_gold',
                'closing_balance',
                'closing_gold',
                'closing_gold_type',
                'footer_note',
            ]);
        });

        Schema::table('productmaster', function (Blueprint $table) {
            $table->dropColumn('default_wastage');
        });
    }
};
