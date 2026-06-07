<div>
    <label class="block text-sm font-medium text-stone-700">Ad</label>
    <input type="text" name="name" value="{{ old('name', $tag->name ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $tag->slug ?? '') }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
