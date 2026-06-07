<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostRevision;
use Illuminate\Http\RedirectResponse;

class PostRevisionController extends Controller
{
    public function restore(Post $post, PostRevision $revision): RedirectResponse
    {
        abort_unless($revision->post_id === $post->id, 404);

        PostRevision::query()->create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'created_at' => now(),
        ]);

        $post->update([
            'title' => $revision->title,
            'excerpt' => $revision->excerpt,
            'body' => $revision->body,
        ]);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Önceki sürüm geri yüklendi.');
    }
}
