<?php

namespace Tests\Feature;

use Tests\TestCase;

class InformationPagesTest extends TestCase
{
    public function test_public_information_pages_render_their_content(): void
    {
        $pages = [
            ['/privacy-policy', 'Privacy Policy', 'Effective date'],
            ['/user-manual', 'Billora User Manual', 'getting-started'],
            ['/documentation', 'Billora Technical Documentation', 'Implemented areas'],
        ];

        foreach ($pages as [$url, $heading, $content]) {
            $this->get($url)
                ->assertOk()
                ->assertSee($heading)
                ->assertSee($content, false)
                ->assertSee(route('privacy-policy'), false)
                ->assertSee(route('user-manual'), false)
                ->assertSee(route('documentation'), false);
        }
    }
}
