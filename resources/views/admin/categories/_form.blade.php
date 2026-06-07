<div>
    <label class="block text-sm font-medium text-stone-700">Ad</label>
    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $category->slug ?? '') }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm" placeholder="Boş bırakılırsa otomatik oluşturulur">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Açıklama</label>
    <textarea name="description" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('description', $category->description ?? '') }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Sıra</label>
    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
    Aktif
</label>
