<?php

namespace App\Console\Commands;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Support\HomePageCache;
use App\Support\Seo\SitemapGenerator;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoPostSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Console\Command;

class SiteEnsureContentCommand extends Command
{
    protected $signature = 'site:ensure-content
                            {--force : Üretim ortamında seed çalıştır}
                            {--demo : Demo yazıları taslak olarak yükler (otomatik yayınlamaz)}';

    protected $description = 'Migration ve önbellek kontrolü; isteğe bağlı demo içerik (taslak) yükler';

    public function handle(HomePageCache $homePageCache, SitemapGenerator $sitemapGenerator): int
    {
        $this->callSilent('migrate', ['--force' => true]);

        $visibleCount = Post::query()->publiclyVisible()->count();

        if ($this->option('demo')) {
            $this->info('Demo yazılar taslak olarak yükleniyor…');

            foreach ([CategorySeeder::class, PageSeeder::class] as $seeder) {
                $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
            }

            $this->call('db:seed', [
                '--class' => DemoPostSeeder::class,
                '--force' => $this->option('force') || true,
            ]);

            $draftCount = Post::query()->where('status', PostStatus::Draft)->count();
            $this->info("Demo yükleme tamamlandı. Taslak yazı: {$draftCount}; yayında: {$visibleCount}.");
        } elseif ($visibleCount === 0) {
            $this->warn('Yayınlı yazı yok. Özgün içerik ekleyin veya geliştirme için: php artisan site:ensure-content --demo');
        } else {
            $this->comment("Yayınlı yazılar mevcut ({$visibleCount}).");
        }

        $homePageCache->forget();
        $sitemapGenerator->forget();
        $this->callSilent('cache:clear');

        return self::SUCCESS;
    }
}
