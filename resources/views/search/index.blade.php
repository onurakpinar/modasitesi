@extends('layouts.app')

@section('content')
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <header class="max-w-2xl">
            <h1 class="font-display text-3xl text-stone-900 sm:text-4xl">Arama</h1>
            <p class="mt-4 text-base text-stone-600">Yazılarda başlık ve içerik arayın.</p>
        </header>

        <form method="GET" action="{{ route('search') }}" class="mt-8 max-w-xl" role="search">
            <label for="search-query" class="sr-only">Arama</label>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input
                    id="search-query"
                    type="search"
                    name="q"
                    value="{{ $query }}"
                    maxlength="100"
                    placeholder="Anahtar kelime yazın"
                    class="w-full border border-stone-300 bg-white px-4 py-3 text-base text-stone-900 placeholder:text-stone-400 focus:border-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-600/30"
                >
                <button type="submit" class="inline-flex min-h-11 shrink-0 items-center justify-center border border-stone-900 bg-stone-900 px-5 py-3 text-sm font-medium uppercase tracking-widest text-white hover:bg-stone-800 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 sm:w-auto">
                    Ara
                </button>
            </div>
        </form>

        @if ($query === '')
            <p class="mt-10 text-base text-stone-600">Aramak istediğiniz kelimeyi yukarıya yazın.</p>
        @elseif ($posts && $posts->count() > 0)
            <p class="mt-10 text-base text-stone-600">
                “<span class="font-medium text-stone-900">{{ $query }}</span>” için {{ $posts->total() }} sonuç bulundu.
            </p>
            <x-post-grid :posts="$posts" class="mt-10" />
            <div class="mt-12">{{ $posts->links() }}</div>
        @else
            <p class="mt-10 text-base text-stone-600">
                “<span class="font-medium text-stone-900">{{ $query }}</span>” için sonuç bulunamadı. Farklı bir anahtar kelime deneyin.
            </p>
        @endif
    </x-site-container>
@endsection
