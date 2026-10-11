<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usermaster', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('is_admin', 1)->default('N');
            $table->string('status', 1)->default('1');
            $table->timestamps();
        });
    }

    public function test_admin_and_regular_accounts_use_the_same_login_and_are_routed_by_role(): void
    {
        $adminId = $this->createAccount('Administrator', 'admin@example.com', 'Y');
        $userId = $this->createAccount('Workspace User', 'user@example.com', 'N');

        $this->post(route('login'), [
            'tb_email' => 'admin@example.com',
            'tb_password' => 'Password123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('login'), [
            'tb_email' => 'user@example.com',
            'tb_password' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertGreaterThan(0, $adminId);
        $this->assertGreaterThan(0, $userId);
    }

    public function test_administrator_pages_reject_requests_without_a_logged_in_account(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect('/');
    }

    private function createAccount(string $name, string $email, string $role): int
    {
        return DB::table('usermaster')->insertGetId([
            'full_name' => $name,
            'email' => $email,
            'password' => securePassword('Password123!', $email),
            'is_admin' => $role,
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
