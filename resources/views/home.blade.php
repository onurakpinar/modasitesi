@extends('layouts.app')

@section('content')
    @if ($featuredPosts->isNotEmpty())
        <section class="border-b border-stone-200 bg-white">
            <x-site-container class="max-w-6xl py-12 sm:py-16">
                <p class="text-xs font-medium uppercase tracking-[0.25em] text-accent-700">Öne çıkan</p>

                <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:gap-12">
                    @php $hero = $featuredPosts->first(); @endphp
                    <div class="lg:col-span-7">
                        <x-post-card :post="$hero" :featured="true" />
                    </div>

                    @if ($featuredPosts->count() > 1)
                        <div class="flex flex-col gap-8 lg:col-span-5">
                            @foreach ($featuredPosts->skip(1) as $post)
                                <article class="group flex gap-4 border-t border-stone-100 pt-6 first:border-t-0 first:pt-0">
                                    <a href="{{ route('posts.show', $post->slug) }}" class="w-24 shrink-0 overflow-hidden sm:w-28 md:w-36">
                                        <x-cover-image
                                            :src="$post->cover_image"
                                            :fallback="$post->cover_image_fallback"
                                            :alt="$post->cover_image_alt ?: $post->title"
                                            :width="$post->cover_image_width"
                                            :height="$post->cover_image_height"
                                            aspect="aspect-square"
                                        />
                                    </a>
                                    <div class="min-w-0">
                                        @if ($post->category)
                                            <a href="{{ route('categories.show', $post->category->slug) }}" class="text-xs font-medium uppercase tracking-[0.2em] text-accent-700">
                                                {{ $post->category->name }}
                                            </a>
                                        @endif
                                        <h3 class="mt-1 line-clamp-3 font-display text-lg leading-snug text-stone-900">
                                            <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-accent-800">{{ $post->title }}</a>
                                        </h3>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-site-container>
        </section>
    @else
        <section class="border-b border-stone-200 bg-white">
            <x-site-container class="max-w-6xl py-16 sm:py-20">
                <p class="text-xs font-medium uppercase tracking-[0.25em] text-accent-700">Moda yayını</p>
                <h1 class="mt-4 max-w-2xl break-words font-display text-3xl leading-tight text-stone-900 sm:text-4xl lg:text-5xl">
                    Stil, trend ve gardırop üzerine özgün içerikler
                </h1>
                <p class="mt-6 max-w-xl text-base leading-relaxed text-stone-600">
                    {{ $siteShortDescription ?: 'Özgün moda yazıları yakında burada yayınlanacak.' }}
                </p>
            </x-site-container>
        </section>
    @endif

    @if ($latestPosts->isNotEmpty())
        <section>
            <x-site-container class="max-w-6xl py-14 sm:py-16">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="font-display text-2xl text-stone-900 sm:text-3xl">Son Yazılar</h2>
                    <a href="{{ route('posts.index') }}" class="shrink-0 text-sm font-medium uppercase tracking-widest text-accent-700 hover:text-accent-800">Tümünü gör</a>
                </div>
                <x-post-grid :posts="$latestPosts" class="mt-10" />
            </x-site-container>
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section class="border-t border-stone-200 bg-white">
            <x-site-container class="max-w-6xl py-14 sm:py-16">
                <h2 class="font-display text-2xl text-stone-900 sm:text-3xl">Kategoriler</h2>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('categories.show', $category->slug) }}"
                            class="group border border-stone-200 bg-stone-50 px-6 py-8 transition hover:border-stone-300 hover:bg-white"
                        >
                            <h3 class="break-words font-display text-xl text-stone-900 group-hover:text-accent-800">{{ $category->name }}</h3>
                            @if ($category->description)
                                <p class="mt-2 line-clamp-2 text-base text-stone-600">{{ $category->description }}</p>
                            @endif
                            <p class="mt-4 text-sm text-stone-500">{{ $category->posts_count }} yazı</p>
                        </a>
                    @endforeach
                </div>
            </x-site-container>
        </section>
    @endif

    @if ($editorPicks->isNotEmpty())
        <section class="border-t border-stone-200">
            <x-site-container class="max-w-6xl py-14 sm:py-16">
                <h2 class="font-display text-3xl text-stone-900">Editörün Seçtikleri</h2>
                <x-post-grid :posts="$editorPicks" class="mt-10" columns="sm:grid-cols-2" />
            </x-site-container>
        </section>
    @endif
@endsection
