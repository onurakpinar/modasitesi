<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\Content\ContentQualityAuditor;
use App\Support\Editorial\EditorialBriefCatalog;
use App\Support\HomePageCache;
use App\Support\PostQualityChecker;
use App\Support\Seo\SitemapGenerator;
use Illuminate\Console\Command;

class UnpublishFlaggedPostsCommand extends Command
{
    protected $signature = 'content:unpublish-flagged
                            {--dry-run : Yalnızca raporla, durumu değiştirme}';

    protected $description = 'Denetimde işaretlenen ve demo/test şablon yazılarını taslağa çeker';

    public function handle(
        ContentQualityAuditor $auditor,
        HomePageCache $homePageCache,
        SitemapGenerator $sitemapGenerator,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $audit = $auditor->audit();
        $flaggedIds = collect($audit['posts'])->where('flagged', true)->pluck('id')->all();

        $demoTitles = collect(EditorialBriefCatalog::definitions())
            ->pluck('title_suggestion')
            ->filter()
            ->all();

        $candidates = Post::query()
            ->where(function ($query) use ($flaggedIds, $demoTitles) {
                $query->whereIn('id', $flaggedIds)
                    ->orWhereIn('title', $demoTitles)
                    ->orWhere('title', 'like', 'Test yazısı%');
            })
            ->where('status', PostStatus::Published)
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Taslağa çekilecek yayınlı şablon yazı bulunamadı.');

            return self::SUCCESS;
        }

        $this->info(($dryRun ? 'Taslağa çekilecek' : 'Taslağa çekilen').' yazı: '.$candidates->count());

        foreach ($candidates as $post) {
            $words = PostQualityChecker::wordCount($post->body ?? '');
            $this->line("  [{$post->id}] {$post->title} ({$words} kelime)");

            if (! $dryRun) {
                $post->update([
                    'status' => PostStatus::Draft,
                    'is_featured' => false,
                ]);
            }
        }

        if (! $dryRun) {
            $homePageCache->forget();
            $sitemapGenerator->forget();
            $this->callSilent('cache:clear');
            $this->comment('Önbellek temizlendi.');
        }

        return self::SUCCESS;
    }
}
