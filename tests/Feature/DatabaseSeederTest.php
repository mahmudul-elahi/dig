<?php

namespace Tests\Feature;

use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_quotes(): void
    {
        $this->seed();

        $this->assertSame(20, Quote::count());
        $this->assertTrue(Quote::query()->whereNotNull('user_id')->exists());
    }
}
