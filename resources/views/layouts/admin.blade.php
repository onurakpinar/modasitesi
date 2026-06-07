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
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <div class="flex min-h-screen">
        <div
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-0 z-40 bg-black/30 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <x-admin-sidebar />

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-stone-200 bg-white px-4 py-3 sm:px-6">
                <button
                    type="button"
                    x-ref="adminMenuButton"
                    class="rounded-md p-2 text-stone-600 hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-accent-600 focus:ring-offset-2 lg:hidden"
                    @click="sidebarOpen = !sidebarOpen; if (sidebarOpen) { $nextTick(() => $refs.adminSidebar?.querySelector('a')?.focus()) }"
                    :aria-expanded="sidebarOpen"
                    aria-controls="admin-sidebar"
                    aria-label="Menüyü aç veya kapat"
                >
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex flex-1 items-center justify-end gap-4">
                    <a href="{{ route('home') }}" class="text-sm text-stone-600 hover:text-stone-900" target="_blank">
                        Siteyi görüntüle
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-stone-700 hover:text-stone-900">
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
