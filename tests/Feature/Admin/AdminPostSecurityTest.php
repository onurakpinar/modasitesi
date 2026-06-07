<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use App\Support\PostQualityChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\Support\PublishablePostPayload;
use Tests\TestCase;

class AdminPostSecurityTest extends TestCase
{
    use PublishablePostPayload;
    use RefreshDatabase;

    private User $admin;

    private Author $author;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->superAdmin()->create();
        $this->author = Author::factory()->create();
        $this->category = Category::factory()->create();
    }

    public function test_699_kelimelik_yazi_yayinlanamaz(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'body' => $this->bodyWithWordCount(699),
            ])
        );

        $response->assertRedirect(route('admin.posts.create'));
        $response->assertSessionHasErrors('body');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_taslak_kisa_icerikle_kaydedilebilir(): void
    {
        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Taslak başlık yeterince uzun görünüyor',
            'body' => '<p>Kısa taslak.</p>',
            'status' => PostStatus::Draft->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('posts', ['status' => PostStatus::Draft->value]);
    }

    public function test_alt_metinsiz_gorsel_ile_yayin_engellenir(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'cover_image_alt' => '',
            ])
        );

        $response->assertSessionHasErrors('cover_image_alt');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_yazarsiz_yayin_engellenir(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'author_id' => '',
            ])
        );

        $response->assertSessionHasErrors('author_id');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_insan_kontrolu_onaysiz_yayin_engellenir(): void
    {
        $payload = $this->publishablePayload($this->author, $this->category);
        unset($payload['human_reviewed']);

        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $payload
        );

        $response->assertSessionHasErrors('human_reviewed');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_ozgunluk_onaysiz_yayin_engellenir(): void
    {
        $payload = $this->publishablePayload($this->author, $this->category);
        unset($payload['originality_confirmed']);

        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $payload
        );

        $response->assertSessionHasErrors('originality_confirmed');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_script_ve_onclick_sanitize_edilir(): void
    {
        $this->actingAs($this->admin)->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'body' => $this->bodyWithWordCount(900).'<script>alert(1)</script><p onclick="alert(2)">Tehlike</p>',
            ])
        )->assertRedirect();

        $post = Post::query()->firstOrFail();

        $this->assertStringNotContainsString('<script', $post->body);
        $this->assertStringNotContainsString('onclick', $post->body);
        $this->assertStringContainsString('Tehlike', $post->body);
    }

    public function test_php_dosyasi_yuklemesi_engellenir(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'cover_image' => UploadedFile::fake()->create('shell.php', '<?php echo "hack";', 'application/x-php'),
            ])
        );

        $response->assertSessionHasErrors('cover_image');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_svg_script_dosyasi_yuklemesi_engellenir(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'cover_image' => UploadedFile::fake()->create('evil.svg', $svg, 'image/svg+xml'),
            ])
        );

        $response->assertSessionHasErrors('cover_image');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_buyuk_dosya_yuklemesi_engellenir(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'cover_image' => UploadedFile::fake()->image('large.jpg')->size(6000),
            ])
        );

        $response->assertSessionHasErrors('cover_image');
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_on_izleme_yetkisiz_erisim_engellenir(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'status' => PostStatus::Draft,
        ]);

        $signed = URL::temporarySignedRoute('admin.posts.preview', now()->addHour(), ['post' => $post->id]);
        $expired = URL::temporarySignedRoute('admin.posts.preview', now()->subHour(), ['post' => $post->id]);

        $this->get($signed)->assertRedirect(route('admin.login'));

        $this->get(route('admin.posts.preview', $post))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($this->admin)
            ->get(route('admin.posts.preview', $post))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get($expired)
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get($signed)
            ->assertOk()
            ->assertDontSee('Server Error', false);
    }

    public function test_on_izleme_sayfasi_noindex_meta_icerir(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'status' => PostStatus::Draft,
        ]);

        $signed = URL::temporarySignedRoute('admin.posts.preview', now()->addHour(), ['post' => $post->id]);

        $this->actingAs($this->admin)
            ->get($signed)
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_revizyon_gecmisi_dogru_kaydedilir(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Kayıtlı Eski Başlık Yeterince Uzun',
            'body' => '<p>Eski gövde metni.</p>',
        ]);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post), [
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Güncellenmiş Başlık Yeterince Uzun',
            'body' => '<p>Yeni gövde metni.</p>',
            'status' => PostStatus::Draft->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('post_revisions', [
            'post_id' => $post->id,
            'title' => 'Kayıtlı Eski Başlık Yeterince Uzun',
            'body' => '<p>Eski gövde metni.</p>',
        ]);
    }

    public function test_eski_surume_donus_calisir(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Güncel Başlık Yeterince Uzun Olmalı',
            'body' => '<p>Güncel metin.</p>',
        ]);

        $revision = PostRevision::query()->create([
            'post_id' => $post->id,
            'user_id' => $this->admin->id,
            'title' => 'Geri Yüklenecek Başlık Yeterince Uzun',
            'excerpt' => str_repeat('Özet metni. ', 15),
            'body' => '<p>Eski sürüm metni.</p>',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.posts.revisions.restore', [$post, $revision]))
            ->assertRedirect(route('admin.posts.edit', $post));

        $post->refresh();
        $this->assertSame('Geri Yüklenecek Başlık Yeterince Uzun', $post->title);
    }

    public function test_editor_kullanici_yonetimine_erisemez(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_turkce_icerikte_kelime_sayaci_dogru_calisir(): void
    {
        $html = '<p>şişe çanta moda İstanbul trendleri gardırop kombin stil</p>';

        $this->assertSame(8, PostQualityChecker::wordCount($html));

        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'body' => $this->bodyWithWordCount(699),
            ])
        );

        $response->assertSessionHasErrors('body');

        $turkishWords = array_merge(array_fill(0, 698, 'moda'), ['şişe', 'çanta']);

        $this->actingAs($this->admin)->post(
            route('admin.posts.store'),
            $this->publishablePayload($this->author, $this->category, [
                'body' => '<p>'.implode(' ', $turkishWords).'</p>',
            ])
        )->assertRedirect();

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_baska_yazinin_revizyonu_geri_yuklenemez(): void
    {
        $postA = Post::factory()->create(['author_id' => $this->author->id, 'category_id' => $this->category->id]);
        $postB = Post::factory()->create(['author_id' => $this->author->id, 'category_id' => $this->category->id]);

        $revision = PostRevision::query()->create([
            'post_id' => $postB->id,
            'user_id' => $this->admin->id,
            'title' => 'B Başlığı Yeterince Uzun Olmalıdır',
            'excerpt' => null,
            'body' => '<p>B içeriği</p>',
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.posts.revisions.restore', [$postA, $revision]))
            ->assertNotFound();
    }
}
