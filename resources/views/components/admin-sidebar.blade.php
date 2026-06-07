<aside
    id="admin-sidebar"
    x-ref="adminSidebar"
    class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full border-r border-stone-200 bg-white transition-transform lg:translate-x-0"
    :class="{ 'translate-x-0': sidebarOpen }"
>
    <div class="flex h-full flex-col">
        <div class="border-b border-stone-200 px-5 py-5">
            <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Yönetim</p>
            <p class="font-display text-lg text-stone-900">{{ $siteName ?? config('site.name') }}</p>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4 text-sm">
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
                    class="flex items-center justify-between rounded-md px-3 py-2 {{ request()->routeIs($link['pattern']) ? 'bg-stone-100 font-medium text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}"
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
                    class="mt-4 block rounded-md px-3 py-2 {{ request()->routeIs('admin.users.*') ? 'bg-stone-100 font-medium text-stone-900' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900' }}"
                >
                    Kullanıcılar
                </a>
            @endif
        </nav>
    </div>
</aside>
