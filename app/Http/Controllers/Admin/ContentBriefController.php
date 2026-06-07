<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BriefStatus;
use App\Enums\BriefTopicCategory;
use App\Http\Controllers\Controller;
use App\Models\ContentBrief;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentBriefController extends Controller
{
    public function index(Request $request): View
    {
        $statusFilter = $request->string('durum')->toString();
        $categoryFilter = $request->string('kategori')->toString();

        $briefs = ContentBrief::query()
            ->with('assignedEditor')
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->when($categoryFilter !== '', fn ($query) => $query->where('topic_category', $categoryFilter))
            ->orderBy('planned_publish_date')
            ->orderBy('title_suggestion')
            ->paginate(20)
            ->withQueryString();

        return view('admin.content-briefs.index', [
            'briefs' => $briefs,
            'statusFilter' => $statusFilter,
            'categoryFilter' => $categoryFilter,
        ]);
    }

    public function create(): View
    {
        return view('admin.content-briefs.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        ContentBrief::query()->create($this->validated($request));

        return redirect()->route('admin.content-briefs.index')->with('success', 'İçerik briefi oluşturuldu.');
    }

    public function edit(ContentBrief $contentBrief): View
    {
        return view('admin.content-briefs.edit', array_merge($this->formData(), [
            'brief' => $contentBrief,
        ]));
    }

    public function update(Request $request, ContentBrief $contentBrief): RedirectResponse
    {
        $contentBrief->update($this->validated($request));

        return redirect()->route('admin.content-briefs.index')->with('success', 'İçerik briefi güncellendi.');
    }

    public function destroy(ContentBrief $contentBrief): RedirectResponse
    {
        $contentBrief->delete();

        return redirect()->route('admin.content-briefs.index')->with('success', 'İçerik briefi silindi.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'editors' => User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'statuses' => BriefStatus::cases(),
            'topicCategories' => BriefTopicCategory::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title_suggestion' => ['required', 'string', 'max:255'],
            'topic_category' => ['required', 'string'],
            'target_audience' => ['required', 'string', 'max:500'],
            'search_intent' => ['required', 'string', 'max:120'],
            'content_summary' => ['required', 'string', 'max:5000'],
            'subheadings' => ['required', 'string', 'max:5000'],
            'suggested_internal_links' => ['nullable', 'string', 'max:2000'],
            'cover_image_note' => ['nullable', 'string', 'max:1000'],
            'planned_publish_date' => ['nullable', 'date'],
            'status' => ['required', 'string'],
            'assigned_editor_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [], [
            'title_suggestion' => 'başlık önerisi',
            'topic_category' => 'kategori',
            'target_audience' => 'hedef okuyucu',
            'search_intent' => 'arama niyeti',
            'content_summary' => 'içerik özeti',
            'subheadings' => 'alt başlıklar',
            'suggested_internal_links' => 'önerilen iç linkler',
            'cover_image_note' => 'kapak görseli notu',
            'planned_publish_date' => 'planlanan yayın tarihi',
            'status' => 'durum',
            'assigned_editor_id' => 'sorumlu editör',
            'notes' => 'notlar',
        ]);

        $validated['assigned_editor_id'] = filled($validated['assigned_editor_id'] ?? null)
            ? $validated['assigned_editor_id']
            : null;

        return $validated;
    }
}
