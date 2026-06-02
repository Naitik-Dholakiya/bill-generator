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
        Schema::create('usermaster', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('full_name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('phone', 15)->nullable();
            $table->enum('is_admin',['N', 'Y'])
                  ->default('N')
                  ->comment('N: No, Y: Yes');
            $table->enum('status', ['0', '1', '2'])
                  ->default('1')
                  ->comment('0: Inactive, 1: Active, 2: Blocked');
            // $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usermaster');
    }
};