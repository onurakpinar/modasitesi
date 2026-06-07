<?php

namespace Tests\Feature\Admin;

use App\Enums\BriefStatus;
use App\Enums\BriefTopicCategory;
use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\ContentBrief;
use App\Models\Post;
use App\Models\User;
use App\Support\Editorial\EditorialBriefCatalog;
use App\Support\PostQualityChecker;
use App\Support\PublicContent;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ContentBriefSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\PublishesStaticPages;
use Tests\TestCase;

class ContentBriefTest extends TestCase
{
    use PublishesStaticPages;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->superAdmin()->create();
    }

    public function test_seeder_otuz_ozgun_brief_olusturur_ve_yazi_uretmez(): void
    {
        $postsBefore = Post::query()->count();

        $this->seed(ContentBriefSeeder::class);

        $this->assertSame(30, ContentBrief::query()->count());
        $this->assertSame(6, ContentBrief::query()->distinct('topic_category')->count('topic_category'));
        $this->assertSame($postsBefore, Post::query()->count());
        $this->assertDatabaseMissing('posts', ['title' => ContentBrief::query()->value('title_suggestion')]);
    }

    public function test_seed_briefleri_birbirinden_belirgin_sekilde_farklidir(): void
    {
        $this->seed(ContentBriefSeeder::class);

        $titles = ContentBrief::query()->pluck('title_suggestion');
        $summaries = ContentBrief::query()->pluck('content_summary');

        $this->assertCount(30, $titles->unique()->values());
        $this->assertCount(30, $summaries->unique()->values());

        foreach (BriefTopicCategory::cases() as $category) {
            $this->assertSame(
                5,
                ContentBrief::query()->where('topic_category', $category)->count(),
                $category->label().' kategorisinde 5 brief olmalı.'
            );
        }

        $this->assertSame(30, count(EditorialBriefCatalog::definitions()));
    }

    public function test_super_admin_icerik_takvimi_crud_yapabilir(): void
    {
        $editor = User::factory()->create(['name' => 'Editör Ayşe']);

        $this->actingAs($this->admin)
            ->get(route('admin.content-briefs.index'))
            ->assertOk()
            ->assertSee('İçerik Takvimi', false);

        $this->actingAs($this->admin)
            ->post(route('admin.content-briefs.store'), $this->briefPayload([
                'assigned_editor_id' => $editor->id,
            ]))
            ->assertRedirect(route('admin.content-briefs.index'));

        $brief = ContentBrief::query()->first();
        $this->assertNotNull($brief);

        $this->actingAs($this->admin)
            ->get(route('admin.content-briefs.edit', $brief))
            ->assertOk()
            ->assertSee('Vücut tipine göre pantolon seçimi rehberi', false);

        $this->actingAs($this->admin)
            ->put(route('admin.content-briefs.update', $brief), $this->briefPayload([
                'content_summary' => 'Güncellenmiş özet metni.',
                'status' => BriefStatus::Preparing->value,
                'assigned_editor_id' => $editor->id,
            ]))
            ->assertRedirect(route('admin.content-briefs.index'));

        $brief->refresh();
        $this->assertSame(BriefStatus::Preparing, $brief->status);

        $this->actingAs($this->admin)
            ->delete(route('admin.content-briefs.destroy', $brief))
            ->assertRedirect(route('admin.content-briefs.index'));

        $this->assertDatabaseMissing('content_briefs', ['id' => $brief->id]);
    }

    public function test_editor_brief_olusturup_duzenleyebilir(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.content-briefs.create'))
            ->assertOk();

        $this->actingAs($editor)
            ->post(route('admin.content-briefs.store'), $this->briefPayload([
                'title_suggestion' => 'Editör tarafından eklenen brief başlığı',
            ]))
            ->assertRedirect(route('admin.content-briefs.index'));

        $brief = ContentBrief::query()->firstOrFail();

        $this->actingAs($editor)
            ->put(route('admin.content-briefs.update', $brief), $this->briefPayload([
                'title_suggestion' => 'Editör tarafından güncellenen brief başlığı',
                'content_summary' => 'Editörün güncellediği özet metni.',
            ]))
            ->assertRedirect(route('admin.content-briefs.index'));

        $brief->refresh();
        $this->assertSame('Editör tarafından güncellenen brief başlığı', $brief->title_suggestion);
    }

    public function test_editor_brief_silemez(): void
    {
        $editor = User::factory()->create();
        $brief = ContentBrief::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.content-briefs.index'))
            ->assertOk()
            ->assertDontSee('>Sil<', false);

        $this->actingAs($editor)
            ->delete(route('admin.content-briefs.destroy', $brief))
            ->assertForbidden();

        $this->assertDatabaseHas('content_briefs', ['id' => $brief->id]);
    }

    public function test_misafir_icerik_takvimine_erisemez(): void
    {
        $this->get(route('admin.content-briefs.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.content-briefs.create'))->assertRedirect(route('admin.login'));
    }

    public function test_pasif_kullanici_icerik_takvimine_erisemez(): void
    {
        $inactive = User::factory()->inactive()->create();

        $this->actingAs($inactive)
            ->get(route('admin.content-briefs.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_briefler_public_tarafta_erisilemez(): void
    {
        $this->seed(ContentBriefSeeder::class);

        $brief = ContentBrief::query()->firstOrFail();

        foreach (Route::getRoutes() as $route) {
            if (str_contains($route->uri(), 'content-brief') && ! str_starts_with($route->uri(), 'admin/')) {
                $this->fail('Brief için public route bulunmamalı: '.$route->uri());
            }
        }

        $this->get('/content-briefs')->assertNotFound();
        $this->get('/icerik-takvimi')->assertNotFound();
        $this->get('/admin/content-briefs')->assertRedirect(route('admin.login'));

        $slug = str($brief->title_suggestion)->slug()->toString();
        $this->get(route('posts.show', $slug))->assertNotFound();
        $this->get(route('home'))->assertDontSee($brief->title_suggestion, false);
        $this->get(route('posts.index'))->assertDontSee($brief->title_suggestion, false);

        $this->assertNull(Route::getRoutes()->getByName('content-briefs.show'));
        $this->assertNull(Route::getRoutes()->getByName('admin.content-briefs.show'));
    }

    public function test_briefler_sitemap_icinde_yer_almaz(): void
    {
        $this->seed(ContentBriefSeeder::class);

        $title = ContentBrief::query()->value('title_suggestion');
        $this->assertNotNull($title);

        $content = $this->get(route('sitemap'))->getContent();

        $this->assertStringNotContainsString('content-brief', $content);
        $this->assertStringNotContainsString('icerik-takvimi', $content);
        $this->assertStringNotContainsString($title, $content);
    }

    public function test_briefler_arama_sonuclarinda_gorunmez(): void
    {
        $this->seed(ContentBriefSeeder::class);

        $brief = ContentBrief::query()
            ->where('title_suggestion', 'like', '%Kapsül Gardırop%')
            ->firstOrFail();

        $this->get(route('search', ['q' => 'Kapsül Gardırop']))
            ->assertOk()
            ->assertDontSee($brief->title_suggestion, false)
            ->assertDontSee($brief->content_summary, false);

        $this->get(route('search', ['q' => 'İçerik Takvimi']))
            ->assertOk()
            ->assertDontSee('content_briefs', false);
    }

    public function test_brief_seed_sonrasi_public_tarafta_sahte_icerik_yok(): void
    {
        $this->seed([
            CategorySeeder::class,
            PageSeeder::class,
            ContentBriefSeeder::class,
        ]);
        $this->publishStaticPagesForTests();

        Post::factory()->create([
            'title' => 'Gizli Taslak Brief Sonrası',
            'slug' => 'gizli-taslak-brief-sonrasi',
            'status' => PostStatus::Draft,
            'body' => '<p>Taslak içerik görünmemeli.</p>',
        ]);

        $briefTitle = ContentBrief::query()->value('title_suggestion');
        $this->assertNotNull($briefTitle);

        foreach ([route('home'), route('posts.index')] as $url) {
            $html = $this->get($url)->getContent();
            $this->assertStringNotContainsString('Lorem ipsum', $html);
            $this->assertStringNotContainsString($briefTitle, $html);
            $this->assertStringNotContainsString('Gizli Taslak Brief Sonrası', $html);
        }

        foreach (array_values(PublicContent::staticPageRoutes()) as $slug) {
            $html = $this->get('/'.$slug)->getContent();
            $this->assertStringNotContainsString('Lorem ipsum', $html);
            $this->assertStringNotContainsString($briefTitle, $html);
        }
    }

    public function test_dashboard_editorial_sayilari_dogrudur(): void
    {
        $this->seed(ContentBriefSeeder::class);

        $author = Author::factory()->create();
        $category = Category::factory()->create();

        Post::query()->create([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Yayında ama eksik yazı başlığı yeterince uzun',
            'slug' => 'yayinda-eksik',
            'body' => '<p>Kısa.</p>',
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('briefTotalCount', 30)
            ->assertViewHas('briefPreparingCount', 6)
            ->assertViewHas('briefReviewCount', 6)
            ->assertViewHas('briefCompletedCount', 2)
            ->assertViewHas('applicationReadyPublishedCount', 0)
            ->assertViewHas('publishedCount', 1);

        $response->assertSee('Editoryal Hazırlık', false)
            ->assertSee('Toplam Brief', false)
            ->assertSee('Başvuru Hazır Yayın', false);
    }

    public function test_dashboard_basvuru_hazir_yayin_sayisi_kalite_kurallarina_gore_hesaplanir(): void
    {
        $checker = app(PostQualityChecker::class);
        $author = Author::factory()->create(['is_active' => true]);
        $category = Category::factory()->create(['is_active' => true]);

        $readyCount = Post::query()
            ->where('status', PostStatus::Published)
            ->get()
            ->filter(fn (Post $post) => $checker->isPublishable($post))
            ->count();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('applicationReadyPublishedCount', $readyCount);
    }

    public function test_editorial_guide_dosyasi_eksiksizdir(): void
    {
        $path = base_path('docs/editorial-guide.md');

        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertNotFalse($content);

        $requiredSections = [
            'Özgün içerik nedir?',
            'Kaynak gösterme yöntemi',
            'Görsel lisansı kontrolü',
            'İçeriğin insan tarafından kontrol edilmesi',
            'Kopya içerikten kaçınma',
            'Yapay zekâ destekli taslak',
            'Gereksiz uzunluk yerine kullanıcıya fayda',
            'Başlık ile içerik uyumu',
            'Yayın öncesi kontrol listesi',
        ];

        foreach ($requiredSections as $section) {
            $this->assertStringContainsString($section, $content, 'Eksik bölüm: '.$section);
        }

        $this->assertSame(30, count(EditorialBriefCatalog::definitions()));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function briefPayload(array $overrides = []): array
    {
        return array_merge([
            'title_suggestion' => 'Vücut tipine göre pantolon seçimi rehberi',
            'topic_category' => BriefTopicCategory::StyleGuide->value,
            'target_audience' => 'Günlük kombin arayan kadın okuyucular',
            'search_intent' => 'bilgilendirici rehber',
            'content_summary' => 'Farklı vücut oranları için pantolon kalıbı ve bel yüksekliği önerileri.',
            'subheadings' => "- Giriş\n- Bel yüksekliği\n- Paça formu\n- Sonuç",
            'suggested_internal_links' => '/kategori/stil-rehberi',
            'cover_image_note' => 'Nötr arka planlı özgün stüdyo çekimi',
            'planned_publish_date' => now()->addMonth()->toDateString(),
            'status' => BriefStatus::Idea->value,
            'assigned_editor_id' => null,
            'notes' => 'Ürün linki veya marka adı kullanılmayacak.',
        ], $overrides);
    }
}
