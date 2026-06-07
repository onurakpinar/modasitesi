<?php

namespace Tests\Feature;

use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    public function test_olmayan_sayfa_404_doner(): void
    {
        $response = $this->get('/var-olmayan-sayfa');

        $response->assertNotFound();
        $response->assertSee('Sayfa bulunamadı', false);
    }
}
