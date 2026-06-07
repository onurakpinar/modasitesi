<div>
    <label class="block text-sm font-medium text-stone-700">Ad</label>
    <input type="text" name="name" value="{{ old('name', $author->name ?? '') }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $author->slug ?? '') }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Kısa Biyografi</label>
    <textarea name="short_bio" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('short_bio', $author->short_bio ?? '') }}</textarea>
    <p class="mt-1 text-xs text-stone-500">Kart ve meta açıklamalarda kullanılır (1–2 cümle).</p>
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Biyografi</label>
    <textarea name="bio" rows="6" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('bio', $author->bio ?? '') }}</textarea>
    <p class="mt-1 text-xs text-stone-500">Yazar profil sayfasında gösterilir. {{-- TODO: Biyografi metinleri editör tarafından doldurulacak. --}}</p>
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Uzmanlık alanı</label>
    <input type="text" name="expertise" value="{{ old('expertise', $author->expertise ?? '') }}" placeholder="Örn. Sürdürülebilir moda, erkek stili" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">E-posta</label>
    <input type="email" name="email" value="{{ old('email', $author->email ?? '') }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>
<div>
    <label class="block text-sm font-medium text-stone-700">Profil Görseli</label>
    <input type="file" name="profile_image" accept="image/*" class="mt-1 w-full text-sm">
    @if (!empty($author?->profile_image))
        <p class="mt-2 text-xs text-stone-500">Mevcut: {{ $author->profile_image }}</p>
    @endif
</div>
<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $author->is_active ?? true))>
    Aktif
</label>
