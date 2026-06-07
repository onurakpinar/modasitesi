<?php

namespace App\Models;

use App\Enums\BriefStatus;
use App\Enums\BriefTopicCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title_suggestion', 'topic_category', 'target_audience', 'search_intent',
    'content_summary', 'subheadings', 'suggested_internal_links', 'cover_image_note',
    'planned_publish_date', 'status', 'assigned_editor_id', 'notes',
])]
class ContentBrief extends Model
{
    /** @use HasFactory<\Database\Factories\ContentBriefFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'topic_category' => BriefTopicCategory::class,
            'status' => BriefStatus::class,
            'planned_publish_date' => 'date',
        ];
    }

    public function assignedEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_editor_id');
    }

    public function scopeStatus($query, BriefStatus $status)
    {
        return $query->where('status', $status);
    }
}
