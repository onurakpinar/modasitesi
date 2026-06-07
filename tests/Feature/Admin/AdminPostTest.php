<?php

namespace Tests\Feature\Admin;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AdminPostTest extends TestCase
{
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

    public function test_taslak_yazi_kisa_icerikle_kaydedilebilir(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Kısa taslak başlığı yeterli uzunlukta',
            'body' => '<p>Kısa taslak içerik.</p>',
            'status' => PostStatus::Draft->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Kısa taslak başlığı yeterli uzunlukta',
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function test_yayin_kalite_kurallari_eksikse_engellenir(): void
    {
        $response = $this->actingAs($this->admin)->from(route('admin.posts.create'))->post(route('admin.posts.store'), [
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Kısa',
            'excerpt' => 'Çok kısa özet',
            'body' => '<p>Az kelime.</p>',
            'status' => PostStatus::Published->value,
            'meta_title' => 'Meta',
            'meta_description' => 'Kısa',
        ]);

        $response->assertRedirect(route('admin.posts.create'));
        $response->assertSessionHasErrors(['title', 'excerpt', 'body', 'cover_image', 'originality_confirmed', 'human_reviewed']);
    }

    public function test_yayinlanabilir_yazi_tum_kurallarla_kaydedilir(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), $this->publishablePayload());

        $response->assertRedirect();

        $post = Post::query()->first();

        $this->assertNotNull($post);
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertNotNull($post->originality_confirmed_at);
        $this->assertNotNull($post->human_reviewed_at);
        $this->assertStringNotContainsString('<script', $post->body);
        $this->assertNotNull($post->cover_image);
    }

    public function test_guncellemede_revizyon_kaydi_olusturulur(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Eski Başlık Yeterince Uzun Olmalıdır',
            'body' => '<p>Eski içerik.</p>',
        ]);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post), [
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Yeni Başlık Yeterince Uzun Olmalıdır',
            'body' => '<p>Yeni içerik.</p>',
            'status' => PostStatus::Draft->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('post_revisions', [
            'post_id' => $post->id,
            'title' => 'Eski Başlık Yeterince Uzun Olmalıdır',
        ]);
    }

    public function test_revizyon_geri_yuklenebilir(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Güncel Başlık Yeterince Uzun Olmalıdır',
            'body' => '<p>Güncel içerik.</p>',
        ]);

        $revision = PostRevision::query()->create([
            'post_id' => $post->id,
            'user_id' => $this->admin->id,
            'title' => 'Eski Sürüm Başlığı Yeterince Uzun',
            'excerpt' => str_repeat('Eski özet metni. ', 12),
            'body' => '<p>Eski sürüm içeriği.</p>',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.posts.revisions.restore', [$post, $revision]))
            ->assertRedirect(route('admin.posts.edit', $post));

        $post->refresh();

        $this->assertSame('Eski Sürüm Başlığı Yeterince Uzun', $post->title);
        $this->assertStringContainsString('Eski sürüm içeriği', $post->body);
    }

    public function test_on_izleme_sadece_imzali_ve_admin_ile_acilir(): void
    {
        $post = Post::factory()->create([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'status' => PostStatus::Draft,
        ]);

        $signed = URL::temporarySignedRoute('admin.posts.preview', now()->addHour(), ['post' => $post->id]);

        $this->get($signed)->assertRedirect(route('admin.login'));

        $this->actingAs($this->admin)
            ->get($signed)
            ->assertOk()
            ->assertSee('noindex, nofollow', false)
            ->assertSee($post->title, false);
    }

    public function test_html_icerik_sanitize_edilir(): void
    {
        $payload = $this->publishablePayload([
            'body' => '<p>Güvenli</p><script>alert(1)</script>'.str_repeat(' <span>kelime</span>', 900),
        ]);

        $this->actingAs($this->admin)->post(route('admin.posts.store'), $payload);

        $post = Post::query()->first();
        $this->assertStringNotContainsString('<script', $post->body);
        $this->assertStringContainsString('Güvenli', $post->body);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function publishablePayload(array $overrides = []): array
    {
        $words = implode(' ', array_fill(0, 900, 'moda'));

        return array_merge([
            'author_id' => $this->author->id,
            'category_id' => $this->category->id,
            'title' => 'Kaliteli Moda Yazısı Başlığı Yeterince Uzundur',
            'excerpt' => 'Bu özet metni, yayın kalitesi standartlarını karşılayacak şekilde hazırlanmış özgün bir moda yazısı girişidir ve okura kapsamı net biçimde aktarır.',
            'body' => '<h2>Bölüm</h2><p>'.$words.'</p>',
            'status' => PostStatus::Published->value,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 900),
            'cover_image_alt' => 'Kış modasında katmanlı giyim örneği',
            'meta_title' => 'Moda Yazısı Meta Başlığı',
            'meta_description' => 'Moda ve stil üzerine özgün bir değerlendirme. Gardırop planlaması, trend analizi ve sürdürülebilir seçimler hakkında kapsamlı rehber sunar.',
            'originality_confirmed' => '1',
            'human_reviewed' => '1',
        ], $overrides);
    }
}
