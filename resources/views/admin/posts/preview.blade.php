<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ön izleme: {{ $post->title }}</title>
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <div class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm text-amber-900">
        Bu sayfa yalnızca ön izlemedir. Arama motorlarına kapalıdır.
    </div>

    <x-site-container class="max-w-4xl py-10 sm:py-14">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent-700">Ön izleme · {{ $post->status->label() }}</p>

        <h1 class="mt-4 font-display text-4xl leading-tight text-stone-900 sm:text-5xl">{{ $post->title }}</h1>

        <div class="mt-6 flex flex-wrap gap-2 text-sm text-stone-500">
            @if ($post->author)<span>{{ $post->author->name }}</span>@endif
            @if ($post->category)<span>· {{ $post->category->name }}</span>@endif
            @if ($post->published_at)<span>· {{ $post->published_at->format('d.m.Y H:i') }}</span>@endif
        </div>

        @if ($post->cover_image)
            <div class="mt-8 overflow-hidden">
                <x-cover-image
                    :src="$post->cover_image"
                    :fallback="$post->cover_image_fallback"
                    :alt="$post->cover_image_alt ?: $post->title"
                    :width="$post->cover_image_width"
                    :height="$post->cover_image_height"
                    aspect="aspect-[16/9]"
                    loading="eager"
                    fetchpriority="high"
                />
            </div>
        @endif

        <div class="prose-content mt-10 max-w-3xl text-stone-800">
            {!! $post->body !!}
        </div>
    </x-site-container>
</body>
</html>
