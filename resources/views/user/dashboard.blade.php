@extends('layouts.fore')

@section('title', 'Beranda & Dashboard')

@section('content')
<div x-data="{
    activeSlide: 1,
    totalSlides: 3,
    criteriaSlide: 1,
    totalCriteriaSlides: {{ $criterias->count() }},
    timer: null,
    startAutoPlay() {
        this.timer = setInterval(() => { this.nextSlide(); }, 6000);
    },
    stopAutoPlay() {
        if (this.timer) clearInterval(this.timer);
    },
    nextSlide() {
        this.activeSlide = this.activeSlide === this.totalSlides ? 1 : this.activeSlide + 1;
    },
    prevSlide() {
        this.activeSlide = this.activeSlide === 1 ? this.totalSlides : this.activeSlide - 1;
    },
    nextCriteria() {
        this.criteriaSlide = this.criteriaSlide === this.totalCriteriaSlides ? 1 : this.criteriaSlide + 1;
    },
    prevCriteria() {
        this.criteriaSlide = this.criteriaSlide === 1 ? this.totalCriteriaSlides : this.criteriaSlide - 1;
    },
    scrollToIkhtisar() {
        document.getElementById('ikhtisar-kriteria')?.scrollIntoView({ behavior: 'smooth' });
    }
}" x-init="startAutoPlay()">

    <!-- ======================================================== -->
    <!-- FULL-SCREEN HERO CAROUSEL (EDGE TO EDGE, NO CONTAINER) -->
    <!-- ======================================================== -->
    <div class="relative w-full overflow-hidden bg-white"
         style="min-height: calc(100vh - 106px);"
         @mouseenter="stopAutoPlay()" 
         @mouseleave="startAutoPlay()">
        
        <!-- Background: subtle blue gradient on left, yellow sparkle on right -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-0 w-1/2 h-full bg-gradient-to-br from-pln-50/60 via-white to-transparent"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-accent-100/40 rounded-full blur-3xl translate-x-24 translate-y-24"></div>
            <div class="absolute top-12 right-1/4 w-64 h-64 bg-pln-100/30 rounded-full blur-3xl"></div>
        </div>

        <!-- Left Arrow -->
        <button @click="prevSlide()" 
                class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white shadow-lg border border-gray-200 flex items-center justify-center transition-all duration-200 hover:scale-110 hover:shadow-xl active:scale-95 group focus:outline-none">
            <svg class="w-5 h-5 text-pln-900 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        <!-- Right Arrow -->
        <button @click="nextSlide()" 
                class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white shadow-lg border border-gray-200 flex items-center justify-center transition-all duration-200 hover:scale-110 hover:shadow-xl active:scale-95 group focus:outline-none">
            <svg class="w-5 h-5 text-pln-900 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- SLIDES WRAPPER (CENTERED) -->
        <div class="relative z-10 flex items-center justify-center w-full" style="min-height: calc(100vh - 106px);">
            <div class="w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-12">

                <!-- SLIDE 1: SELAMAT DATANG -->
                <div x-show="activeSlide === 1" 
                     x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-12" x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-12"
                     class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
                    
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-pln-100 text-pln-800 text-xs font-bold tracking-wide">
                            <span class="w-2.5 h-2.5 rounded-full bg-pln-600 animate-pulse"></span>
                            PORTAL RESMI EVALUASI K3 PLN
                        </div>
                        
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 font-display tracking-tight leading-[1.1]">
                            Selamat Datang, <br>
                            <span class="text-pln-700">{{ Auth::user()->name ?? 'Rekan Kerja PLN' }}</span>
                        </h1>
                        
                        <p class="text-gray-500 text-base sm:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0">
                            Kelola dan lengkapi bukti dokumen eviden indikator kematangan K3 untuk mendukung keselamatan operasional terbaik di PT PLN (Persero).
                        </p>

                        <div class="pt-2 flex flex-wrap gap-3 justify-center lg:justify-start">
                            <a href="{{ route('user.kriteria') }}" 
                               class="px-7 py-3.5 rounded-lg bg-pln-700 hover:bg-pln-800 text-white font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2">
                                <span>Daftar Kriteria & Upload</span>
                                <span>&rarr;</span>
                            </a>
                            <a href="{{ route('user.panduan') }}" 
                               class="px-7 py-3.5 rounded-lg bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-200 font-bold text-sm transition-all duration-200">
                                Panduan Pengisian
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-5 flex justify-center">
                        <div class="bg-pln-900 p-8 rounded-2xl text-white shadow-2xl max-w-md w-full relative overflow-hidden">
                            <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-pln-700/50 rounded-full blur-2xl pointer-events-none"></div>
                            <div class="absolute top-4 right-4 w-16 h-1 bg-accent-400 rounded-full"></div>
                            
                            <div class="flex items-center justify-between mb-5">
                                <span class="text-xs font-semibold uppercase tracking-widest text-pln-300">Ringkasan Nilai Kriteria</span>
                                <span class="bg-accent-400 text-pln-900 text-[10px] font-bold px-2.5 py-1 rounded uppercase">0–5</span>
                            </div>
                            <div class="relative min-h-[250px] overflow-hidden">
                                @foreach($criterias as $criteria)
                                    @php $criteriaScore = $criteria->scoreForUser(); $criteriaPercent = round(($criteriaScore / 5) * 100); @endphp
                                    <div x-cloak x-show="criteriaSlide === {{ $loop->iteration }}" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-5" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-5" class="absolute inset-0 flex min-h-[250px] flex-col">
                                        <p class="text-3xl font-extrabold text-white">{{ $criteria->code }}</p>
                                        <h3 class="mt-3 min-h-[56px] font-display text-xl font-extrabold leading-tight text-white">{{ $criteria->title }}</h3>
                                        <div class="mt-auto">
                                            <div class="flex items-end justify-between gap-3"><span class="text-xs text-pln-300">Nilai Kriteria</span><span class="text-3xl font-extrabold text-accent-400">{{ number_format($criteriaScore, 2) }}<span class="text-sm text-pln-300"> / 5</span></span></div>
                                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-accent-400" style="width: {{ $criteriaPercent }}%"></div></div>
                                            <a href="{{ route('user.kriteria', ['criteria_id' => $criteria->id]) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-accent-400 px-4 py-3 text-sm font-extrabold text-pln-950 transition hover:bg-accent-300">Buka {{ $criteria->code }} <span class="ml-2">&rarr;</span></a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-5 flex items-center justify-between border-t border-white/15 pt-4">
                                <button type="button" @click="prevCriteria()" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20 text-lg text-white transition hover:bg-white/10" aria-label="Kriteria sebelumnya">&larr;</button>
                                <div class="flex items-center gap-1.5">@foreach($criterias as $criteria)<button type="button" @click="criteriaSlide={{ $loop->iteration }}" :class="criteriaSlide === {{ $loop->iteration }} ? 'bg-accent-400' : 'bg-white/30'" class="h-1.5 w-5 rounded-full" aria-label="Kriteria {{ $criteria->code }}"></button>@endforeach</div>
                                <button type="button" @click="nextCriteria()" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/20 text-lg text-white transition hover:bg-white/10" aria-label="Kriteria berikutnya">&rarr;</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2: STATUS DOKUMEN -->
                <div x-show="activeSlide === 2" 
                     x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-12" x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-12"
                     class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
                    
                    <div class="lg:col-span-5 space-y-5 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-accent-100 text-accent-700 text-xs font-bold tracking-wide">
                            <span class="w-2.5 h-2.5 rounded-full bg-accent-500"></span>
                            RINGKASAN STATUS EVIDEN
                        </div>
                        
                        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 font-display tracking-tight leading-[1.1]">
                            Status Verifikasi <span class="text-pln-700">Berkas</span>
                        </h2>
                        
                        <p class="text-gray-500 text-base leading-relaxed">
                            Evaluasi dilakukan oleh tim penilai K3. Berkas yang disetujui berkontribusi langsung pada skor maturitas unit kerja.
                        </p>

                        <div class="pt-2 flex flex-wrap gap-3 justify-center lg:justify-start">
                            <a href="{{ route('user.history') }}" class="px-6 py-3 rounded-lg bg-pln-700 hover:bg-pln-800 text-white font-bold text-sm shadow-md transition">
                                Lihat Riwayat &rarr;
                            </a>
                            @if($stats['totalRejected'] > 0)
                                <a href="{{ route('user.revisi') }}" class="px-6 py-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm shadow-md transition">
                                    Perbaiki {{ $stats['totalRejected'] }} File &rarr;
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            
                            <!-- Disetujui -->
                            <div class="bg-white border-2 border-emerald-200 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-shadow">
                                <div class="flex items-center justify-between mb-5">
                                    <span class="text-xs font-extrabold uppercase text-emerald-700 tracking-wider">Disetujui</span>
                                    <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">✓</span>
                                </div>
                                <p class="text-4xl font-extrabold text-gray-900 font-display">{{ $stats['totalApproved'] }}</p>
                                <p class="text-xs text-gray-500 font-medium mt-1">File Terverifikasi</p>
                            </div>

                            <!-- Menunggu -->
                            <div class="bg-white border-2 border-accent-200 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-shadow">
                                <div class="flex items-center justify-between mb-5">
                                    <span class="text-xs font-extrabold uppercase text-accent-700 tracking-wider">Menunggu</span>
                                    <span class="w-10 h-10 rounded-xl bg-accent-100 text-accent-700 flex items-center justify-center font-bold text-sm">⏳</span>
                                </div>
                                <p class="text-4xl font-extrabold text-gray-900 font-display">{{ $stats['totalPending'] }}</p>
                                <p class="text-xs text-gray-500 font-medium mt-1">Sedang Dievaluasi</p>
                            </div>

                            <!-- Perlu Revisi -->
                            <div class="bg-white border-2 border-rose-200 p-6 rounded-2xl shadow-sm hover:shadow-lg transition-shadow">
                                <div class="flex items-center justify-between mb-5">
                                    <span class="text-xs font-extrabold uppercase text-rose-700 tracking-wider">Perlu Revisi</span>
                                    <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm">✕</span>
                                </div>
                                <p class="text-4xl font-extrabold text-gray-900 font-display">{{ $stats['totalRejected'] }}</p>
                                <p class="text-xs text-gray-500 font-medium mt-1">Butuh Perbaikan</p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SLIDE 3: PROGRESS MATURITY -->
                <div x-show="activeSlide === 3" 
                     x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-12" x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-12"
                     class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
                    
                    <div class="lg:col-span-7 space-y-5 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-pln-100 text-pln-800 text-xs font-bold tracking-wide">
                            <span class="w-2.5 h-2.5 rounded-full bg-pln-600"></span>
                            PROGRESS KEMATANGAN K3
                        </div>
                        
                        <h2 class="text-4xl sm:text-5xl font-extrabold text-gray-900 font-display tracking-tight leading-[1.1]">
                            Pencapaian <span class="text-pln-700">Maturity Level</span>
                        </h2>
                        
                        <p class="text-gray-500 text-base leading-relaxed">
                            Total <strong class="text-gray-700">{{ $stats['totalApproved'] }}</strong> dari <strong class="text-gray-700">{{ $stats['totalSlots'] }}</strong> slot level indikator telah dinyatakan lengkap dan valid.
                        </p>

                        <!-- Progress Bar -->
                        <div class="space-y-3 pt-2 max-w-xl">
                            <div class="flex justify-between items-center text-sm font-bold text-gray-600">
                                <span>Status Kelengkapan</span>
                                <span class="text-pln-700 text-2xl">{{ $stats['globalPercent'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-5 overflow-hidden border border-gray-200">
                                <div class="bg-gradient-to-r from-pln-600 to-pln-400 h-5 rounded-full transition-all duration-700 flex items-center justify-end pr-2" 
                                     style="width: {{ max($stats['globalPercent'], 3) }}%">
                                    @if($stats['globalPercent'] > 10)
                                        <span class="text-[10px] font-bold text-white">{{ $stats['globalPercent'] }}%</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-5 flex justify-center">
                        <div class="bg-white border-2 border-gray-200 p-8 rounded-2xl text-center shadow-lg w-full max-w-sm space-y-5">
                            <div class="w-28 h-28 rounded-full bg-pln-700 text-white mx-auto flex items-center justify-center font-extrabold text-4xl font-display shadow-lg">
                                {{ $stats['globalPercent'] }}<span class="text-lg text-pln-300">%</span>
                            </div>
                            <div>
                                <p class="font-extrabold text-gray-900 text-lg">Tingkat Kepatuhan K3</p>
                                <p class="text-xs text-gray-500 mt-1">Berdasarkan eviden terverifikasi evaluator</p>
                            </div>
                            <div class="flex items-center justify-center gap-2 text-xs text-gray-400">
                                <span class="w-3 h-3 rounded-full bg-accent-400"></span>
                                <span>Target: 100% kelengkapan semua level</span>
                            </div>
                            <a href="{{ route('user.kriteria') }}" class="inline-block w-full py-3 bg-pln-700 hover:bg-pln-800 text-white rounded-lg text-sm font-bold transition shadow-md">
                                Lengkapi Dokumen &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- BOTTOM BAR: DOTS + SCROLL DOWN ARROW -->
        <div class="absolute bottom-0 left-0 right-0 z-20 px-6 sm:px-10 pb-6 flex items-end justify-between">
            
            <div class="w-40 hidden sm:block"></div>

            <!-- Dots -->
            <div class="flex items-center justify-center gap-2.5">
                <button @click="activeSlide = 1" 
                        :class="activeSlide === 1 ? 'w-10 bg-pln-700' : 'w-3 bg-gray-300 hover:bg-gray-400'"
                        class="h-3 rounded-full transition-all duration-300 focus:outline-none"></button>
                <button @click="activeSlide = 2" 
                        :class="activeSlide === 2 ? 'w-10 bg-pln-700' : 'w-3 bg-gray-300 hover:bg-gray-400'"
                        class="h-3 rounded-full transition-all duration-300 focus:outline-none"></button>
                <button @click="activeSlide = 3" 
                        :class="activeSlide === 3 ? 'w-10 bg-pln-700' : 'w-3 bg-gray-300 hover:bg-gray-400'"
                        class="h-3 rounded-full transition-all duration-300 focus:outline-none"></button>
            </div>

            <!-- Scroll Down Arrow (Bottom Right) -->
            <div class="w-auto sm:w-40 flex justify-end">
                <button @click="scrollToIkhtisar()" 
                        class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-pln-900 px-4 py-2.5 rounded-lg border border-gray-200 shadow-lg hover:shadow-xl transition-all duration-200 group focus:outline-none">
                    <span class="text-xs font-bold hidden sm:inline text-gray-600 group-hover:text-pln-700">Ikhtisar Kriteria</span>
                    <svg class="w-4 h-4 animate-bounce text-pln-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Thin yellow accent line at bottom of carousel -->
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-accent-400 z-10"></div>
    </div>


    <!-- ======================================================== -->
    <!-- IKHTISAR KRITERIA MATURITY LEVEL K3 -->
    <!-- ======================================================== -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div id="ikhtisar-kriteria" class="bg-white p-6 sm:p-10 rounded-2xl border border-gray-200 shadow-sm space-y-6 scroll-mt-24">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-gray-200">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-pln-100 text-pln-800 text-xs font-bold mb-2">
                        <span>📋 IKHTISAR KATEGORI</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-gray-900 font-display">Ikhtisar Kriteria Maturity Level K3</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Daftar kelompok kriteria dan persentase kelengkapan bukti eviden</p>
                </div>
                <a href="{{ route('user.kriteria') }}" class="px-5 py-2.5 rounded-lg bg-pln-700 hover:bg-pln-800 text-white font-bold text-xs sm:text-sm transition shadow-md text-center">
                    Mulai Isi Semua Kriteria &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($criterias as $criteria)
                    @php
                        $critScore = $criteria->scoreForUser();
                        $critPercent = round(($critScore / 5) * 100);
                    @endphp

                    <div class="bg-gray-50 p-5 rounded-xl border border-gray-100 hover:border-pln-200 hover:bg-pln-50/30 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="space-y-1 md:max-w-lg">
                            <div class="flex items-center gap-2.5">
                                <span class="px-3 py-1 rounded-lg bg-pln-700 text-white font-extrabold text-xs">
                                    {{ $criteria->code ?? $criteria->kode ?? 'KRIT' }}
                                </span>
                                <h3 class="font-bold text-gray-900 text-base">
                                    {{ $criteria->title ?? $criteria->nama ?? 'Kriteria K3' }}
                                </h3>
                            </div>
                            <p class="text-xs text-gray-500">
                                {{ count($criteria->subKriterias) }} Sub Kriteria • Nilai Kriteria {{ number_format($critScore, 2) }} / 5
                            </p>
                        </div>

                        <div class="flex items-center gap-4 w-full md:w-72">
                            <div class="flex-grow space-y-1.5">
                                <div class="flex justify-between text-xs font-bold text-gray-500">
                                    <span>Pencapaian</span>
                                    <span class="text-pln-700">{{ $critPercent }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-pln-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $critPercent }}%"></div>
                                </div>
                            </div>
                            <a href="{{ route('user.kriteria') }}" class="px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:text-pln-700 hover:border-pln-400 text-xs font-bold transition shrink-0 shadow-xs">
                                Buka
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-gray-400 text-sm">
                        Belum ada data kriteria yang terdaftar.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
