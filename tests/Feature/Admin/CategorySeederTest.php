<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_moda_kategorileri_olusturulur(): void
    {
        $this->seed(CategorySeeder::class);

        $this->assertSame(6, Category::query()->count());
        $this->assertDatabaseHas('categories', ['name' => 'Kadın Modası']);
        $this->assertDatabaseHas('categories', ['name' => 'Sürdürülebilir Moda']);
    }
}
