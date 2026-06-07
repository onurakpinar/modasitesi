@extends('layouts.app')

@section('content')
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <x-breadcrumb :items="[
            ['label' => 'Ana Sayfa', 'url' => route('home')],
            ['label' => 'Etiket'],
            ['label' => $tag->name],
        ]" />

        <header class="max-w-2xl">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent-700">Etiket</p>
            <h1 class="mt-3 font-display text-4xl text-stone-900">{{ $tag->name }}</h1>
        </header>

        @if ($posts->count() > 0)
            <x-post-grid :posts="$posts" class="mt-12" />
            <div class="mt-12">{{ $posts->links() }}</div>
        @else
            <p class="mt-12 text-base text-stone-600">Bu etikette henüz yayınlanmış yazı yok.</p>
        @endif
    </x-site-container>
@endsection
