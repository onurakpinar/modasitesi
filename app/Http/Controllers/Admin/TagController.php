<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::query()->orderBy('name')->paginate(20);

        return view('admin.tags.index', compact('tags'));
    }

    public function create(): View
    {
        return view('admin.tags.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tags,slug'],
        ], [], ['name' => 'ad', 'slug' => 'slug']);

        $validated['slug'] = $validated['slug'] ?? Tag::generateUniqueSlug($validated['name']);

        Tag::query()->create($validated);

        return redirect()->route('admin.tags.index')->with('success', 'Etiket oluşturuldu.');
    }

    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tags,slug,'.$tag->id],
        ], [], ['name' => 'ad', 'slug' => 'slug']);

        $validated['slug'] = $validated['slug'] ?? Tag::generateUniqueSlug($validated['name'], $tag->id);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')->with('success', 'Etiket güncellendi.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->posts()->detach();
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Etiket silindi.');
    }
}
