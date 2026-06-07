@extends('layouts.app')

@section('content')
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <header class="max-w-2xl">
            <h1 class="font-display text-3xl text-stone-900 sm:text-4xl">Yazılar</h1>
            <p class="mt-4 text-base text-stone-600">Yayınlanmış moda yazılarını keşfedin.</p>
        </header>

        @if ($navCategories->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                <a
                    href="{{ route('posts.index') }}"
                    class="rounded-full border px-4 py-2 text-sm {{ $activeCategory ? 'border-stone-200 text-stone-600 hover:border-stone-300' : 'border-stone-900 bg-stone-900 text-white' }}"
                >
                    Tümü
                </a>
                @foreach ($navCategories as $category)
                    <a
                        href="{{ route('posts.index', ['kategori' => $category->slug]) }}"
                        class="rounded-full border px-4 py-2 text-sm {{ $activeCategory?->id === $category->id ? 'border-stone-900 bg-stone-900 text-white' : 'border-stone-200 text-stone-600 hover:border-stone-300' }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($posts->count() > 0)
            <x-post-grid :posts="$posts" class="mt-12" />
            <div class="mt-12">{{ $posts->links() }}</div>
        @else
            <p class="mt-12 text-base text-stone-600">Bu filtrede yayınlanmış yazı bulunamadı.</p>
        @endif
    </x-site-container>
@endsection
