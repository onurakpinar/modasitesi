@extends('layouts.app')

@section('content')
    <article>
        <x-site-container class="max-w-6xl py-10 sm:py-14">
            <x-breadcrumb :items="array_values(array_filter([
                ['label' => 'Ana Sayfa', 'url' => route('home')],
                $post->category ? ['label' => $post->category->name, 'url' => route('categories.show', $post->category->slug)] : null,
                ['label' => $post->title],
            ]))" />

            <header class="max-w-3xl">
                @if ($post->category)
                    <a href="{{ route('categories.show', $post->category->slug) }}" class="text-xs font-medium uppercase tracking-[0.2em] text-accent-700 hover:text-accent-800">
                        {{ $post->category->name }}
                    </a>
                @endif

                <h1 class="mt-4 font-display text-4xl leading-tight text-stone-900 sm:text-5xl">{{ $post->title }}</h1>

                <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-stone-500">
                    @if ($post->author)
                        <a href="{{ route('authors.show', $post->author->slug) }}" class="font-medium text-stone-700 hover:text-accent-700">
                            {{ $post->author->name }}
                        </a>
                        <span aria-hidden="true">·</span>
                    @endif
                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                        {{ $post->published_at->translatedFormat('d F Y') }}
                    </time>
                </div>
            </header>

            <div class="mt-10 max-w-4xl overflow-hidden">
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

            <div class="prose-content mx-auto mt-10 text-stone-800">
                {!! $postBodyBeforeAd !!}
                <x-adsense.article-middle :post="$post" />
                {!! $postBodyAfterAd !!}
                <x-adsense.article-bottom :post="$post" />
            </div>

            @if ($post->tags->isNotEmpty())
                <div class="mx-auto mt-10 max-w-3xl border-t border-stone-200 pt-8">
                    <h2 class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Etiketler</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <a href="{{ route('tags.show', $tag->slug) }}" class="rounded-full border border-stone-200 px-4 py-1.5 text-sm text-stone-700 hover:border-stone-300 hover:text-accent-700">
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-site-container>

        @if ($relatedPosts->isNotEmpty())
            <section class="border-t border-stone-200 bg-white">
                <x-site-container class="max-w-6xl py-14">
                    <h2 class="font-display text-3xl text-stone-900">İlgili Yazılar</h2>
                    <x-post-grid :posts="$relatedPosts" class="mt-10" />
                </x-site-container>
            </section>
        @endif
    </article>
@endsection
