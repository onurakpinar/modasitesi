<?php

namespace App\Models;

use App\Concerns\GeneratesSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'short_bio', 'profile_image', 'email', 'is_active'])]
class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use GeneratesSlug, HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
