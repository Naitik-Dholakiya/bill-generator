<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customermaster', function (Blueprint $table) {

            $table->id('customer_id');

            $table->string('customer_code', 20)->unique();

            $table->string('customer_name', 100);

            $table->string('email', 100)->nullable()->unique();

            $table->string('phone', 20)->nullable();

            $table->string('gst_number', 15)->nullable()->unique();

            $table->text('billing_address')->nullable();

            $table->text('shipping_address')->nullable();

            $table->enum('status', ['1', '0'])
                ->default('1');

            $table->unsignedBigInteger('created_by')->nullable();

            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->softDeletes();

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

    public function down(): void
    {
        Schema::dropIfExists('customermaster');
    }
};