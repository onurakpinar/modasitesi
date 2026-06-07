<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\Content\ContentQualityAuditor;
use App\Support\Editorial\EditorialArticleLoader;
use App\Support\Editorial\EditorialBriefCatalog;
use App\Support\HomePageCache;
use App\Support\PostQualityChecker;
use App\Support\Seo\SitemapGenerator;
use Illuminate\Console\Command;

class ImportEditorialArticlesCommand extends Command
{
    protected $signature = 'content:import-articles
                            {--dry-run : Kaydetmeden kelime sayısını raporla}';

    protected $description = 'Özgün editoryal makale dosyalarını posts tablosuna aktarır';

    public function handle(
        HomePageCache $homePageCache,
        SitemapGenerator $sitemapGenerator,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $imported = 0;
        $missing = [];

        foreach (EditorialBriefCatalog::definitions() as $brief) {
            $title = (string) $brief['title_suggestion'];

            try {
                $article = EditorialArticleLoader::forTitle($title);
            } catch (\Throwable $exception) {
                $missing[] = $title;
                $this->error($exception->getMessage());

                continue;
            }

            $words = PostQualityChecker::wordCount($article['body']);

            if ($words < 700 || $words > 1150) {
                $this->warn("  [{$words} kelime] {$title}");
            } else {
                $this->line("  [{$words} kelime] {$title}");
            }

            if ($dryRun) {
                $imported++;

                continue;
            }

            $post = Post::query()->where('title', $title)->orderBy('id')->first();

            if (! $post) {
                $this->warn("  DB kaydı yok, atlanıyor: {$title}");

                continue;
            }

            $post->update([
                'excerpt' => $article['excerpt'],
                'body' => $article['body'],
                'sources' => $article['sources'],
                'content_updated_at' => now(),
            ]);

            $imported++;
        }

        if ($missing !== []) {
            $this->error('Eksik makale dosyası: '.count($missing));

            return self::FAILURE;
        }

        $this->info(($dryRun ? 'Kontrol edilen' : 'Güncellenen')." yazı: {$imported}");

        if (! $dryRun && $imported > 0) {
            $homePageCache->forget();
            $sitemapGenerator->forget();
            $this->callSilent('cache:clear');
        }

        return self::SUCCESS;
    }
}
