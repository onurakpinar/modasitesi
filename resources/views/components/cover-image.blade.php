@props([
    'src' => null,
    'fallback' => null,
    'alt' => '',
    'aspect' => 'aspect-[4/3]',
    'loading' => 'lazy',
    'fetchpriority' => null,
    'width' => null,
    'height' => null,
])

@php
    $url = \App\Support\MediaUrl::public($src, $fallback);
    $fallbackUrl = filled($fallback) ? \App\Support\MediaUrl::public($fallback) : null;
    $baseClass = trim("$aspect w-full max-w-full");
    $imageClass = trim("$baseClass object-cover");
    $fallbackClass = trim("$baseClass flex items-center justify-center bg-stone-100");
@endphp

@if ($url)
    <picture>
        @if ($fallbackUrl && $url !== $fallbackUrl)
            <source srcset="{{ $url }}" type="image/webp">
        @endif
        <img
            src="{{ $fallbackUrl && $url !== $fallbackUrl ? $fallbackUrl : $url }}"
            alt="{{ $alt }}"
            loading="{{ $loading }}"
            decoding="async"
            @if ($width && $height)
                width="{{ $width }}"
                height="{{ $height }}"
            @endif
            @if ($fetchpriority)
                fetchpriority="{{ $fetchpriority }}"
            @endif
            {{ $attributes->merge(['class' => trim("$imageClass {$attributes->get('class', '')}")]) }}
        >
    </picture>
@else
    <div
        {{ $attributes->merge(['class' => trim("$fallbackClass {$attributes->get('class', '')}")]) }}
        role="img"
        aria-label="{{ $alt ?: 'Görsel mevcut değil' }}"
    >
        <svg class="size-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>
@endif
