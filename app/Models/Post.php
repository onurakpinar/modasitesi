<?php

namespace App\Models;

use App\Concerns\GeneratesSlug;
use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'author_id', 'category_id', 'title', 'slug', 'excerpt', 'body', 'sources',
    'cover_image', 'cover_image_fallback', 'cover_image_width', 'cover_image_height', 'cover_image_alt', 'status', 'published_at', 'content_updated_at', 'is_featured',
    'meta_title', 'meta_description', 'canonical_url',
    'originality_confirmed_at', 'human_reviewed_at',
])]
class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use GeneratesSlug, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
            'content_updated_at' => 'datetime',
            'is_featured' => 'boolean',
            'originality_confirmed_at' => 'datetime',
            'human_reviewed_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PostRevision::class)->latest('created_at');
    }

    public function scopePublished($query)
    {
        return $query->where('status', PostStatus::Published);
    }

    public function scopePubliclyVisible($query)
    {
        return $query->published()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PostStatus::Draft);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
