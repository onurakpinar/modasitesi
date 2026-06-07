<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\Content\ContentPlaceholderCleaner;
use App\Support\HomePageCache;
use App\Support\Seo\SitemapGenerator;
use Illuminate\Console\Command;

class CleanContentPlaceholdersCommand extends Command
{
    protected $signature = 'content:clean-placeholders
                            {--dry-run : Değişiklikleri kaydetmeden yalnızca raporla}';

    protected $description = 'Yazı içeriklerindeki editoryal kalıntıları ve placeholder\'ları temizler';

    public function handle(
        ContentPlaceholderCleaner $cleaner,
        HomePageCache $homePageCache,
        SitemapGenerator $sitemapGenerator,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $updatedPosts = 0;
        $totalMatches = 0;

        $this->info($dryRun ? 'Kuru çalıştırma: değişiklik kaydedilmeyecek.' : 'Placeholder temizliği başlıyor…');

        Post::query()
            ->withTrashed()
            ->orderBy('id')
            ->chunkById(50, function ($posts) use ($cleaner, $dryRun, &$updatedPosts, &$totalMatches) {
                foreach ($posts as $post) {
                    $changes = [];

                    foreach (['body' => $post->body, 'excerpt' => $post->excerpt ?? ''] as $field => $value) {
                        if ($value === null || $value === '') {
                            continue;
                        }

                        $result = $cleaner->clean($value);

                        if ($result['cleaned'] === $result['original']) {
                            continue;
                        }

                        $changes[$field] = $result;
                        $totalMatches += count($result['matches']);
                    }

                    if ($changes === []) {
                        continue;
                    }

                    $this->line("• [{$post->id}] {$post->title} ({$post->slug})");

                    foreach ($changes as $field => $result) {
                        foreach ($result['matches'] as $match) {
                            $this->line("  - {$field}: {$match}");
                        }
                    }

                    if (! $dryRun) {
                        foreach ($changes as $field => $result) {
                            $post->{$field} = $result['cleaned'];
                        }

                        $post->save();
                    }

                    $updatedPosts++;
                }
            });

        if ($updatedPosts === 0) {
            $this->info('Temizlenecek placeholder bulunamadı.');
        } else {
            $this->info(($dryRun ? 'Temizlenecek' : 'Güncellenen')." yazı: {$updatedPosts}; eşleşme: {$totalMatches}");
        }

        if (! $dryRun && $updatedPosts > 0) {
            $homePageCache->forget();
            $sitemapGenerator->forget();
            $this->callSilent('cache:clear');
            $this->comment('Önbellek temizlendi.');
        }

        return self::SUCCESS;
    }
}
