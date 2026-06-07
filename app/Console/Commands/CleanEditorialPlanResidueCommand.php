<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\Content\EditorialPlanResidueCleaner;
use App\Support\HomePageCache;
use App\Support\Seo\SitemapGenerator;
use Illuminate\Console\Command;

class CleanEditorialPlanResidueCommand extends Command
{
    protected $signature = 'content:clean-plan-residue
                            {--dry-run : Değişiklikleri kaydetmeden yalnızca raporla}';

    protected $description = 'Yazı gövdelerindeki üretim planı (plan özeti) kalıntılarını temizler';

    public function handle(
        EditorialPlanResidueCleaner $cleaner,
        HomePageCache $homePageCache,
        SitemapGenerator $sitemapGenerator,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $updatedPosts = 0;
        $totalMatches = 0;

        $this->info($dryRun ? 'Kuru çalıştırma: değişiklik kaydedilmeyecek.' : 'Plan kalıntısı temizliği başlıyor…');

        Post::query()
            ->withTrashed()
            ->orderBy('id')
            ->chunkById(50, function ($posts) use ($cleaner, $dryRun, &$updatedPosts, &$totalMatches) {
                foreach ($posts as $post) {
                    if ($post->body === null || $post->body === '') {
                        continue;
                    }

                    $result = $cleaner->clean($post->body);

                    if ($result['cleaned'] === $result['original']) {
                        continue;
                    }

                    $matchCount = count($result['matches']);
                    $totalMatches += $matchCount;

                    $this->line("• [{$post->id}] {$post->title} ({$matchCount} blok)");

                    if (! $dryRun) {
                        $post->body = $result['cleaned'];
                        $post->content_updated_at = now();
                        $post->save();
                    }

                    $updatedPosts++;
                }
            });

        if ($updatedPosts === 0) {
            $this->info('Temizlenecek plan kalıntısı bulunamadı.');
        } else {
            $this->info(($dryRun ? 'Temizlenecek' : 'Güncellenen')." yazı: {$updatedPosts}; kaldırılan blok: {$totalMatches}");
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
