<div class="space-y-5">
    <div>
        <label for="title_suggestion" class="block text-sm font-medium text-stone-700">Başlık önerisi</label>
        <input type="text" name="title_suggestion" id="title_suggestion" value="{{ old('title_suggestion', $brief->title_suggestion ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="topic_category" class="block text-sm font-medium text-stone-700">Kategori</label>
            <select name="topic_category" id="topic_category" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
                @foreach ($topicCategories as $category)
                    <option value="{{ $category->value }}" @selected(old('topic_category', $brief->topic_category->value ?? '') === $category->value)>
                        {{ $category->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-stone-700">Durum</label>
            <select name="status" id="status" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $brief->status->value ?? 'idea') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="target_audience" class="block text-sm font-medium text-stone-700">Hedef okuyucu</label>
            <input type="text" name="target_audience" id="target_audience" value="{{ old('target_audience', $brief->target_audience ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="search_intent" class="block text-sm font-medium text-stone-700">Arama niyeti</label>
            <input type="text" name="search_intent" id="search_intent" value="{{ old('search_intent', $brief->search_intent ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm" placeholder="bilgilendirici, karşılaştırma, rehber">
        </div>
    </div>

    <div>
        <label for="content_summary" class="block text-sm font-medium text-stone-700">İçerik özeti</label>
        <textarea name="content_summary" id="content_summary" rows="4" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('content_summary', $brief->content_summary ?? '') }}</textarea>
    </div>

    <div>
        <label for="subheadings" class="block text-sm font-medium text-stone-700">Ele alınması gereken alt başlıklar</label>
        <textarea name="subheadings" id="subheadings" rows="6" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm" placeholder="Her satıra bir alt başlık veya madde işareti">{{ old('subheadings', $brief->subheadings ?? '') }}</textarea>
    </div>

    <div>
        <label for="suggested_internal_links" class="block text-sm font-medium text-stone-700">Önerilen iç linkler</label>
        <textarea name="suggested_internal_links" id="suggested_internal_links" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm" placeholder="Her satıra bir yol veya açıklama">{{ old('suggested_internal_links', $brief->suggested_internal_links ?? '') }}</textarea>
    </div>

    <div>
        <label for="cover_image_note" class="block text-sm font-medium text-stone-700">Kapak görseli notu</label>
        <textarea name="cover_image_note" id="cover_image_note" rows="2" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('cover_image_note', $brief->cover_image_note ?? '') }}</textarea>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="planned_publish_date" class="block text-sm font-medium text-stone-700">Planlanan yayın tarihi</label>
            <input type="date" name="planned_publish_date" id="planned_publish_date" value="{{ old('planned_publish_date', isset($brief) && $brief->planned_publish_date ? $brief->planned_publish_date->format('Y-m-d') : '') }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="assigned_editor_id" class="block text-sm font-medium text-stone-700">Sorumlu editör</label>
            <select name="assigned_editor_id" id="assigned_editor_id" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
                <option value="">Atanmadı</option>
                @foreach ($editors as $editor)
                    <option value="{{ $editor->id }}" @selected((string) old('assigned_editor_id', $brief->assigned_editor_id ?? '') === (string) $editor->id)>
                        {{ $editor->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-stone-700">Notlar</label>
        <textarea name="notes" id="notes" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm" placeholder="Editoryal uyarılar, kaçınılacak konular">{{ old('notes', $brief->notes ?? '') }}</textarea>
    </div>
</div>
