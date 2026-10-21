<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ResourceOwnershipMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usermaster', function (Blueprint $table): void {
            $table->id('user_id');
            $table->string('full_name');
            $table->string('email');
            $table->string('password');
            $table->string('is_admin', 1)->default('N');
            $table->string('status', 1)->default('1');
        });

        Schema::create('customermaster', function (Blueprint $table): void {
            $table->id('customer_id');
            $table->unsignedBigInteger('created_by');
        });

        Route::middleware(['web', 'user.auth', 'resource.owner:customermaster,customer_id,id'])
            ->get('/_test/customers/{id}', fn () => response()->noContent());
    }

    public function test_user_can_access_owned_resource_id(): void
    {
        $userId = $this->createUser();
        $customerId = DB::table('customermaster')->insertGetId(['created_by' => $userId]);

        $this->withCookie('GTA', (string) $userId)
            ->get('/_test/customers/'.$customerId)
            ->assertNoContent();
    }

    public function test_changing_resource_id_cannot_access_another_users_record(): void
    {
        $userId = $this->createUser();
        $otherUserId = $this->createUser();
        $customerId = DB::table('customermaster')->insertGetId(['created_by' => $otherUserId]);

        $this->withCookie('GTA', (string) $userId)
            ->get('/_test/customers/'.$customerId)
            ->assertNotFound();
    }

    private function createUser(): int
    {
        return DB::table('usermaster')->insertGetId([
            'full_name' => 'Workspace User',
            'email' => uniqid('user'). '@example.com',
            'password' => 'unused',
            'is_admin' => 'N',
            'status' => '1',
        ]);
    }
}
