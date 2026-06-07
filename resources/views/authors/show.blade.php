@extends('layouts.app')

@section('content')
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <x-breadcrumb :items="[
            ['label' => 'Ana Sayfa', 'url' => route('home')],
            ['label' => 'Yazar'],
            ['label' => $author->name],
        ]" />

        <header class="flex flex-col gap-6 sm:flex-row sm:items-start">
            <div class="shrink-0">
                @php $profileUrl = \App\Support\MediaUrl::public($author->profile_image); @endphp
                @if ($profileUrl)
                    <img src="{{ $profileUrl }}" alt="{{ $author->name }}" width="128" height="128" loading="lazy" decoding="async" class="size-28 rounded-full object-cover sm:size-32">
                @else
                    <div class="flex size-28 items-center justify-center rounded-full bg-stone-100 sm:size-32" aria-hidden="true">
                        <span class="font-display text-3xl text-stone-400">{{ mb_substr($author->name, 0, 1) }}</span>
                    </div>
                @endif
            </div>

            <div class="max-w-2xl">
                <p class="text-xs font-medium uppercase tracking-[0.2em] text-accent-700">Yazar</p>
                <h1 class="mt-2 font-display text-4xl text-stone-900">{{ $author->name }}</h1>
                @if ($author->short_bio)
                    <p class="mt-4 text-base leading-relaxed text-stone-600">{{ $author->short_bio }}</p>
                @endif
            </div>
        </header>

        @if ($posts->count() > 0)
            <h2 class="mt-14 font-display text-2xl text-stone-900">Yazıları</h2>
            <x-post-grid :posts="$posts" class="mt-8" />
            <div class="mt-12">{{ $posts->links() }}</div>
        @else
            <p class="mt-12 text-base text-stone-600">Bu yazarın henüz yayınlanmış yazısı yok.</p>
        @endif
    </x-site-container>
@endsection
