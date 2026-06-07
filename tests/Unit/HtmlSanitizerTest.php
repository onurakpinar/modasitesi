<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_script_ve_iframe_temizlenir(): void
    {
        $sanitizer = new HtmlSanitizer;

        $result = $sanitizer->sanitize('<p>Metin</p><script>alert(1)</script><iframe src="x"></iframe>');

        $this->assertStringContainsString('<p>Metin</p>', $result);
        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('<iframe', $result);
    }

    public function test_h1_etiketi_h2_olarak_donusturulur(): void
    {
        $sanitizer = new HtmlSanitizer;

        $result = $sanitizer->sanitize('<h1>Başlık</h1><h2>Alt</h2>');

        $this->assertStringNotContainsString('<h1', $result);
        $this->assertStringContainsString('<h2>Başlık</h2>', $result);
        $this->assertStringContainsString('<h2>Alt</h2>', $result);
    }

    public function test_inline_event_handler_kaldirilir(): void
    {
        $sanitizer = new HtmlSanitizer;

        $result = $sanitizer->sanitize('<p onclick="alert(1)">Metin</p>');

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringContainsString('Metin', $result);
    }
}
