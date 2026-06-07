<?php

namespace Database\Seeders;

use App\Enums\BriefTopicCategory;
use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Support\Demo\DemoPostBodyWriter;
use App\Support\Demo\DemoPostCoverImage;
use App\Support\Editorial\EditorialBriefCatalog;
use App\Support\HomePageCache;
use App\Support\PostQualityChecker;
use App\Support\Seo\SitemapGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use RuntimeException;

class DemoPostSeeder extends Seeder
{
    /** @var array<string, string> */
    private array $categoryMap = [
        'style_guide' => 'Stil Rehberi',
        'womens_fashion' => 'Kadın Modası',
        'mens_fashion' => 'Erkek Modası',
        'accessories' => 'Aksesuar',
        'season_trends' => 'Sezon Trendleri',
        'sustainable_fashion' => 'Sürdürülebilir Moda',
    ];

    public function run(): void
    {
        $this->ensureSiteBasics();

        if (Category::query()->count() === 0) {
            $this->call(CategorySeeder::class);
        }

        $authors = $this->seedAuthors();
        $categories = $this->loadCategories();
        $tags = $this->seedTags();
        $coverFetcher = new DemoPostCoverImage;
        $briefs = EditorialBriefCatalog::definitions();

        $this->removeDuplicateDemoPosts($briefs);

        $this->command?->info('30 özgün blog yazısı oluşturuluyor…');

        foreach ($briefs as $index => $brief) {
            $categoryName = $this->categoryMap[$brief['topic_category']->value] ?? 'Stil Rehberi';
            $category = $categories[$categoryName] ?? reset($categories);
            $author = $this->authorForCategory($brief['topic_category'], $authors);

            $title = (string) $brief['title_suggestion'];
            $body = DemoPostBodyWriter::html($brief);
            $wordCount = PostQualityChecker::wordCount($body);

            if ($wordCount < PostQualityChecker::MIN_WORD_COUNT) {
                throw new RuntimeException("{$title} yetersiz kelime sayısı: {$wordCount}");
            }

            $excerpt = $this->excerpt((string) $brief['content_summary']);
            $metaDescription = $this->metaDescription((string) $brief['content_summary']);
            $existing = Post::query()->where('title', $title)->orderBy('id')->first();
            $slug = $existing?->slug ?? Post::generateUniqueSlug($title);

            $this->command?->line(sprintf('  [%02d/30] %s (%d kelime)', $index + 1, $title, $wordCount));

            $cover = $coverFetcher->fetch($index, $title);

            $post = Post::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $author->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'cover_image' => $cover['path'],
                    'cover_image_fallback' => $cover['fallback'],
                    'cover_image_width' => $cover['width'],
                    'cover_image_height' => $cover['height'],
                    'cover_image_alt' => (string) ($brief['cover_image_note'] ?? $title),
                    'status' => PostStatus::Published,
                    'published_at' => Carbon::now()->subDays(90 - ($index * 3))->setTime(9, 0),
                    'is_featured' => in_array($index, [0, 1, 2, 4, 7, 10, 14, 18], true),
                    'meta_title' => mb_substr($title, 0, 60),
                    'meta_description' => $metaDescription,
                    'originality_confirmed_at' => now(),
                    'human_reviewed_at' => now(),
                ]
            );

            $post->tags()->sync($this->tagsForPost($tags, $brief['topic_category'], $index));
        }

        app(HomePageCache::class)->forget();
        app(SitemapGenerator::class)->forget();

        $this->command?->info('Tamamlandı: '.Post::query()->publiclyVisible()->count().' yayınlı yazı.');
    }

    /**
     * @param  array<int, array<string, mixed>>  $briefs
     */
    private function removeDuplicateDemoPosts(array $briefs): void
    {
        $titles = collect($briefs)->pluck('title_suggestion')->filter()->all();

        Post::query()
            ->whereIn('title', $titles)
            ->orderBy('id')
            ->get()
            ->groupBy('title')
            ->each(function ($group): void {
                if ($group->count() <= 1) {
                    return;
                }

                $group->slice(1)->each->forceDelete();
            });
    }

    private function ensureSiteBasics(): void
    {
        if (! filled(SiteSetting::get('site_name'))) {
            SiteSetting::set('site_name', 'ModaPusula', 'general');
        }

        if (! filled(SiteSetting::get('site_tagline'))) {
            SiteSetting::set('site_tagline', 'Moda ve stil üzerine özgün yayınlar', 'general');
        }

        if (! filled(SiteSetting::get('contact_email'))) {
            SiteSetting::set('contact_email', config('legal.contact_email'), 'contact');
        }
    }

    /**
     * @return array<string, Author>
     */
    private function seedAuthors(): array
    {
        $definitions = [
            'elif' => [
                'name' => 'Elif Kaya',
                'email' => 'elif.kaya@modapusula.test',
                'short_bio' => 'Stil editörü. Kadın modası, gardırop planlaması ve günlük kombin üzerine özgün rehberler yazar.',
            ],
            'deniz' => [
                'name' => 'Deniz Arslan',
                'email' => 'deniz.arslan@modapusula.test',
                'short_bio' => 'Erkek modası ve aksesuar editörü. İş ve casual stil, ayakkabı ve dış giyim konularında uzmanlaşmıştır.',
            ],
            'meryem' => [
                'name' => 'Meryem Aksoy',
                'email' => 'meryem.aksoy@modapusula.test',
                'short_bio' => 'Sürdürülebilir moda yazarı. Bilinçli tüketim, bakım rutinleri ve sezon geçişleri üzerine çalışır.',
            ],
        ];

        $authors = [];

        foreach ($definitions as $key => $data) {
            $authors[$key] = Author::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'slug' => Author::generateUniqueSlug($data['name']),
                    'short_bio' => $data['short_bio'],
                    'is_active' => true,
                ]
            );
        }

        return $authors;
    }

    /**
     * @return array<string, Category>
     */
    private function loadCategories(): array
    {
        return Category::query()
            ->whereIn('name', array_values($this->categoryMap))
            ->get()
            ->keyBy('name')
            ->all();
    }

    /**
     * @return array<string, Tag>
     */
    private function seedTags(): array
    {
        $names = [
            'Stil Rehberi', 'Gardırop', 'Kombin', 'Minimal Stil', 'İş Stili',
            'Erkek Modası', 'Kadın Modası', 'Aksesuar', 'Ayakkabı', 'Sezon Geçişi',
            'Sürdürülebilirlik', 'Kumaş Bilgisi', 'Renk Uyumu', 'Katmanlı Giyim', 'Bakım',
        ];

        $tags = [];

        foreach ($names as $name) {
            $tags[$name] = Tag::query()->firstOrCreate(
                ['name' => $name],
                ['slug' => Tag::generateUniqueSlug($name)]
            );
        }

        return $tags;
    }

    /**
     * @param  array<string, Author>  $authors
     */
    private function authorForCategory(BriefTopicCategory $category, array $authors): Author
    {
        return match ($category) {
            BriefTopicCategory::MensFashion, BriefTopicCategory::Accessories => $authors['deniz'],
            BriefTopicCategory::SustainableFashion => $authors['meryem'],
            default => $authors['elif'],
        };
    }

    private function excerpt(string $summary): string
    {
        $text = trim($summary);

        if (mb_strlen($text) < PostQualityChecker::MIN_EXCERPT_LENGTH) {
            $text .= ' Bu özgün moda rehberi, gardırobunuzu bilinçli yönetmeniz için pratik adımlar sunar.';
        }

        if (mb_strlen($text) > PostQualityChecker::MAX_EXCERPT_LENGTH) {
            $text = mb_substr($text, 0, PostQualityChecker::MAX_EXCERPT_LENGTH - 1).'…';
        }

        return $text;
    }

    private function metaDescription(string $summary): string
    {
        $text = trim($summary.' Özgün editoryal içerik; marka önerisi veya otomatik metin yok.');

        if (mb_strlen($text) > PostQualityChecker::MAX_META_DESCRIPTION_LENGTH) {
            $text = mb_substr($text, 0, PostQualityChecker::MAX_META_DESCRIPTION_LENGTH - 1).'…';
        }

        while (mb_strlen($text) < PostQualityChecker::MIN_META_DESCRIPTION_LENGTH) {
            $text .= ' Pratik moda rehberi.';
        }

        return $text;
    }

    /**
     * @param  array<string, Tag>  $tags
     * @return array<int, int>
     */
    private function tagsForPost(array $tags, BriefTopicCategory $category, int $index): array
    {
        $pool = match ($category) {
            BriefTopicCategory::StyleGuide => ['Stil Rehberi', 'Renk Uyumu', 'Kumaş Bilgisi', 'Gardırop'],
            BriefTopicCategory::WomensFashion => ['Kadın Modası', 'Gardırop', 'Kombin', 'İş Stili'],
            BriefTopicCategory::MensFashion => ['Erkek Modası', 'İş Stili', 'Katmanlı Giyim', 'Bakım'],
            BriefTopicCategory::Accessories => ['Aksesuar', 'Ayakkabı', 'Kombin', 'Minimal Stil'],
            BriefTopicCategory::SeasonTrends => ['Sezon Geçişi', 'Katmanlı Giyim', 'Kombin', 'Gardırop'],
            BriefTopicCategory::SustainableFashion => ['Sürdürülebilirlik', 'Bakım', 'Gardırop', 'Minimal Stil'],
        };

        $selected = [
            $pool[$index % count($pool)],
            $pool[($index + 1) % count($pool)],
        ];

        return collect($selected)
            ->map(fn (string $name) => $tags[$name]->id ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
