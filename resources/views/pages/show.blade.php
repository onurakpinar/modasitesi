@extends('layouts.app')

@section('content')
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <x-breadcrumb :items="[
            ['label' => 'Ana Sayfa', 'url' => route('home')],
            ['label' => $page->title],
        ]" />

        <header class="max-w-3xl">
            <h1 class="break-words font-display text-3xl text-stone-900 sm:text-4xl lg:text-5xl">{{ $page->title }}</h1>
        </header>

        <div class="prose-content mt-10 max-w-3xl text-stone-800">
            @if ($isContactPage)
                @if (filled($pageBody))
                    {!! $pageBody !!}
                @endif

                @if (filled($contactEmail))
                    <p class="mt-6">
                        <span class="font-medium text-stone-900">E-posta:</span>
                        @if (\App\Support\Legal\LegalPlaceholders::isValidEmail($contactEmail))
                            <a href="mailto:{{ $contactEmail }}" class="text-accent-700 hover:text-accent-800">{{ $contactEmail }}</a>
                        @else
                            <span>{{ $contactEmail }}</span>
                        @endif
                    </p>
                @endif

                <x-contact-form />
            @elseif (filled($pageBody))
                {!! $pageBody !!}
            @else
                <p class="text-stone-600">Bu sayfanın içeriği henüz hazırlanmamıştır. Site yöneticisi panelinden tamamlanabilir.</p>
            @endif
        </div>
    </x-site-container>
@endsection
