<?php

namespace Tests\Feature\Public;

use App\Models\Category;
use App\Models\Post;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_public_header_merkezi_navigasyon_state_kullanir(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('x-data="siteHeader()"', false)
            ->assertSee('data-site-header-bar', false)
            ->assertSee('x-ref="menuButton"', false)
            ->assertSee('id="site-navigation-mobile"', false)
            ->assertSee('aria-controls="site-navigation-mobile"', false)
            ->assertSee('x-cloak', false);
    }

    public function test_footer_accordion_merkezi_state_kullanir(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('x-data="footerAccordion()"', false)
            ->assertSee('toggleSection(', false)
            ->assertSee('footer-section-pages', false)
            ->assertSee('aria-controls="footer-section-explore"', false);
    }

    public function test_kategori_dropdown_tiklama_ile_acilir(): void
    {
        $category = Category::factory()->create([
            'name' => 'Kadın Modası',
            'slug' => 'kadin-modasi',
            'is_active' => true,
        ]);

        Post::factory()->published()->create(['category_id' => $category->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('toggleCategories()', false)
            ->assertSee('aria-controls="site-navigation-categories"', false)
            ->assertSee(route('categories.show', 'kadin-modasi'), false);
    }

    public function test_mobil_kategori_accordion_yapisı_mevcut(): void
    {
        $category = Category::factory()->create([
            'name' => 'Erkek Modası',
            'slug' => 'erkek-modasi',
            'is_active' => true,
        ]);

        Post::factory()->published()->create(['category_id' => $category->id]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('toggleMobileAccordion(\'categories\')', false)
            ->assertSee('id="site-navigation-mobile-categories"', false);
    }

    public function test_responsive_temel_siniflar_public_sayfalarda_mevcut(): void
    {
        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('max-w-6xl px-4', $html);
        $this->assertStringContainsString('lg:hidden', $html);
        $this->assertStringNotContainsString('w-screen', $html);
    }
}
