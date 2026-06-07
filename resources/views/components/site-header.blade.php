@php
    $logoUrl = \App\Support\MediaUrl::public($siteLogo ?? null);
@endphp

<header
    class="relative z-40 border-b border-stone-200 bg-white"
    x-data="{
        open: false,
        categoriesOpen: false,
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.categoriesOpen = false;
                document.body.classList.add('overflow-hidden');
                this.$nextTick(() => this.$refs.mobileNav?.querySelector('a, button')?.focus());
            } else {
                this.close();
            }
        },
        close() {
            if (!this.open) {
                return;
            }
            this.open = false;
            this.categoriesOpen = false;
            document.body.classList.remove('overflow-hidden');
            this.$nextTick(() => this.$refs.menuButton?.focus());
        },
        toggleCategories() {
            this.categoriesOpen = !this.categoriesOpen;
        },
        closeCategories() {
            this.categoriesOpen = false;
        },
    }"
    @keydown.escape.window="close(); closeCategories()"
>
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex items-center justify-between gap-3 py-4 sm:gap-4 sm:py-5">
            <a href="{{ route('home') }}" class="group flex min-w-0 flex-1 items-center gap-3 sm:gap-4 lg:flex-none" @click="close()">
                @if ($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $siteName ?? config('site.name') }}"
                        class="h-9 w-auto max-w-[120px] shrink-0 object-contain sm:h-10 sm:max-w-[140px]"
                        width="140"
                        height="48"
                    >
                @endif
                <div class="min-w-0">
                    <span class="font-display text-xl tracking-tight text-stone-900 sm:text-2xl lg:text-3xl">
                        {{ $siteName ?? config('site.name') }}
                    </span>
                    @if ($siteTagline ?? config('site.tagline'))
                        <span class="mt-0.5 block truncate text-[0.65rem] font-medium uppercase tracking-[0.18em] text-stone-500 sm:text-xs sm:tracking-[0.2em]">
                            {{ $siteTagline ?? config('site.tagline') }}
                        </span>
                    @endif
                </div>
            </a>

            <nav
                id="site-navigation-desktop"
                aria-label="Ana menü"
                class="hidden items-center gap-5 text-sm font-medium uppercase tracking-widest text-stone-600 lg:flex"
            >
                <a href="{{ route('home') }}" class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('home') ? 'text-accent-700' : '' }}">
                    Ana Sayfa
                </a>
                <a href="{{ route('posts.index') }}" class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('posts.*') ? 'text-accent-700' : '' }}">
                    Yazılar
                </a>

                @if ($navCategories->isNotEmpty())
                    <div class="relative" @click.outside="closeCategories()">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('categories.show') ? 'text-accent-700' : '' }}"
                            @click="toggleCategories()"
                            :aria-expanded="categoriesOpen"
                            aria-controls="site-navigation-categories"
                        >
                            Kategoriler
                            <svg
                                class="size-4 transition-transform duration-200"
                                :class="{ 'rotate-180': categoriesOpen }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            id="site-navigation-categories"
                            x-show="categoriesOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-0 top-full z-50 mt-2 min-w-[12rem] rounded-md border border-stone-200 bg-white py-2 shadow-lg"
                        >
                            @foreach ($navCategories as $category)
                                <a
                                    href="{{ route('categories.show', $category->slug) }}"
                                    class="block px-4 py-2.5 text-sm normal-case tracking-normal text-stone-700 hover:bg-stone-50 hover:text-accent-700 {{ request()->routeIs('categories.show') && request()->route('slug') === $category->slug ? 'bg-stone-50 text-accent-700' : '' }}"
                                    @click="closeCategories()"
                                >
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <a href="{{ route('search') }}" class="hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 {{ request()->routeIs('search') ? 'text-accent-700' : '' }}">
                    Ara
                </a>
            </nav>

            <div class="flex shrink-0 items-center gap-1">
                <a
                    href="{{ route('search') }}"
                    class="rounded-md p-2 text-stone-600 hover:bg-stone-100 hover:text-stone-900 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 lg:hidden"
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

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 top-[var(--site-header-offset,4.5rem)] z-30 bg-stone-900/20 lg:hidden"
            @click="close()"
            aria-hidden="true"
        ></div>

        <nav
            id="site-navigation-mobile"
            x-ref="mobileNav"
            aria-label="Mobil menü"
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="relative z-40 max-h-[calc(100dvh-5rem)] overflow-y-auto border-t border-stone-100 bg-white pb-4 lg:hidden"
        >
            <a href="{{ route('home') }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2" @click="close()">Ana Sayfa</a>
            <a href="{{ route('posts.index') }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2" @click="close()">Yazılar</a>

            @if ($navCategories->isNotEmpty())
                <div x-data="{ mobileCategoriesOpen: false }" class="border-t border-stone-50 first:border-t-0">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2"
                        @click="mobileCategoriesOpen = !mobileCategoriesOpen"
                        :aria-expanded="mobileCategoriesOpen"
                    >
                        Kategoriler
                        <svg
                            class="size-4 transition-transform duration-200"
                            :class="{ 'rotate-180': mobileCategoriesOpen }"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="mobileCategoriesOpen"
                        x-cloak
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pb-2 pl-3"
                    >
                        @foreach ($navCategories as $category)
                            <a
                                href="{{ route('categories.show', $category->slug) }}"
                                class="block py-2.5 text-sm font-medium normal-case tracking-normal text-stone-600 hover:text-accent-700"
                                @click="close()"
                            >
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <a href="{{ route('search') }}" class="block py-3 text-sm font-medium uppercase tracking-widest text-stone-600 hover:text-accent-700 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2" @click="close()">Ara</a>
        </nav>
    </div>
</header>
