<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'ad',
            'slug' => 'slug',
            'description' => 'açıklama',
            'sort_order' => 'sıra',
        ]);

        $validated['slug'] = $validated['slug'] ?? Category::generateUniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Category::query()->create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori oluşturuldu.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,'.$category->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'ad',
            'slug' => 'slug',
        ]);

        $validated['slug'] = $validated['slug'] ?? Category::generateUniqueSlug($validated['name'], $category->id);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:categories,id'],
        ], [], [
            'order' => 'sıralama',
        ]);

        foreach ($validated['order'] as $position => $categoryId) {
            Category::query()->whereKey($categoryId)->update(['sort_order' => $position]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Kategori sıralaması güncellendi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Bu kategoriye bağlı yazılar var. Önce yazıları taşıyın veya silin.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori silindi.');
    }
}
