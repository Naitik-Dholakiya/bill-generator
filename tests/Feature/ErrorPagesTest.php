<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_not_found_uses_the_custom_404_page(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('We couldn’t find that page')
            ->assertSee('Go to home');
    }

    public function test_forbidden_response_uses_the_custom_403_page(): void
    {
        Route::get('/__test/forbidden', fn () => abort(403));

        $this->get('/__test/forbidden')
            ->assertForbidden()
            ->assertSee('You can’t access this page');
    }

    public function test_other_client_and_server_errors_use_custom_fallback_pages(): void
    {
        Route::get('/__test/client-error', fn () => abort(422));
        Route::get('/__test/server-error', fn () => abort(502));

        $this->get('/__test/client-error')
            ->assertStatus(422)
            ->assertSee('Request error')
            ->assertSee('422');

        $this->get('/__test/server-error')
            ->assertStatus(502)
            ->assertSee('Service error')
            ->assertSee('502');
    }

    public function test_common_status_codes_use_their_dedicated_pages(): void
    {
        Route::get('/__test/error/{status}', fn (string $status) => abort((int) $status))
            ->whereIn('status', [401, 419, 429, 500, 503]);

        $pages = [
            401 => 'Sign in required',
            419 => 'Your session has expired',
            429 => 'Please slow down',
            500 => 'Something went wrong',
            503 => 'We’ll be back shortly',
        ];

        foreach ($pages as $status => $heading) {
            $this->get("/__test/error/{$status}")
                ->assertStatus($status)
                ->assertSee($heading);
        }
    }
}
