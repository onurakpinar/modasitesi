<?php

namespace Tests\Support;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Support\PublicContent;

trait PublishesStaticPages
{
    protected function publishStaticPagesForTests(): void
    {
        $replacements = [
            '[ISLETME_UNVANI]' => 'Test Yayın Ltd.',
            '[SITE_ADI]' => 'Test Moda Sitesi',
            '[SITE_URL]' => 'https://moda.test',
            '[ILETISIM_EPOSTA]' => 'iletisim@moda.test',
            '[GUNCELLEME_TARIHI]' => '1 Ocak 2026',
        ];

        foreach (array_values(PublicContent::staticPageRoutes()) as $slug) {
            $page = Page::query()->where('slug', $slug)->firstOrFail();

            $page->update([
                'body' => str_replace(array_keys($replacements), array_values($replacements), $page->body ?? ''),
                'status' => PageStatus::Published,
            ]);
        }
    }
}
