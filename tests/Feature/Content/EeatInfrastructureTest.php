<?php

namespace Tests\Feature\Content;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Post;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EeatInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
        URL::forceRootUrl('https://moda.test');
        URL::forceScheme('https');
    }

    public function test_yazar_profili_bio_ve_uzmanlik_alanlarini_destekler(): void
    {
        $author = Author::factory()->create([
            'bio' => 'Uzun biyografi metni burada yer alır.',
            'expertise' => 'Sürdürülebilir moda',
        ]);

        $this->get(route('authors.show', $author->slug))
            ->assertOk()
            ->assertSee('Uzun biyografi metni burada yer alır.', false)
            ->assertSee('Sürdürülebilir moda', false);
    }

    public function test_yazida_kaynaklar_bolumu_ve_guncelleme_tarihi_gorunur(): void
    {
        $author = Author::factory()->create();
        $post = Post::factory()->published()->create([
            'author_id' => $author->id,
            'slug' => 'kaynakli-yazi',
            'sources' => '<ul><li><a href="https://moda.test/kaynak">Örnek kaynak</a></li></ul>',
            'content_updated_at' => now()->addDay(),
            'originality_confirmed_at' => now(),
            'human_reviewed_at' => now(),
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee('Kaynaklar', false)
            ->assertSee('Örnek kaynak', false)
            ->assertSee('Güncelleme:', false);
    }

    public function test_article_json_ld_yayinci_ve_yazar_url_icerir(): void
    {
        $author = Author::factory()->create(['name' => 'Ayşe Yılmaz', 'slug' => 'ayse-yilmaz']);
        $post = Post::factory()->published()->create([
            'author_id' => $author->id,
            'slug' => 'json-ld-eeat',
            'title' => 'E-E-A-T Test',
        ]);

        $html = $this->get(route('posts.show', $post->slug))->getContent();

        $this->assertStringContainsString('GOAT Bilişim Teknolojileri Ticaret A.Ş.', $html);
        $this->assertStringContainsString(route('authors.show', 'ayse-yilmaz'), $html);
    }

    public function test_ana_sayfa_breadcrumb_json_ld_icerir(): void
    {
        $html = $this->get(route('home'))->getContent();

        $this->assertStringContainsString('BreadcrumbList', $html);
        $this->assertStringContainsString('Ana Sayfa', $html);
    }
}
