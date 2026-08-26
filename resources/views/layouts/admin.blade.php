<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workspace Verifikator') - PLN MATLEV</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pln: {
                            50: '#fffde7',
                            100: '#fff9c4',
                            200: '#fff59d',
                            300: '#fff176',
                            400: '#ffeb3b',
                            500: '#fdd835',
                            600: '#f9c900',
                            700: '#d9a900',
                            800: '#a87800',
                            900: '#6f4f00',
                            950: '#3f2d00'
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
                        display: ['Merriweather', 'serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-stone-800 font-sans" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <aside class="w-72 bg-white text-stone-900 hidden lg:flex lg:flex-col sticky top-0 h-screen shadow-2xl shadow-amber-900/20" style="background-image: linear-gradient(rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0.18)), url('{{ asset('images/batik kuning.png') }}'); background-size: auto, 560px auto; background-position: center, center top;">
            <div class="px-6 py-5 border-b border-amber-200/80 bg-white/90">
                <img src="{{ asset('images/pln transparan.png') }}" alt="Logo PLN" class="h-12 w-auto object-contain object-left">
                <p class="text-[11px] uppercase tracking-[0.2em] text-amber-800 font-bold mt-4">Ruang Kerja Verifikator</p>
                <h1 class="text-xl font-extrabold font-display text-stone-900 mt-1">PLN MATLEV</h1>
                <div class="mt-4 h-1 w-16 rounded-full bg-pln-500"></div>
            </div>

            <nav class="p-4 space-y-1.5">
                <p class="px-3 pb-2 text-[10px] uppercase tracking-[0.18em] text-amber-800 font-bold">Menu Utama</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-amber-800 shadow-lg shadow-amber-900/10' : 'text-stone-800 hover:bg-white/70 hover:text-amber-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.queue') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.queue') ? 'bg-white text-amber-800 shadow-lg shadow-amber-900/10' : 'text-stone-800 hover:bg-white/70 hover:text-amber-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3V3m0 0L9 3m6 0v2M4 7h16M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12" /></svg>
                    <span>Antrean Verifikasi</span>
                </a>

                <a href="{{ route('admin.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ request()->routeIs('admin.history') ? 'bg-white text-amber-800 shadow-lg shadow-amber-900/10' : 'text-stone-800 hover:bg-white/70 hover:text-amber-900' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-3-6.708" /></svg>
                    <span>Riwayat Evaluasi</span>
                </a>
            </nav>

            <div class="mt-auto p-4 border-t border-white/10 space-y-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold shadow-lg shadow-rose-950/20 transition">Logout</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="lg:hidden sticky top-0 z-30 bg-pln-950/95 backdrop-blur border-b border-white/10 px-4 py-3 flex items-center justify-between text-white">
                <div class="flex items-center gap-2"><img src="{{ asset('images/pln transparan.png') }}" alt="Logo PLN" class="h-8 w-auto object-contain"><h2 class="font-display font-extrabold text-white">PLN MATLEV</h2></div>
                <button @click="sidebarOpen = true" class="p-2 rounded-lg border border-white/20 text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </header>

            <main class="p-4 sm:p-6 lg:p-8 max-w-[1600px]">
                @yield('content')
            </main>
        </div>
    </div>

    <div x-show="sidebarOpen" x-transition class="fixed inset-0 z-50 lg:hidden" style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="sidebarOpen = false"></div>
        <aside class="absolute left-0 top-0 h-full w-72 bg-white text-stone-900 border-r border-amber-200 p-4 space-y-2" style="background-image: linear-gradient(rgba(0, 0, 0, 0.18), rgba(0, 0, 0, 0.18)), url('{{ asset('images/batik kuning.png') }}'); background-size: auto, 560px auto; background-position: center, center top;">
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-display font-extrabold text-pln-900">Menu Verifikator</h3>
                <button @click="sidebarOpen = false" class="text-stone-500">✕</button>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-white text-amber-800' : 'text-stone-700 hover:bg-white/70 hover:text-amber-900' }}">Dashboard</a>
            <a href="{{ route('admin.queue') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.queue') ? 'bg-white text-amber-800' : 'text-stone-700 hover:bg-white/70 hover:text-amber-900' }}">Antrean Verifikasi</a>
            <a href="{{ route('admin.history') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.history') ? 'bg-white text-amber-800' : 'text-stone-700 hover:bg-white/70 hover:text-amber-900' }}">Riwayat Evaluasi</a>
            <form action="{{ route('logout') }}" method="POST" class="pt-2">
                @csrf
                <button type="submit" class="w-full py-2 rounded-lg bg-rose-600 text-white text-sm font-bold">Logout</button>
            </form>
        </aside>
    </div>
</body>
</html>
