<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kadın Modası', 'sort_order' => 1],
            ['name' => 'Erkek Modası', 'sort_order' => 2],
            ['name' => 'Stil Rehberi', 'sort_order' => 3],
            ['name' => 'Aksesuar', 'sort_order' => 4],
            ['name' => 'Sezon Trendleri', 'sort_order' => 5],
            ['name' => 'Sürdürülebilir Moda', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Category::generateUniqueSlug($category['name']),
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }
    }
}
