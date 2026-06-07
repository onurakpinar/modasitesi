<?php

namespace Tests\Feature\Admin;

use App\Models\ContentBrief;
use App\Support\Editorial\EditorialBriefCatalog;
use Database\Seeders\ContentBriefSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentBriefSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_brief_seeder_idempotent_calisir(): void
    {
        $this->seed(ContentBriefSeeder::class);
        $this->seed(ContentBriefSeeder::class);

        $this->assertSame(30, ContentBrief::query()->count());
        $this->assertSame(30, count(EditorialBriefCatalog::definitions()));
    }
}
