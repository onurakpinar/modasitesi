<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\HomePageCache;
use App\Support\Seo\SitemapGenerator;
use Database\Seeders\DemoPostSeeder;
use Illuminate\Console\Command;

class SiteEnsureContentCommand extends Command
{
    protected $signature = 'site:ensure-content {--force : Üretim ortamında seed çalıştır}';

    protected $description = 'Yayınlı yazı yoksa demo blog içeriğini yükler ve önbelleği temizler';

    public function handle(HomePageCache $homePageCache, SitemapGenerator $sitemapGenerator): int
    {
        $this->callSilent('migrate', ['--force' => true]);

        $visibleCount = Post::query()->publiclyVisible()->count();

        if ($visibleCount === 0) {
            $this->info('Yayınlı yazı bulunamadı; 30 demo yazı yükleniyor…');

            $this->call('db:seed', [
                '--class' => DemoPostSeeder::class,
                '--force' => $this->option('force') || true,
            ]);

            $visibleCount = Post::query()->publiclyVisible()->count();

            if ($visibleCount === 0) {
                $this->error('Demo içerik yüklenemedi. Migration ve storage izinlerini kontrol edin.');

                return self::FAILURE;
            }

            $this->info("{$visibleCount} yayınlı yazı yüklendi.");
        } else {
            $this->comment("Yayınlı yazılar mevcut ({$visibleCount}).");
        }

        $homePageCache->forget();
        $sitemapGenerator->forget();
        $this->callSilent('cache:clear');

        return self::SUCCESS;
    }
}
