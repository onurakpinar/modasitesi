<?php

namespace App\Models;

use App\Concerns\GeneratesSlug;
use App\Enums\PageStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'body', 'meta_title', 'meta_description', 'status'])]
class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use GeneratesSlug, HasFactory;

    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', PageStatus::Published);
    }
}
