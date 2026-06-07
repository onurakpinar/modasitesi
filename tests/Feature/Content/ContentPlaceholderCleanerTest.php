<?php

namespace Tests\Feature\Content;

use App\Support\Content\ContentPlaceholderCleaner;
use Tests\TestCase;

class ContentPlaceholderCleanerTest extends TestCase
{
    public function test_editoryal_not_ve_placeholder_kalintilarini_temizler(): void
    {
        $cleaner = new ContentPlaceholderCleaner;

        $result = $cleaner->clean(
            '<p>Metin başlıyor. Editoryal not: Liste kişiselleştirme notu eklenmeli. Devam ediyor.</p>'
            .'<p>[SITE_ADI] için örnek.</p>'
        );

        $this->assertStringNotContainsString('Editoryal not:', $result['cleaned']);
        $this->assertStringNotContainsString('[SITE_ADI]', $result['cleaned']);
        $this->assertStringNotContainsString('eklenmeli', $result['cleaned']);
        $this->assertNotEmpty($result['matches']);
    }
}
