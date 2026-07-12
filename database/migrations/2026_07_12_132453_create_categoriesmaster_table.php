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
        Schema::create('categorymaster', function (Blueprint $table) {
            $table->id('category_id');

            $table->string('category_name', 100);
            $table->text('description')->nullable();

            $table->enum('status', ['0', '1'])
                  ->default('1')
                  ->comment('0: Inactive, 1: Active');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['created_by', 'category_name'], 'categorymaster_createdby_categoryname_unique');

            // Foreign Keys
            $table->foreign('created_by')
                  ->references('user_id')
                  ->on('usermaster');

            $table->foreign('updated_by')
                  ->references('user_id')
                  ->on('usermaster');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorymaster');
    }
};