<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class PostPreviewController extends Controller
{
    public function show(Post $post): View
    {
        $post->load(['author', 'category', 'tags']);

        return view('admin.posts.preview', compact('post'));
    }
}
