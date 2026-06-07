<?php

namespace Database\Factories;

use App\Enums\BriefStatus;
use App\Enums\BriefTopicCategory;
use App\Models\ContentBrief;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentBrief>
 */
class ContentBriefFactory extends Factory
{
    protected $model = ContentBrief::class;

    public function definition(): array
    {
        return [
            'title_suggestion' => fake()->unique()->sentence(6),
            'topic_category' => fake()->randomElement(BriefTopicCategory::cases()),
            'target_audience' => 'Moda okurları',
            'search_intent' => 'bilgilendirici',
            'content_summary' => fake()->paragraph(),
            'subheadings' => "- Giriş\n- Temel noktalar\n- Sonuç",
            'suggested_internal_links' => "/yazilar\n/kategori/stil-rehberi",
            'cover_image_note' => 'Özgün stüdyo çekimi veya lisanslı görsel',
            'planned_publish_date' => now()->addWeeks(2)->toDateString(),
            'status' => BriefStatus::Idea,
            'notes' => null,
        ];
    }
}
