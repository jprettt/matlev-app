<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Workspace Atasan') - MATLEV K3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { ink: '#172033', ocean: '#155e75', skywash: '#edf7fa', coral: '#e76f51' }, fontFamily: { sans: ['Manrope', 'sans-serif'], display: ['DM Serif Display', 'serif'] } } }
        }
    </script>
</head>
<body class="min-h-screen bg-[#f4f7f8] font-sans text-slate-900" x-data="{ sidebarOpen: false, collapsed: false }">
<div class="flex min-h-screen">
    <aside class="hidden lg:flex lg:flex-col bg-ink text-white transition-all duration-300" :class="collapsed ? 'w-20' : 'w-72'">
        <div class="flex items-center gap-3 border-b border-white/10 px-5 py-6" :class="collapsed ? 'justify-center px-3' : ''">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-400 text-lg font-black text-ink shadow-lg shadow-cyan-950/30">M</div>
            <div x-show="!collapsed" x-transition><p class="text-sm font-bold tracking-wide">MATLEV K3</p><p class="text-xs text-cyan-200">Executive Workspace</p></div>
            <button type="button" @click="collapsed = !collapsed" class="ml-auto hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sm text-slate-300 transition hover:bg-white/10 hover:text-white lg:flex" :title="collapsed ? 'Perbesar sidebar' : 'Ciutkan sidebar'"><span x-text="collapsed ? '→' : '←'"></span></button>
        </div>
        <nav class="flex-1 space-y-2 p-4">
            <p x-show="!collapsed" class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Navigasi Utama</p>
            <a href="{{ route('atasan.dashboard') }}" title="Dashboard" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('atasan.dashboard') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}" :class="collapsed ? 'justify-center' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10M9 20v-6h6v6"/></svg><span x-show="!collapsed">Dashboard</span>
            </a>
            <a href="{{ route('atasan.evidence') }}" title="Eviden Valid" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('atasan.evidence') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}" :class="collapsed ? 'justify-center' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4zM8 9h8M8 13h5"/></svg><span x-show="!collapsed">Eviden Valid</span>
            </a>
            <a href="{{ route('atasan.status.summary') }}" title="Rekap Status Dokumen" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('atasan.status.summary') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}" :class="collapsed ? 'justify-center' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16M4 10h16M4 15h16M4 20h16"/></svg><span x-show="!collapsed">Rekap Status</span>
            </a>
            <a href="{{ route('atasan.activity') }}" title="Riwayat Aktivitas" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('atasan.activity') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}" :class="collapsed ? 'justify-center' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V5m0 14h16M8 16v-3m4 3V8m4 8v-6"/></svg><span x-show="!collapsed">Riwayat Aktivitas</span>
            </a>
            <a href="{{ route('atasan.users') }}" title="Manajemen User" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('atasan.users*') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}" :class="collapsed ? 'justify-center' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m9-10a4 4 0 100-8 4 4 0 000 8m7-5v6m3-3h-6"/></svg><span x-show="!collapsed">Manajemen User</span>
            </a>
            <a href="{{ route('atasan.export') }}" title="Export Laporan" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition {{ request()->routeIs('atasan.export') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}" :class="collapsed ? 'justify-center' : ''">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg><span x-show="!collapsed">Export Laporan</span>
            </a>
        </nav>
    </aside>
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" style="display:none">
        <div class="absolute inset-0 bg-slate-950/60" @click="sidebarOpen = false"></div>
        <aside class="absolute left-0 top-0 flex h-full w-72 flex-col bg-ink p-4 text-white shadow-2xl">
            <div class="mb-8 flex items-center justify-between"><div class="flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400 font-black text-ink">M</div><div><p class="text-sm font-bold">MATLEV K3</p><p class="text-xs text-cyan-200">Executive Workspace</p></div></div><button @click="sidebarOpen = false" class="text-2xl text-slate-300" aria-label="Tutup menu">&times;</button></div>
            <nav class="space-y-2"><a href="{{ route('atasan.dashboard') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('atasan.dashboard') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}">Dashboard</a><a href="{{ route('atasan.evidence') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('atasan.evidence') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}">Eviden Valid</a><a href="{{ route('atasan.status.summary') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('atasan.status.summary') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}">Rekap Status</a><a href="{{ route('atasan.activity') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('atasan.activity') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}">Riwayat Aktivitas</a><a href="{{ route('atasan.users') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('atasan.users*') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}">Manajemen User</a><a href="{{ route('atasan.export') }}" class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('atasan.export') ? 'bg-cyan-400 text-ink' : 'text-slate-200 hover:bg-white/10' }}">Export Laporan</a></nav>
        </aside>
    </div>
    <main class="min-w-0 flex-1">
        <div class="flex items-center justify-between border-b border-slate-200 bg-white/95 px-4 py-4 shadow-sm backdrop-blur sm:px-6 lg:px-10">
            <button @click="sidebarOpen = true" class="rounded-lg border border-slate-200 p-2 text-slate-700 lg:hidden" aria-label="Buka menu"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            <div class="ml-auto flex items-center gap-3"><span class="hidden text-right sm:block"><span class="block text-xs font-bold text-slate-800">{{ Auth::user()->name ?? 'Atasan' }}</span><span class="block text-[10px] uppercase tracking-wider text-slate-500">Atasan / Auditor</span></span><span class="flex h-9 w-9 items-center justify-center rounded-full bg-cyan-100 text-sm font-black text-ocean">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700" title="Logout"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 8l4 4m0 0l-4 4m4-4H9m4-4V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h5"/></svg><span class="hidden sm:inline">Logout</span></button></form></div>
        </div>
        <div class="mx-auto max-w-[1500px] p-4 sm:p-6 lg:p-10">@yield('content')</div>
    </main>
</div>
</body>
</html>
