<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentQualityCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_placeholders_komutu_editoryal_notlari_kaldirir(): void
    {
        $post = Post::factory()->create([
            'body' => '<p>Paragraf. Editoryal not: Optik muayene yerine geçmez notu eklenmeli. Son.</p>',
        ]);

        $this->artisan('content:clean-placeholders')
            ->assertSuccessful();

        $post->refresh();

        $this->assertStringNotContainsString('Editoryal not:', $post->body);
        $this->assertStringNotContainsString('eklenmeli', $post->body);
    }

    public function test_audit_quality_komutu_rapor_uretir(): void
    {
        Post::factory()->count(2)->create([
            'body' => '<p>Renk uyumunu test ederken gün ışığında bakmak, yapay ışıktaki yanıltıcı tonları eler.</p>',
        ]);

        $this->artisan('content:audit-quality')
            ->expectsOutputToContain('Yazı kalite denetimi')
            ->assertSuccessful();
    }
}
