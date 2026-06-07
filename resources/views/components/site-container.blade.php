@props([
    'tag' => 'div',
])

<{{ $tag }} {{ $attributes->merge(['class' => 'mx-auto w-full max-w-5xl px-4 sm:px-6']) }}>
    {{ $slot }}
</{{ $tag }}>
