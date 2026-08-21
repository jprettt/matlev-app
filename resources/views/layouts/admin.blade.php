<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workspace Verifikator') - PLN MATLEV</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pln: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554'
                        },
                        accent: {
                            100: '#fef3c7',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            600: '#d97706'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-stone-50 text-stone-800 font-sans" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <aside class="w-72 bg-white border-r border-stone-200 hidden lg:flex lg:flex-col sticky top-0 h-screen">
            <div class="px-6 py-5 border-b border-stone-200">
                <p class="text-[11px] uppercase tracking-[0.2em] text-pln-700 font-bold">Verifikator Workspace</p>
                <h1 class="text-xl font-extrabold font-display text-pln-900 mt-1">PLN MATLEV Admin</h1>
            </div>

            <nav class="p-4 space-y-1.5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-pln-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.queue') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.queue') ? 'bg-pln-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3V3m0 0L9 3m6 0v2M4 7h16M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12" /></svg>
                    <span>Antrean Verifikasi</span>
                </a>

                <a href="{{ route('admin.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.history') ? 'bg-pln-700 text-white shadow-sm' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-3-6.708" /></svg>
                    <span>Riwayat Evaluasi</span>
                </a>
            </nav>

            <div class="mt-auto p-4 border-t border-stone-200 space-y-3">
                <a href="{{ route('admin.users') }}" class="block text-xs text-stone-500 hover:text-pln-700 font-semibold">Manajemen User</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">Logout</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="lg:hidden sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-stone-200 px-4 py-3 flex items-center justify-between">
                <h2 class="font-display font-extrabold text-pln-900">PLN MATLEV Admin</h2>
                <button @click="sidebarOpen = true" class="p-2 rounded-lg border border-stone-300 text-stone-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

    <div x-show="sidebarOpen" x-transition class="fixed inset-0 z-50 lg:hidden" style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="sidebarOpen = false"></div>
        <aside class="absolute left-0 top-0 h-full w-72 bg-white border-r border-stone-200 p-4 space-y-2">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-display font-extrabold text-pln-900">Menu Verifikator</h3>
                <button @click="sidebarOpen = false" class="text-stone-500">✕</button>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-pln-700 text-white' : 'text-stone-700 hover:bg-stone-100' }}">Dashboard</a>
            <a href="{{ route('admin.queue') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.queue') ? 'bg-pln-700 text-white' : 'text-stone-700 hover:bg-stone-100' }}">Antrean Verifikasi</a>
            <a href="{{ route('admin.history') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.history') ? 'bg-pln-700 text-white' : 'text-stone-700 hover:bg-stone-100' }}">Riwayat Evaluasi</a>
            <a href="{{ route('admin.users') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.users') ? 'bg-pln-700 text-white' : 'text-stone-700 hover:bg-stone-100' }}">Manajemen User</a>
            <form action="{{ route('logout') }}" method="POST" class="pt-2">
                @csrf
                <button type="submit" class="w-full py-2 rounded-lg bg-rose-600 text-white text-sm font-bold">Logout</button>
            </form>
        </aside>
    </div>
</body>
</html>
