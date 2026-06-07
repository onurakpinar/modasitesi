<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()->latest()->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create', ['statuses' => PageStatus::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request);

        Page::query()->create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Sayfa oluşturuldu.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'statuses' => PageStatus::cases(),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validatePage($request, $page->id));

        return redirect()->route('admin.pages.index')->with('success', 'Sayfa güncellendi.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Sayfa silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request, ?int $pageId = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:pages,slug,'.($pageId ?? 'NULL')],
            'body' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'status' => ['required', 'in:'.implode(',', array_column(PageStatus::cases(), 'value'))],
        ], [], [
            'title' => 'başlık',
            'body' => 'içerik',
            'status' => 'durum',
        ]);

        $validated['slug'] = $validated['slug'] ?? Page::generateUniqueSlug($validated['title'], $pageId);

        return $validated;
    }
}
