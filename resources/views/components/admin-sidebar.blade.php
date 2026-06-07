<aside
    id="admin-sidebar"
    x-ref="adminSidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 max-w-[85vw] -translate-x-full border-r border-stone-200 bg-white transition-transform lg:translate-x-0"
    :class="{ 'translate-x-0': sidebarOpen }"
    :aria-hidden="!isDesktop() && !sidebarOpen"
>
    <div class="flex h-full flex-col">
        <div class="flex items-start justify-between gap-3 border-b border-stone-200 px-5 py-5">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Yönetim</p>
                <p class="truncate font-display text-lg text-stone-900">{{ $siteName ?? config('site.name') }}</p>
            </div>
            <button
                type="button"
                class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-md p-2 text-stone-600 hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 lg:hidden"
                @click="closeSidebar()"
                aria-label="Menüyü kapat"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm" aria-label="Yönetim menüsü">
            @php
                $links = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'pattern' => 'admin.dashboard'],
                    ['route' => 'admin.posts.index', 'label' => 'Yazılar', 'pattern' => 'admin.posts.*'],
                    ['route' => 'admin.content-briefs.index', 'label' => 'İçerik Takvimi', 'pattern' => 'admin.content-briefs.*'],
                    ['route' => 'admin.categories.index', 'label' => 'Kategoriler', 'pattern' => 'admin.categories.*'],
                    ['route' => 'admin.tags.index', 'label' => 'Etiketler', 'pattern' => 'admin.tags.*'],
                    ['route' => 'admin.authors.index', 'label' => 'Yazarlar', 'pattern' => 'admin.authors.*'],
                    ['route' => 'admin.pages.index', 'label' => 'Sabit Sayfalar', 'pattern' => 'admin.pages.*'],
                    ['route' => 'admin.contact-messages.index', 'label' => 'İletişim Mesajları', 'pattern' => 'admin.contact-messages.*'],
                    ['route' => 'admin.settings.edit', 'label' => 'Site Ayarları', 'pattern' => 'admin.settings.*'],
                    ['route' => 'admin.adsense.edit', 'label' => 'AdSense ve Gizlilik', 'pattern' => 'admin.adsense.*'],
                ];
            @endphp

            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    @click="closeSidebarOnNavigate()"
                    class="flex min-h-11 items-center justify-between rounded-md px-3 py-2 {{ request()->routeIs($link['pattern']) ? 'bg-stone-100 font-medium text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}"
                >
                    <span>{{ $link['label'] }}</span>
                    @if ($link['route'] === 'admin.contact-messages.index' && ($unreadContactCount ?? 0) > 0)
                        <span class="rounded-full bg-accent-700 px-2 py-0.5 text-xs font-medium text-white" aria-label="{{ $unreadContactCount }} okunmamış mesaj">
                            {{ $unreadContactCount }}
                        </span>
                    @endif
                </a>
            @endforeach

            @if (auth()->user()?->isSuperAdmin())
                <a
                    href="{{ route('admin.users.index') }}"
                    @click="closeSidebarOnNavigate()"
                    class="mt-4 flex min-h-11 items-center rounded-md px-3 py-2 {{ request()->routeIs('admin.users.*') ? 'bg-stone-100 font-medium text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}"
                >
                    Kullanıcılar
                </a>
            @endif
        </nav>
    </div>
</aside>
