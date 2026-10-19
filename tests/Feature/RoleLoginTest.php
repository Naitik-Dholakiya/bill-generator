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

    public function test_login_remember_checkbox_controls_authentication_and_display_cookie_lifetimes(): void
    {
        $userId = $this->createAccount('Workspace User', 'user@example.com', 'N');

        $sessionLogin = $this->post(route('login'), [
            'tb_email' => 'user@example.com',
            'tb_password' => 'Password123!',
        ])->assertRedirect(route('dashboard'));

        foreach (['GTA', 'CSGO'] as $cookieName) {
            $this->assertSame(0, $sessionLogin->getCookie($cookieName)->getExpiresTime());
        }

        $rememberedLogin = $this->post(route('login'), [
            'tb_email' => 'user@example.com',
            'tb_password' => 'Password123!',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'));

        foreach (['GTA', 'CSGO'] as $cookieName) {
            $this->assertGreaterThan(
                now()->addDays(29)->getTimestamp(),
                $rememberedLogin->getCookie($cookieName)->getExpiresTime(),
            );
        }

        $this->assertGreaterThan(0, $userId);
    }

    public function test_login_returns_to_the_requested_internal_page(): void
    {
        $this->createAccount('Workspace User', 'user@example.com', 'N');

        $this->get(route('customers.index'))
            ->assertRedirect(route('login.form'));

        $this->post(route('login'), [
            'tb_email' => 'user@example.com',
            'tb_password' => 'Password123!',
        ])->assertRedirect(route('customers.index'));
    }

    public function test_administrator_pages_reject_requests_without_a_logged_in_account(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect('/');
    }

    public function test_admin_setup_endpoint_rejects_requests_without_an_administrator_cookie(): void
    {
        $this->get(route('add.admin.user'))->assertRedirect(route('login.form'));
    }

    public function test_admin_layout_uses_the_shared_accessible_logout_confirmation(): void
    {
        $adminId = $this->createAccount('Administrator', 'admin@example.com', 'Y');

        $this->withCookie('GTA', (string) $adminId)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="logoutModal"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('openLogoutModal(event)', false)
            ->assertSee('administration console')
            ->assertSee('id="logout-form"', false)
            ->assertSee('Stay signed in');
    }

    public function test_logout_clears_admin_authentication_and_redirects_to_login(): void
    {
        $adminId = $this->createAccount('Administrator', 'admin@example.com', 'Y');

        $this->withCookie('GTA', (string) $adminId)
            ->post(route('logout'))
            ->assertRedirect(route('login.form'));
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
