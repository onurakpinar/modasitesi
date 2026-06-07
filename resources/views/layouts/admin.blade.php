<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', 'Yönetim') — {{ $siteName ?? config('site.name') }}</title>

    <x-site-head :with-fonts="false" />
    @stack('head')
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased" x-data="adminShell" @keydown.escape.window="closeSidebar">
    <div class="flex min-h-screen">
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/30 lg:hidden"
            @click="closeSidebar()"
            aria-hidden="true"
        ></div>

        <x-admin-sidebar />

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-30 flex items-center justify-between gap-3 border-b border-stone-200 bg-white px-4 py-3 sm:px-6">
                <button
                    type="button"
                    x-ref="adminMenuButton"
                    class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-md p-2 text-stone-600 hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 lg:hidden"
                    @click="toggleSidebar()"
                    :aria-expanded="sidebarOpen"
                    aria-controls="admin-sidebar"
                    aria-label="Menüyü aç veya kapat"
                >
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path x-show="menuButtonShowsOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="sidebarOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="flex min-w-0 flex-1 items-center justify-end gap-2 sm:gap-4">
                    <a href="{{ route('home') }}" class="truncate text-sm text-stone-600 hover:text-stone-900" target="_blank" rel="noopener noreferrer">
                        <span class="hidden sm:inline">Siteyi görüntüle</span>
                        <span class="sm:hidden">Site</span>
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="min-h-11 px-2 text-sm font-medium text-stone-700 hover:text-stone-900">
                            Çıkış
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                <x-admin-flash />
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
