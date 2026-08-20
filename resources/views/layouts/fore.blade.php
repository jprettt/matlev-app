<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Maturity Level K3') - PT PLN (Persero)</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
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
                            950: '#172554',
                        },
                        accent: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #ffffff;
            color: #1e293b;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-pln-500 selection:text-white" x-data="{ mobileMenuOpen: false }">

    <!-- 1. TOP NAVBAR (Blue PLN Theme) -->
    <header class="bg-pln-900 sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand / Logo PLN -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/pln transparan.png') }}" alt="Logo PLN" class="h-10 w-auto object-contain transition-transform group-hover:scale-105 duration-200">
                        <div class="hidden sm:block leading-tight">
                            <span class="block text-white font-extrabold text-base tracking-tight font-display">PLN MATLEV</span>
                            <span class="block text-[10px] font-medium text-pln-300 tracking-wider uppercase">K3 & Keamanan Kerja</span>
                        </div>
                    </a>
                </div>

                <!-- Center Navigation Menu -->
                <nav class="hidden lg:flex items-center space-x-1">
                    <a href="{{ route('user.dashboard') }}" 
                       class="px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-all duration-200 {{ request()->routeIs('user.dashboard') ? 'text-white bg-white/15 font-bold' : 'text-pln-200 hover:text-white hover:bg-white/10' }}">
                        Beranda
                    </a>

                    <a href="{{ route('user.kriteria') }}" 
                       class="px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-all duration-200 {{ request()->routeIs('user.kriteria') || request()->routeIs('user.upload') ? 'text-white bg-white/15 font-bold' : 'text-pln-200 hover:text-white hover:bg-white/10' }}">
                        Daftar Kriteria & Upload
                    </a>

                    <a href="{{ route('user.history') }}" 
                       class="px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-all duration-200 {{ request()->routeIs('user.history') ? 'text-white bg-white/15 font-bold' : 'text-pln-200 hover:text-white hover:bg-white/10' }}">
                        Riwayat Aktivitas
                    </a>

                    <a href="{{ route('user.panduan') }}" 
                       class="px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-all duration-200 {{ request()->routeIs('user.panduan') ? 'text-white bg-white/15 font-bold' : 'text-pln-200 hover:text-white hover:bg-white/10' }}">
                        Panduan
                    </a>

                    <a href="{{ route('user.revisi') }}" 
                       class="px-3.5 py-1.5 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-1.5 {{ request()->routeIs('user.revisi') ? 'text-accent-300 bg-white/15 font-bold' : 'text-pln-200 hover:text-accent-300 hover:bg-white/10' }}">
                        <span>Perlu Revisi</span>
                        @if(isset($stats['totalRejected']) && $stats['totalRejected'] > 0)
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-pln-900 bg-accent-400 rounded-full animate-pulse">
                                {{ $stats['totalRejected'] }}
                            </span>
                        @endif
                    </a>
                </nav>

                <!-- Right Side: User & Logout -->
                <div class="hidden md:flex items-center space-x-3">
                    <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg text-xs">
                        <div class="w-6 h-6 rounded-full bg-accent-400 text-pln-900 flex items-center justify-center font-bold text-[10px]">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="font-medium text-white max-w-[100px] truncate">{{ Auth::user()->name ?? 'User' }}</span>
                        <span class="bg-accent-400/20 text-accent-300 text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">
                            {{ Auth::user()->role ?? 'User' }}
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="px-3.5 py-1.5 text-xs font-bold text-white bg-white/10 border border-white/20 rounded-lg hover:bg-rose-600 hover:border-rose-600 transition-all duration-200">
                            Logout
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-white p-2 rounded-lg focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Navigation Dropdown -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden bg-pln-800 border-t border-pln-700 px-4 pt-2 pb-4 space-y-1">
            <a href="{{ route('user.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('user.dashboard') ? 'bg-white/15 text-white' : 'text-pln-200' }}">Beranda</a>
            <a href="{{ route('user.kriteria') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('user.kriteria') ? 'bg-white/15 text-white' : 'text-pln-200' }}">Daftar Kriteria & Upload</a>
            <a href="{{ route('user.history') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('user.history') ? 'bg-white/15 text-white' : 'text-pln-200' }}">Riwayat Aktivitas</a>
            <a href="{{ route('user.panduan') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('user.panduan') ? 'bg-white/15 text-white' : 'text-pln-200' }}">Panduan</a>
            <a href="{{ route('user.revisi') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('user.revisi') ? 'bg-white/15 text-accent-300' : 'text-pln-200' }}">
                <span>Perlu Revisi</span>
                @if(isset($stats['totalRejected']) && $stats['totalRejected'] > 0)
                    <span class="bg-accent-400 text-pln-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ $stats['totalRejected'] }}</span>
                @endif
            </a>
            <div class="pt-3 border-t border-pln-700 flex items-center justify-between">
                <span class="text-xs text-pln-300 font-medium">{{ Auth::user()->name ?? 'User' }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-rose-400">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- 2. YELLOW ACCENT ANNOUNCEMENT BAR -->
    <div class="bg-accent-400 text-pln-900 py-2 px-4 text-xs sm:text-sm font-medium">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-pln-900 animate-pulse"></span>
                <span><strong>Sistem Informasi Maturity Level K3</strong> • Pastikan seluruh eviden diunggah sesuai kriteria standar PLN</span>
            </div>
            <a href="{{ route('user.panduan') }}" class="bg-pln-900 hover:bg-pln-800 text-white font-bold px-3 py-1 rounded-full text-xs transition">
                Panduan &rarr;
            </a>
        </div>
    </div>

    <!-- 3. FLASH NOTIFICATIONS -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm mb-4" role="alert">
                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">✓</div>
                <div class="text-xs sm:text-sm font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-300 text-rose-900 px-4 py-3 rounded-xl flex items-center gap-3 shadow-sm mb-4" role="alert">
                <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 font-bold text-sm">!</div>
                <div class="text-xs sm:text-sm font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-3 rounded-xl shadow-sm mb-4" role="alert">
                <ul class="list-disc pl-5 text-xs space-y-0.5 text-amber-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- 4. MAIN CONTENT -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- 5. FOOTER -->
    <footer class="bg-pln-950 text-white/70 py-10 text-xs mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/pln transparan.png') }}" alt="PLN Logo" class="h-8 w-auto opacity-70">
                <div>
                    <p class="font-bold text-white font-display">PT PLN (Persero)</p>
                    <p class="text-white/50">Portal Evaluasi Maturity Level K3 & Keselamatan Operasional</p>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-6 font-medium text-white/50">
                <a href="{{ route('user.dashboard') }}" class="hover:text-accent-400 transition">Beranda</a>
                <a href="{{ route('user.kriteria') }}" class="hover:text-accent-400 transition">Daftar Kriteria</a>
                <a href="{{ route('user.history') }}" class="hover:text-accent-400 transition">Riwayat</a>
                <a href="{{ route('user.revisi') }}" class="hover:text-accent-400 transition">Perlu Revisi</a>
                <a href="{{ route('user.panduan') }}" class="hover:text-accent-400 transition">Panduan</a>
            </div>

            <p class="text-white/40 text-center md:text-right">
                &copy; {{ date('Y') }} PT PLN (Persero). All Rights Reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
