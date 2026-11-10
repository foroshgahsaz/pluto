<?php

namespace Tests\Feature;

use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_public_root_returns_successful_response(): void
    {
        Site::factory()->central()->create([
            'slug' => 'central',
            'is_active' => true,
        ]);

        $this->get('/')->assertOk();
    }
}
