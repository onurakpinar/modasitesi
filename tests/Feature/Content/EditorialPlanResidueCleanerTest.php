<?php

namespace Tests\Feature\Content;

use App\Models\Post;
use App\Support\Content\EditorialPlanResidueCleaner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialPlanResidueCleanerTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_ozeti_blogunu_ve_sablon_paragraflari_kaldirir(): void
    {
        $body = <<<'HTML'
<h2>Gerçek içerik</h2>
<p>Özgün paragraf burada kalır.</p>
<h2>Şapka ve Bere — plan özeti</h2>
<p>Özet metni. Hedef okur: okurlar; kategori: accessories.</p>
<h3>Şapka türleri</h3>
<p>«Şapka ve Bere» — 1. adım «Şapka türleri»: detay (okurlar, accessories)</p>
<p>«Şapka türleri» maddesini uygularken «Şapka ve Bere» kapsamında envanter notu tutun; eksik kaldıysa yalnızca bu başlık için alım planlayın.</p>
HTML;

        $result = (new EditorialPlanResidueCleaner)->clean($body);

        $this->assertStringContainsString('Özgün paragraf', $result['cleaned']);
        $this->assertStringNotContainsString('plan özeti', $result['cleaned']);
        $this->assertStringNotContainsString('Hedef okur:', $result['cleaned']);
        $this->assertStringNotContainsString('envanter notu', $result['cleaned']);
        $this->assertContains('plan_ozeti_blogu', $result['matches']);
    }

    public function test_clean_plan_residue_komutu_veritabanini_gunceller(): void
    {
        Post::factory()->create([
            'body' => '<p>İçerik.</p><h2>Başlık — plan özeti</h2><p>Hedef okur: x; kategori: style-guide.</p>',
        ]);

        $this->artisan('content:clean-plan-residue')
            ->expectsOutputToContain('Güncellenen')
            ->assertSuccessful();

        $post = Post::query()->first();
        $this->assertStringNotContainsString('plan özeti', $post->body);
        $this->assertSame('İçerik.', strip_tags($post->body));
    }
}
