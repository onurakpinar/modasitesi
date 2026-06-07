<?php

namespace Tests\Support;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\UploadedFile;

trait PublishablePostPayload
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function publishablePayload(Author $author, Category $category, array $overrides = []): array
    {
        return array_merge([
            'author_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Kaliteli Moda Yazısı Başlığı Yeterince Uzundur',
            'excerpt' => 'Bu özet metni, yayın kalitesi standartlarını karşılayacak şekilde hazırlanmış özgün bir moda yazısı girişidir ve okura kapsamı net biçimde aktarır.',
            'body' => '<h2>Bölüm</h2><p>'.implode(' ', array_fill(0, 700, 'moda')).'</p>',
            'status' => PostStatus::Published->value,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 900),
            'cover_image_alt' => 'Kış modasında katmanlı giyim örneği',
            'meta_title' => 'Moda Yazısı Meta Başlığı',
            'meta_description' => 'Moda ve stil üzerine özgün bir değerlendirme. Gardırop planlaması, trend analizi ve sürdürülebilir seçimler hakkında kapsamlı rehber sunar.',
            'originality_confirmed' => '1',
            'human_reviewed' => '1',
        ], $overrides);
    }

    protected function bodyWithWordCount(int $count): string
    {
        return '<p>'.implode(' ', array_fill(0, $count, 'kelime')).'</p>';
    }
}
