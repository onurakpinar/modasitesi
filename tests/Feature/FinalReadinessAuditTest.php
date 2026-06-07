<?php

namespace Tests\Feature;

use App\Enums\PageStatus;
use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\Ads\AdSettings;
use App\Support\PublicContent;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class FinalReadinessAuditTest extends TestCase
{
    use PublishesStaticPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([CategorySeeder::class, PageSeeder::class]);
        $this->publishStaticPagesForTests();

        $this->seedSiteSettingsForAudit();
    }

    private function seedSiteSettingsForAudit(): void
    {
        \App\Models\SiteSetting::set('site_name', 'Denetim Moda Sitesi', 'general');
        \App\Models\SiteSetting::set('contact_email', 'iletisim@moda.test', 'contact');
        AdSettings::setBoolean('privacy_policy_completed', true);
        AdSettings::setBoolean('cookie_policy_completed', true);
        AdSettings::setBoolean('contact_information_completed', true);
        AdSettings::setBoolean('editorial_information_completed', true);
    }

    public function test_public_route_smoke_denetimi(): void
    {
        $author = Author::factory()->create(['is_active' => true, 'short_bio' => 'Moda editörü biyografisi.']);
        $category = Category::query()->where('name', 'Stil Rehberi')->firstOrFail();
        $tag = Tag::factory()->create(['name' => 'Minimal Stil', 'slug' => 'minimal-stil']);
        $post = Post::factory()->published()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'slug' => 'denetim-yazi-slug',
            'title' => 'Denetim İçin Örnek Yayın Başlığı Yeterince Uzun',
            'human_reviewed_at' => now(),
            'originality_confirmed_at' => now(),
        ]);
        $post->tags()->attach($tag);

        $routes = [
            ['GET', route('home'), 200],
            ['GET', route('posts.index'), 200],
            ['GET', route('categories.show', $category->slug), 200],
            ['GET', route('posts.show', $post->slug), 200],
            ['GET', route('tags.show', $tag->slug), 200],
            ['GET', route('authors.show', $author->slug), 200],
            ['GET', route('search', ['q' => 'stil']), 200],
            ['GET', route('sitemap'), 200],
            ['GET', route('rss'), 200],
            ['GET', route('robots'), 200],
            ['GET', route('ads.txt'), 200],
            ['GET', route('health'), 200],
            ['GET', '/up', 200],
            ['GET', '/gecersiz-url-denetim-404', 404],
        ];

        foreach (array_keys(PublicContent::staticPageRoutes()) as $routeName) {
            $routes[] = ['GET', route($routeName), 200];
        }

        foreach ($routes as [$method, $url, $status]) {
            $this->json($method, $url)->assertStatus($status, "Route başarısız: {$url}");
        }
    }

    public function test_adsense_politika_korumalari(): void
    {
        AdSettings::simulateEnvironment('production');
        AdSettings::setBoolean('adsense_ads_enabled', true);
        AdSettings::setBoolean('certified_cmp_configured', false);

        $post = Post::factory()->published()->create();
        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringNotContainsString('adsbygoogle', $html);
        $this->assertStringNotContainsString('ad-slot', $html);

        $this->get(route('search', ['q' => 'moda']))->assertDontSee('adsbygoogle', false);
        $this->get('/admin/login')->assertDontSee('adsbygoogle', false);

        foreach (array_keys(PublicContent::staticPageRoutes()) as $routeName) {
            $this->get(route($routeName))->assertDontSee('adsbygoogle', false);
        }

        $this->get('/gecersiz-sayfa-404')->assertDontSee('adsbygoogle', false);

        AdSettings::resetSimulation();
    }

    public function test_mobil_uyum_siniflari_mevcut(): void
    {
        $post = Post::factory()->published()->create(['slug' => 'mobil-denetim']);

        $home = $this->get(route('home'))->getContent();
        $this->assertStringContainsString('max-w-6xl px-4', $home);
        $this->assertStringContainsString('lg:hidden', $home);

        $detail = $this->get(route('posts.show', $post->slug))->getContent();
        $this->assertStringContainsString('sm:py-14', $detail);

        $login = $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('admin.dashboard'))
            ->getContent();
        $this->assertStringContainsString('lg:pl-64', $login);
        $this->assertStringContainsString('sidebarOpen', $login);
    }
}
