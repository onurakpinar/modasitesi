<?php

namespace Database\Seeders;

use App\Models\ContentBrief;
use App\Support\Editorial\EditorialBriefCatalog;
use Illuminate\Database\Seeder;

class ContentBriefSeeder extends Seeder
{
    public function run(): void
    {
        foreach (EditorialBriefCatalog::definitions() as $definition) {
            ContentBrief::query()->firstOrCreate(
                ['title_suggestion' => $definition['title_suggestion']],
                $definition
            );
        }
    }
}
