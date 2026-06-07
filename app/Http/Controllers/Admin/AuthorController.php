<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Support\SecureImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(): View
    {
        $authors = Author::query()->orderBy('name')->paginate(20);

        return view('admin.authors.index', compact('authors'));
    }

    public function create(): View
    {
        return view('admin.authors.create');
    }

    public function store(Request $request, SecureImageUploader $uploader): RedirectResponse
    {
        $validated = $this->validateAuthor($request);

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $this->uploadProfileImage($request, $uploader);
        }

        Author::query()->create($validated);

        return redirect()->route('admin.authors.index')->with('success', 'Yazar oluşturuldu.');
    }

    public function edit(Author $author): View
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author, SecureImageUploader $uploader): RedirectResponse
    {
        $validated = $this->validateAuthor($request, $author->id);

        if ($request->hasFile('profile_image')) {
            if ($author->profile_image) {
                Storage::disk('public')->delete($author->profile_image);
            }
            $validated['profile_image'] = $this->uploadProfileImage($request, $uploader);
        }

        $author->update($validated);

        return redirect()->route('admin.authors.index')->with('success', 'Yazar güncellendi.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->posts()->exists()) {
            return back()->with('error', 'Bu yazara bağlı yazılar var.');
        }

        if ($author->profile_image) {
            Storage::disk('public')->delete($author->profile_image);
        }

        $author->delete();

        return redirect()->route('admin.authors.index')->with('success', 'Yazar silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAuthor(Request $request, ?int $authorId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:authors,slug,'.($authorId ?? 'NULL')],
            'short_bio' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'profile_image' => ['nullable', File::image(allowSvg: false)->max(2048)],
            'is_active' => ['boolean'],
        ], [], [
            'name' => 'ad',
            'short_bio' => 'kısa biyografi',
            'profile_image' => 'profil görseli',
        ]);

        $validated['slug'] = $validated['slug'] ?? Author::generateUniqueSlug($validated['name'], $authorId);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function uploadProfileImage(Request $request, SecureImageUploader $uploader): string
    {
        return $uploader->upload($request->file('profile_image'), 'authors', 800);
    }
}
