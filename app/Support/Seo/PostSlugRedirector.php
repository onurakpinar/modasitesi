<?php

namespace App\Support\Seo;

use App\Models\Post;
use App\Models\PostSlugRedirect;

class PostSlugRedirector
{
    public function record(Post $post, string $oldSlug): void
    {
        $oldSlug = trim($oldSlug);
        $newSlug = $post->slug;

        if ($oldSlug === '' || $oldSlug === $newSlug) {
            return;
        }

        if (Post::query()->where('slug', $oldSlug)->whereKeyNot($post->id)->exists()) {
            return;
        }

        PostSlugRedirect::query()->where('old_slug', $newSlug)->delete();

        PostSlugRedirect::query()->updateOrCreate(
            ['old_slug' => $oldSlug],
            ['post_id' => $post->id, 'created_at' => now()]
        );
    }

    public function resolvePostId(string $slug): ?int
    {
        $redirect = PostSlugRedirect::query()->where('old_slug', $slug)->first();

        return $redirect?->post_id;
    }
}
