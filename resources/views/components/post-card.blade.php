@props(['post', 'featured' => false])

<article {{ $attributes->merge(['class' => 'group flex flex-col']) }}>
    <a href="{{ route('posts.show', $post->slug) }}" class="block overflow-hidden bg-white">
        <x-cover-image
            :src="$post->cover_image"
            :fallback="$post->cover_image_fallback"
            :alt="$post->cover_image_alt ?: $post->title"
            :aspect="$featured ? 'aspect-[16/10]' : 'aspect-[4/3]'"
            :width="$post->cover_image_width"
            :height="$post->cover_image_height"
            :loading="$featured ? 'eager' : 'lazy'"
            :fetchpriority="$featured ? 'high' : null"
            class="transition duration-300 group-hover:opacity-95"
        />
    </a>

    <div class="mt-4 flex flex-1 flex-col">
        @if ($post->category)
            <a
                href="{{ route('categories.show', $post->category->slug) }}"
                class="text-xs font-medium uppercase tracking-[0.2em] text-accent-700 hover:text-accent-800"
            >
                {{ $post->category->name }}
            </a>
        @endif

        <h{{ $featured ? 2 : 3 }} class="mt-2 font-display text-xl leading-snug text-stone-900 sm:text-2xl">
            <a href="{{ route('posts.show', $post->slug) }}" class="hover:text-accent-800">
                {{ $post->title }}
            </a>
        </h{{ $featured ? 2 : 3 }}>

        @if ($post->excerpt)
            <p class="mt-2 line-clamp-3 text-base leading-relaxed text-stone-600">
                {{ $post->excerpt }}
            </p>
        @endif

        <p class="mt-auto pt-4 text-sm text-stone-500">
            @if ($post->author)
                <a href="{{ route('authors.show', $post->author->slug) }}" class="hover:text-stone-700">
                    {{ $post->author->name }}
                </a>
                <span class="mx-1">·</span>
            @endif
            <time datetime="{{ $post->published_at->toIso8601String() }}">
                {{ $post->published_at->translatedFormat('d F Y') }}
            </time>
        </p>
    </div>
</article>
