<div>
    <label class="block text-sm font-medium text-stone-700">Başlık</label>
    <input type="text" name="title" value="{{ old('title', $page?->title) }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $page?->slug) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">İçerik</label>
    <textarea name="body" rows="12" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm font-mono">{{ old('body', $page?->body) }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Durum</label>
    <select name="status" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
        @foreach ($statuses as $status)
            <option value="{{ $status->value }}" @selected(old('status', $page?->status?->value ?? 'draft') == $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Meta Başlık</label>
    <input type="text" name="meta_title" value="{{ old('meta_title', $page?->meta_title) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Meta Açıklama</label>
    <textarea name="meta_description" rows="2" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('meta_description', $page?->meta_description) }}</textarea>
</div>
