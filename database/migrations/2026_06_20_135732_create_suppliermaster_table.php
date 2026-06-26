<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliermaster', function (Blueprint $table) {

            $table->bigIncrements('supplier_id');

            $table->string('supplier_code', 20)->unique();
            $table->string('supplier_name', 100);

            $table->string('contact_person', 100)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('phone', 20)->nullable();

            $table->string('gst_number', 15)->nullable()->unique();

            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();

            $table->enum('status', ['0', '1'])
                ->default('1')
                ->comment('0: Inactive, 1: Active');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys (if using usermaster table)
            $table->foreign('created_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();

            $table->foreign('updated_by')
                ->references('user_id')
                ->on('usermaster')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliermaster');
    }
};