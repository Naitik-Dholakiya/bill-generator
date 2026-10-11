<?php

namespace Tests\Feature;

use App\Mail\NewUserCredentials;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminUserCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.default' => 'smtp']);

        Schema::create('usermaster', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_gstin')->nullable();
            $table->string('is_admin', 1)->default('N');
            $table->string('status', 1)->default('1');
            $table->boolean('is_jewelry')->default(false);
            $table->string('invoice_template')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_user_creation_emails_the_new_users_login_credentials(): void
    {
        Mail::fake();

        $adminId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => securePassword('AdminPass123!', 'admin@example.com'),
            'is_admin' => 'Y',
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withCookie('GTA', (string) $adminId)
            ->post(route('admin.users.store'), [
                'full_name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'UserPass123!',
                'password_confirmation' => 'UserPass123!',
                'is_admin' => 'N',
                'status' => '1',
                'is_jewelry' => '0',
                'invoice_template' => 'pdf',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'User account created.');

        $this->assertDatabaseHas('usermaster', [
            'email' => 'newuser@example.com',
            'password' => securePassword('UserPass123!', 'newuser@example.com'),
        ]);

        Mail::assertSent(NewUserCredentials::class, function (NewUserCredentials $mail): bool {
            $mail->assertSeeInHtml('newuser@example.com');
            $mail->assertSeeInHtml('UserPass123!');
            $mail->assertSeeInHtml(route('login.form'));

            return $mail->hasTo('newuser@example.com')
                && $mail->subject === 'Your '.config('app.name').' sign-in details';
        });
    }

    public function test_admin_can_reset_an_existing_users_password_and_resend_credentials(): void
    {
        Mail::fake();

        $adminId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => securePassword('AdminPass123!', 'admin@example.com'),
            'is_admin' => 'Y',
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => securePassword('OldPass123!', 'existing@example.com'),
            'is_admin' => 'N',
            'status' => '1',
            'is_jewelry' => false,
            'invoice_template' => 'pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $accountData = [
            'full_name' => 'Existing User',
            'email' => 'existing@example.com',
            'send_credentials' => '1',
            'is_admin' => 'N',
            'status' => '1',
            'is_jewelry' => '0',
            'invoice_template' => 'pdf',
        ];

        $this->withCookie('GTA', (string) $adminId)
            ->put(route('admin.users.update', $userId), $accountData)
            ->assertSessionHasErrors('password');

        $this->assertDatabaseHas('usermaster', [
            'user_id' => $userId,
            'password' => securePassword('OldPass123!', 'existing@example.com'),
        ]);
        Mail::assertNothingSent();

        $this->withCookie('GTA', (string) $adminId)
            ->put(route('admin.users.update', $userId), $accountData + [
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'Account password updated and credentials email sent.');

        $this->assertDatabaseHas('usermaster', [
            'user_id' => $userId,
            'password' => securePassword('NewPass123!', 'existing@example.com'),
        ]);

        Mail::assertSent(NewUserCredentials::class, function (NewUserCredentials $mail): bool {
            $mail->assertSeeInHtml('existing@example.com');
            $mail->assertSeeInHtml('NewPass123!');

            return $mail->hasTo('existing@example.com');
        });
    }

    public function test_admin_sees_that_the_log_mailer_does_not_deliver_created_user_email(): void
    {
        Mail::fake();
        config(['mail.default' => 'log']);

        $adminId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => securePassword('AdminPass123!', 'admin@example.com'),
            'is_admin' => 'Y',
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withCookie('GTA', (string) $adminId)
            ->post(route('admin.users.store'), [
                'full_name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'UserPass123!',
                'password_confirmation' => 'UserPass123!',
                'is_admin' => 'N',
                'status' => '1',
                'is_jewelry' => '0',
                'invoice_template' => 'pdf',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error', "Account created, but credentials email was not sent. The configured mailer is 'log', which does not deliver email. Configure a real SMTP or other delivery mailer.");

        Mail::assertNothingSent();
    }

    public function test_admin_sees_the_mail_transport_error_when_delivery_fails(): void
    {
        config(['mail.default' => 'smtp']);
        Mail::shouldReceive('to')
            ->once()
            ->with('newuser@example.com')
            ->andThrow(new \RuntimeException('SMTP connection refused'));

        $adminId = DB::table('usermaster')->insertGetId([
            'full_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => securePassword('AdminPass123!', 'admin@example.com'),
            'is_admin' => 'Y',
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withCookie('GTA', (string) $adminId)
            ->post(route('admin.users.store'), [
                'full_name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'UserPass123!',
                'password_confirmation' => 'UserPass123!',
                'is_admin' => 'N',
                'status' => '1',
                'is_jewelry' => '0',
                'invoice_template' => 'pdf',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error', 'Account created, but credentials email was not sent. Mailer error: SMTP connection refused');
    }
}
