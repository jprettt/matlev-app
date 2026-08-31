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
        window.addEventListener('load', () => {
            const viewport = { width: window.innerWidth, height: window.innerHeight };
            const isLgScreen = window.innerWidth >= 1024;
            const asides = document.querySelectorAll('aside');
            
            console.log('=== SIDEBAR DEBUG ===');
            console.log('Viewport:', viewport.width + 'x' + viewport.height);
            console.log('Is LG Screen (>= 1024px):', isLgScreen);
            console.log('Total aside elements found:', asides.length);
            
            asides.forEach((aside, index) => {
                const style = window.getComputedStyle(aside);
                console.log(`Aside ${index}:`, {
                    display: style.display,
                    visibility: style.visibility,
                    width: style.width,
                    classes: aside.className
                });
            });
        });
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pln: {
                            50: '#fffde7', 100: '#fff9c4', 200: '#fff59d', 300: '#fff176',
                            400: '#ffeb3b', 500: '#fdd835', 600: '#f9c900', 700: '#d9a900',
                            800: '#a87800', 900: '#6f4f00', 950: '#3f2d00'
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
<body class="bg-stone-100 text-stone-800 font-sans" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
<div class="min-h-screen flex items-start">
    <aside class="bg-[#b89416] text-white flex flex-col sticky top-0 h-screen self-start shadow-2xl shadow-yellow-900/20 transition-all duration-300 hidden lg:flex" :class="sidebarCollapsed ? 'w-20' : 'w-64'">
        <div class="px-3 pt-3 pb-4 border-b border-yellow-200/20">
            <button type="button" @click="sidebarCollapsed = !sidebarCollapsed" class="mb-3 ml-auto flex items-center justify-center p-1 text-white hover:text-yellow-100 transition" :title="sidebarCollapsed ? 'Buka sidebar' : 'Ciutkan sidebar'" aria-label="Ciutkan atau buka sidebar">
                    <svg class="w-6 h-6 transition-transform" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <div class="flex items-center justify-center gap-3">
                    <img src="{{ asset('images/Logo-PLN-Terbaru.jpg') }}" alt="Logo PLN" class="object-contain rounded-sm" :class="sidebarCollapsed ? 'h-12 w-14' : 'h-16 w-24'">
                    <div x-show="!sidebarCollapsed" x-transition class="text-left">
                        <p class="text-lg font-extrabold leading-tight text-white">Verifikator</p>
                        <p class="text-lg font-extrabold leading-tight text-white">MatLev K3</p>
                    </div>
                </div>
            </div>
            <nav class="p-3 space-y-1.5">
                <p x-show="!sidebarCollapsed" class="px-3 pb-2 text-[10px] uppercase tracking-[0.18em] text-yellow-100 font-bold">Menu Utama</p>
                <a href="{{ route('admin.dashboard') }}" title="Dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-base font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#dfbd28] text-white shadow-lg shadow-yellow-950/20' : 'text-white hover:bg-[#caa51b]' }}" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    <span x-show="!sidebarCollapsed">Dashboard</span>
                </a>
                <a href="{{ route('admin.queue') }}" title="Antrean Verifikasi" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-base font-semibold transition {{ request()->routeIs('admin.queue') ? 'bg-[#dfbd28] text-white shadow-lg shadow-yellow-950/20' : 'text-white hover:bg-[#caa51b]' }}" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3V3m0 0L9 3m6 0v2M4 7h16M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12" /></svg>
                    <span x-show="!sidebarCollapsed">Antrean Verifikasi</span>
                </a>
                <a href="{{ route('admin.activity') }}" title="Riwayat Aktivitas" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-base font-semibold transition {{ request()->routeIs('admin.activity') ? 'bg-[#dfbd28] text-white shadow-lg shadow-yellow-950/20' : 'text-white hover:bg-[#caa51b]' }}" :class="sidebarCollapsed ? 'justify-center' : ''">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-3-6.708" /></svg>
                        <span x-show="!sidebarCollapsed">Riwayat Aktivitas</span>
                </a>
            </nav>
            <div class="mt-auto p-3 border-t border-yellow-200/20 space-y-2">
                <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Yakin ingin logout?');">
                    @csrf
                    <button type="submit" title="Logout" class="w-full flex items-center justify-center gap-2 py-2.5 text-white hover:text-yellow-100 text-sm font-bold transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8l4 4m0 0l-4 4m4-4H9m4-4V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h5a2 2 0 002-2v-3" /></svg>
                        <span x-show="!sidebarCollapsed">Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        <div class="flex-1 min-w-0">
            <div class="lg:hidden sticky top-0 z-30 bg-white border-b border-stone-200 px-4 py-3 flex items-center justify-end">
                <button @click="sidebarOpen = true" class="p-2 rounded-lg border border-stone-300 text-stone-600" aria-label="Buka menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
            <div class="flex justify-end items-center gap-3 px-4 sm:px-6 lg:px-8 pt-2">
                <div class="relative" x-data="{ notificationOpen: false }">
                    <button type="button" @click="notificationOpen = !notificationOpen" class="relative w-10 h-10 inline-flex items-center justify-center rounded-lg text-stone-700 hover:bg-stone-100 transition" aria-label="Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.6A2 2 0 0 1 18 14v-3a6 6 0 0 0-12 0v3a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9" /></svg>
                        @if($navbarUnreadCount > 0)<span class="absolute top-1 right-1 min-w-4 h-4 px-1 rounded-full bg-pln-700 text-white text-[9px] font-extrabold leading-4">{{ $navbarUnreadCount > 99 ? '99+' : $navbarUnreadCount }}</span>@endif
                    </button>
                    <div x-show="notificationOpen" x-transition @click.outside="notificationOpen = false" class="absolute right-0 z-40 mt-2 w-80 rounded-xl border border-stone-200 bg-white shadow-xl" style="display: none;">
                        <div class="flex items-center justify-between border-b border-stone-100 px-4 py-3"><strong class="text-xs">Notifikasi</strong>@if($navbarUnreadCount > 0)<form action="{{ route('notifications.read-all') }}" method="POST">@csrf<button class="text-[10px] font-bold text-pln-700">Tandai semua dibaca</button></form>@endif</div>
                        @forelse($navbarNotifications as $notification)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">@csrf<button type="submit" class="w-full border-b border-stone-100 px-4 py-3 text-left {{ $notification->is_read ? '' : 'bg-amber-50' }}"><p class="text-xs font-bold">{{ $notification->title }}</p><p class="mt-1 text-[11px] text-stone-500">{{ $notification->message }}</p></button></form>
                        @empty
                            <p class="px-4 py-6 text-center text-xs text-stone-400">Belum ada notifikasi.</p>
                        @endforelse
                        <a href="{{ route('notifications.history') }}" class="block border-t border-stone-100 px-4 py-3 text-center text-xs font-bold text-pln-700">Riwayat Notifikasi</a>
                    </div>
                </div>
            </div>
            <main class="p-2 sm:p-3 lg:p-4 max-w-[1600px]">@yield('content')</main>
        </div>
    </div>
    <div x-show="sidebarOpen" x-transition class="fixed inset-0 z-50 lg:hidden" style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="sidebarOpen = false"></div>
        <aside class="absolute left-0 top-0 h-full w-72 bg-[#b89416] text-white border-r border-yellow-200/20 p-4 space-y-2">
            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-3"><img src="{{ asset('images/Logo-PLN-Terbaru.jpg') }}" alt="Logo PLN" class="h-14 w-20 object-contain rounded-sm"><h3 class="text-lg font-display font-extrabold text-white">Verifikator MatLev K3</h3></div>
                <button @click="sidebarOpen = false" class="text-white text-xl" aria-label="Tutup menu">&times;</button>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-base {{ request()->routeIs('admin.dashboard') ? 'bg-[#dfbd28] text-white' : 'text-white hover:bg-[#caa51b]' }}">Dashboard</a>
            <a href="{{ route('admin.queue') }}" class="block px-3 py-2 rounded-lg text-base {{ request()->routeIs('admin.queue') ? 'bg-[#dfbd28] text-white' : 'text-white hover:bg-[#caa51b]' }}">Antrean Verifikasi</a>
            <a href="{{ route('admin.activity') }}" class="block px-3 py-2 rounded-lg text-base {{ request()->routeIs('admin.activity') ? 'bg-[#dfbd28] text-white' : 'text-white hover:bg-[#caa51b]' }}">Riwayat Aktivitas</a>
            <form action="{{ route('logout') }}" method="POST" class="pt-2" onsubmit="return confirm('Yakin ingin logout?');">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-white hover:text-yellow-100 text-sm font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 8l4 4m0 0l-4 4m4-4H9m4-4V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h5a2 2 0 002-2v-3" /></svg>
                    <span>Logout</span>
                </button>
            </form>
        </aside>
    </div>
</body>
</html>