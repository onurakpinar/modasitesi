@php
    $quality = isset($post) ? app(\App\Support\PostQualityChecker::class)->analyze($post) : null;
@endphp

@if ($errors->isNotEmpty())
    <div class="rounded border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <p class="font-medium">Lütfen aşağıdaki hataları düzeltin:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-stone-700">Yazar</label>
        <select name="author_id" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('author_id') border-rose-500 @enderror">
            <option value="">Seçin</option>
            @foreach ($authors as $author)
                <option value="{{ $author->id }}" @selected(old('author_id', $post?->author_id) == $author->id)>{{ $author->name }}</option>
            @endforeach
        </select>
        @error('author_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Kategori</label>
        <select name="category_id" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('category_id') border-rose-500 @enderror">
            <option value="">Seçin</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $post?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-stone-700">Başlık <span class="text-stone-400">(yayın için min. 35 karakter)</span></label>
    <input type="text" name="title" value="{{ old('title', $post?->title) }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('title') border-rose-500 @enderror">
    @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-stone-700">Slug</label>
    <input type="text" name="slug" value="{{ old('slug', $post?->slug) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
</div>

<div>
    <label class="block text-sm font-medium text-stone-700">Özet <span class="text-stone-400">(yayın için 140–260 karakter)</span></label>
    <textarea name="excerpt" rows="3" maxlength="1000" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('excerpt') border-rose-500 @enderror">{{ old('excerpt', $post?->excerpt) }}</textarea>
    @error('excerpt')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-medium text-stone-700">İçerik</label>
    <p class="mt-1 text-xs text-stone-500">Sayfa başlığı H1 olarak kullanılır. İçerikte yalnızca H2 ve H3 kullanın. Yayın için en az 700 kelime gerekir.</p>
    <input id="body-input" type="hidden" name="body" value="{{ old('body', $post?->body) }}">
    <trix-editor input="body-input" class="trix-content mt-2 min-h-[320px] border border-stone-300 bg-white @error('body') border-rose-500 @enderror"></trix-editor>
    @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    @if ($quality)
        <p class="mt-2 text-xs text-stone-500">Kelime sayısı: {{ $quality['word_count'] }}</p>
    @endif
</div>

<div>
    <label class="block text-sm font-medium text-stone-700">Kaynaklar <span class="text-stone-400">(opsiyonel)</span></label>
    <p class="mt-1 text-xs text-stone-500">Doldurulursa yazı sonunda “Kaynaklar” bölümü görünür. Liste veya kısa referanslar yazabilirsiniz.</p>
    <textarea name="sources" rows="4" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('sources') border-rose-500 @enderror">{{ old('sources', $post?->sources) }}</textarea>
    @error('sources')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>

<div class="rounded border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
    <p class="font-medium">Görsel kullanım uyarısı</p>
    <p class="mt-1">Yalnızca kullanım hakkına sahip olduğunuz görselleri yükleyin. Lisansı belirsiz veya izinsiz görseller kullanmayın.</p>
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-stone-700">Kapak Görseli</label>
        <p class="mt-1 text-xs text-stone-500">JPG, PNG veya WebP · Maks. 5 MB · Otomatik optimize edilir</p>
        @if ($post?->cover_image)
            <div class="mt-2">
                <img src="{{ \App\Support\MediaUrl::public($post->cover_image, $post->cover_image_fallback) }}" alt="{{ $post->cover_image_alt ?: $post->title }}" class="max-h-40 rounded border border-stone-200 object-cover">
            </div>
        @endif
        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 w-full text-sm @error('cover_image') border-rose-500 @enderror">
        @error('cover_image')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Kapak Görsel Alt Metni <span class="text-rose-600">*</span></label>
        <input type="text" name="cover_image_alt" value="{{ old('cover_image_alt', $post?->cover_image_alt) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('cover_image_alt') border-rose-500 @enderror">
        @error('cover_image_alt')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-stone-700">Durum</label>
        <select name="status" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $post?->status?->value ?? 'draft') == $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Yayın Tarihi</label>
        <input type="datetime-local" name="published_at" value="{{ old('published_at', $post?->published_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
        <p class="mt-1 text-xs text-stone-500">Zamanlanmış yayın için gelecek bir tarih seçin.</p>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-stone-700">Etiketler</label>
    <select name="tags[]" multiple class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm" size="5">
        @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @selected(collect(old('tags', $post?->tags?->pluck('id') ?? []))->contains($tag->id))>{{ $tag->name }}</option>
        @endforeach
    </select>
</div>

<label class="flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post?->is_featured ?? false))>
    Öne çıkan yazı
</label>

<fieldset class="space-y-3 border border-stone-200 p-4">
    <legend class="px-1 text-sm font-medium text-stone-900">SEO</legend>
    <div>
        <label class="block text-sm font-medium text-stone-700">Meta Başlık</label>
        <input type="text" name="meta_title" value="{{ old('meta_title', $post?->meta_title) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('meta_title') border-rose-500 @enderror">
        @error('meta_title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Meta Açıklama <span class="text-stone-400">(120–160 karakter)</span></label>
        <textarea name="meta_description" rows="2" maxlength="500" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm @error('meta_description') border-rose-500 @enderror">{{ old('meta_description', $post?->meta_description) }}</textarea>
        @error('meta_description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Canonical URL</label>
        <input type="url" name="canonical_url" value="{{ old('canonical_url', $post?->canonical_url) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
    </div>
</fieldset>

<fieldset class="space-y-3 border border-stone-200 p-4">
    <legend class="px-1 text-sm font-medium text-stone-900">Yayın öncesi onaylar</legend>
    <label class="flex items-start gap-2 text-sm">
        <input type="checkbox" name="originality_confirmed" value="1" @checked(old('originality_confirmed', (bool) $post?->originality_confirmed_at)) @disabled($post?->originality_confirmed_at)>
        <span>Bu içerik özgündür ve başka bir siteden kopyalanmamıştır.</span>
    </label>
    @error('originality_confirmed')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
    @if ($post?->originality_confirmed_at)
        <p class="text-xs text-stone-500">Onaylandı: {{ $post->originality_confirmed_at->format('d.m.Y H:i') }}</p>
    @endif

    <label class="flex items-start gap-2 text-sm">
        <input type="checkbox" name="human_reviewed" value="1" @checked(old('human_reviewed', (bool) $post?->human_reviewed_at)) @disabled($post?->human_reviewed_at)>
        <span>İçerik insan tarafından kontrol edildi.</span>
    </label>
    @error('human_reviewed')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
    @if ($post?->human_reviewed_at)
        <p class="text-xs text-stone-500">Onaylandı: {{ $post->human_reviewed_at->format('d.m.Y H:i') }}</p>
    @endif
</fieldset>

@if ($quality && ! $quality['ready'])
    <div class="rounded border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
        <p class="font-medium">Yayın kalite kontrolü</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($quality['issues'] as $issue)
                <li>{{ $issue }}</li>
            @endforeach
        </ul>
    </div>
@endif

@push('head')
    @vite('resources/js/admin-editor.js')
    <style>
        trix-toolbar .trix-button-group--file-tools,
        trix-toolbar .trix-button--icon-heading-1 {
            display: none !important;
        }
        .trix-content h1 { display: none; }
    </style>
@endpush
