<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Support\Ads\PageTemplates;
use App\Support\PublicContent;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $labels = PublicContent::staticPageLabels();
        $templates = PageTemplates::defaultBodies();

        foreach (PublicContent::staticPageRoutes() as $slug) {
            Page::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $labels[$slug] ?? $slug,
                    'body' => $templates[$slug] ?? '',
                    'status' => PageStatus::Draft,
                ]
            );
        }
    }
}
