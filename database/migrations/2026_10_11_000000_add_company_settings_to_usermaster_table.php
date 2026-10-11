<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usermaster', function (Blueprint $table) {
            $table->string('invoice_template', 50)->default('pdf');
            $table->string('company_name', 150)->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_gstin', 15)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('usermaster', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_template',
                'company_name',
                'company_address',
                'company_gstin',
            ]);
        });
    }
};
