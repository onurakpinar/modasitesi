<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertPostRequest;
use App\Models\Author;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use App\Support\PostImageUploader;
use App\Support\Seo\PostSlugRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use RuntimeException;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->with(['author', 'category'])
            ->latest()
            ->paginate(20);

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create', $this->formData());
    }

    public function store(UpsertPostRequest $request, HtmlSanitizer $sanitizer, PostImageUploader $uploader): RedirectResponse
    {
        $validated = $this->prepareAttributes($request, $sanitizer);

        if ($request->hasFile('cover_image')) {
            $uploaded = $this->uploadCoverImage($request, $uploader);

            if ($uploaded instanceof RedirectResponse) {
                return $uploaded;
            }

            $validated['cover_image'] = $uploaded['path'];
            $validated['cover_image_fallback'] = $uploaded['fallback'];
            $validated['cover_image_width'] = $uploaded['width'];
            $validated['cover_image_height'] = $uploaded['height'];
        }

        $validated['slug'] = $validated['slug'] ?? Post::generateUniqueSlug($validated['title']);

        $post = Post::query()->create($validated);
        $post->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Yazı oluşturuldu.');
    }

    public function edit(Post $post): View
    {
        $post->load(['tags', 'revisions.user']);

        return view('admin.posts.edit', array_merge($this->formData(), [
            'post' => $post,
            'previewUrl' => URL::temporarySignedRoute(
                'admin.posts.preview',
                now()->addDays(7),
                ['post' => $post->id]
            ),
        ]));
    }

    public function update(UpsertPostRequest $request, Post $post, HtmlSanitizer $sanitizer, PostImageUploader $uploader, PostSlugRedirector $redirector): RedirectResponse
    {
        PostRevision::query()->create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'created_at' => now(),
        ]);

        $validated = $this->prepareAttributes($request, $sanitizer, $post);
        $bodyChanged = array_key_exists('body', $validated) && $validated['body'] !== $post->body;

        if ($request->hasFile('cover_image')) {
            $uploader->delete($post->cover_image, $post->cover_image_fallback);
            $uploaded = $this->uploadCoverImage($request, $uploader);

            if ($uploaded instanceof RedirectResponse) {
                return $uploaded;
            }

            $validated['cover_image'] = $uploaded['path'];
            $validated['cover_image_fallback'] = $uploaded['fallback'];
            $validated['cover_image_width'] = $uploaded['width'];
            $validated['cover_image_height'] = $uploaded['height'];
        }

        $validated['slug'] = $validated['slug'] ?? Post::generateUniqueSlug($validated['title'], $post->id);
        $oldSlug = $post->slug;

        if ($bodyChanged) {
            $validated['content_updated_at'] = now();
        }

        $post->update($validated);

        if ($oldSlug !== $validated['slug']) {
            $redirector->record($post->fresh(), $oldSlug);
        }
        $post->tags()->sync($request->input('tags', []));

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Yazı güncellendi.');
    }

    public function destroy(Post $post, PostImageUploader $uploader): RedirectResponse
    {
        $uploader->delete($post->cover_image, $post->cover_image_fallback);
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Yazı silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'authors' => Author::query()->where('is_active', true)->orderBy('name')->get(),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'tags' => Tag::query()->orderBy('name')->get(),
            'statuses' => PostStatus::cases(),
        ];
    }

    /**
     * @return array{path: string, fallback: string|null}|RedirectResponse
     */
    private function uploadCoverImage(UpsertPostRequest $request, PostImageUploader $uploader): array|RedirectResponse
    {
        try {
            return $uploader->upload($request->file('cover_image'));
        } catch (RuntimeException $exception) {
            return back()
                ->withErrors(['cover_image' => $exception->getMessage()])
                ->withInput();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function prepareAttributes(UpsertPostRequest $request, HtmlSanitizer $sanitizer, ?Post $post = null): array
    {
        $validated = $request->validated();
        $validated['body'] = $sanitizer->sanitize($validated['body'] ?? '');
        $validated['sources'] = filled($validated['sources'] ?? null)
            ? $sanitizer->sanitize($validated['sources'])
            : null;
        $validated['is_featured'] = $request->boolean('is_featured');

        $status = $validated['status'] instanceof PostStatus
            ? $validated['status']
            : PostStatus::from($validated['status']);

        $validated['status'] = $status;

        if ($status === PostStatus::Published && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if ($request->boolean('originality_confirmed')) {
            $validated['originality_confirmed_at'] = $post?->originality_confirmed_at ?? now();
        }

        if ($request->boolean('human_reviewed')) {
            $validated['human_reviewed_at'] = $post?->human_reviewed_at ?? now();
        }

        unset($validated['originality_confirmed'], $validated['human_reviewed'], $validated['tags']);

        return $validated;
    }
}
