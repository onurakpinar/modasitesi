@php
    $logoUrl = \App\Support\MediaUrl::public($siteLogo ?? null);
@endphp

<header
    class="border-b border-stone-200 bg-white"
    x-data="{
        open: false,
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.mobileNav?.querySelector('a')?.focus());
            }
        },
        close() {
            if (!this.open) {
                return;
            }
            this.open = false;
            this.$nextTick(() => this.$refs.menuButton?.focus());
        },
    }"
    @keydown.escape.window="close()"
>
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex items-center justify-between gap-4 py-5 sm:py-6">
            <a href="{{ route('home') }}" class="group flex min-w-0 flex-1 items-center gap-4 lg:flex-none">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName ?? config('site.name') }}" class="h-10 w-auto max-w-[140px] shrink-0 object-contain sm:h-12" width="140" height="48">
                @endif
                <div class="min-w-0">
                    <span class="font-display text-2xl tracking-tight text-stone-900 sm:text-3xl">
                        {{ $siteName ?? config('site.name') }}
                    </span>
                    @if ($siteTagline ?? config('site.tagline'))
                        <span class="mt-0.5 block truncate text-xs font-medium uppercase tracking-[0.2em] text-stone-500">
                            {{ $siteTagline ?? config('site.tagline') }}
                        </span>
                    @endif
                </div>
            </a>

            <nav
                id="site-navigation-desktop"
                aria-label="Ana menü"
                class="hidden items-center gap-6 text-sm font-medium uppercase tracking-widest text-stone-600 lg:flex"
            >
                <a href="{{ route('home') }}" class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('home') ? 'text-accent-700' : '' }}">
                    Ana Sayfa
                </a>
                <a href="{{ route('posts.index') }}" class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('posts.*') ? 'text-accent-700' : '' }}">
                    Yazılar
                </a>
                @foreach ($navCategories as $category)
                    <a
                        href="{{ route('categories.show', $category->slug) }}"
                        class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('categories.show') && request()->route('slug') === $category->slug ? 'text-accent-700' : '' }}"
                    >
                        {{ $category->name }}
                    </a>
                @endforeach
                <a href="{{ route('search') }}" class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('search') ? 'text-accent-700' : '' }}">
                    Ara
                </a>
            </nav>

            <div class="flex shrink-0 items-center gap-1">
                <a
                    href="{{ route('search') }}"
                    class="rounded-md p-2 text-stone-600 hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2"
                    aria-label="Ara"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </a>

                <button
                    type="button"
                    x-ref="menuButton"
                    class="inline-flex items-center justify-center rounded-md p-2 text-stone-600 hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 lg:hidden"
                    @click="toggle()"
                    :aria-expanded="open"
                    aria-controls="site-navigation-mobile"
                    aria-label="Menüyü aç veya kapat"
                >
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <nav
            id="site-navigation-mobile"
            x-ref="mobileNav"
            aria-label="Mobil menü"
            x-show="open"
            x-cloak
            x-transition
            class="border-t border-stone-100 pb-4 lg:hidden"
        >
            <a href="{{ route('home') }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2">Ana Sayfa</a>
            <a href="{{ route('posts.index') }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2">Yazılar</a>
            @foreach ($navCategories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2">
                    {{ $category->name }}
                </a>
            @endforeach
            <a href="{{ route('search') }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2">Ara</a>
        </nav>
    </div>
</header>
