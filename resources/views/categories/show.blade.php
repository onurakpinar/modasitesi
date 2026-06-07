@extends('layouts.app')

@section('content')
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <x-breadcrumb :items="[
            ['label' => 'Ana Sayfa', 'url' => route('home')],
            ['label' => $category->name],
        ]" />

        <header class="max-w-2xl">
            <h1 class="font-display text-4xl text-stone-900">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-4 text-base leading-relaxed text-stone-600">{{ $category->description }}</p>
            @endif
        </header>

        @if ($posts->count() > 0)
            <x-post-grid :posts="$posts" class="mt-12" />
            <div class="mt-12">{{ $posts->links() }}</div>
        @else
            <p class="mt-12 text-base text-stone-600">Bu kategoride henüz yayınlanmış yazı yok.</p>
        @endif
    </x-site-container>
@endsection
