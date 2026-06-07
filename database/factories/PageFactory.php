<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ucfirst($title),
            'slug' => Page::generateUniqueSlug($title),
            'body' => fake()->paragraphs(2, true),
            'status' => PageStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => PageStatus::Draft]);
    }
}
