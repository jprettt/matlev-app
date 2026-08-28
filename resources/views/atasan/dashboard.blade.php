@extends('layouts.atasan')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <section class="relative overflow-hidden rounded-[2rem] bg-ink px-6 py-8 text-white shadow-xl shadow-slate-900/10 sm:px-9 sm:py-10">
        <div class="absolute -right-16 -top-24 h-64 w-64 rounded-full border-[28px] border-cyan-400/15"></div>
        <div class="relative max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.24em] text-cyan-300">Atasan / Auditor / Manajemen</p><h1 class="mt-3 font-display text-4xl leading-tight sm:text-5xl">Executive Dashboard K3</h1><p class="mt-3 max-w-xl text-sm leading-6 text-slate-300">Satu ruang kendali untuk membaca capaian, memantau dokumen, dan mengambil keputusan dengan cepat.</p></div>
    </section>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 border-l-4 border-l-ocean bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Dokumen</p><span class="rounded-lg bg-skywash px-2 py-1 text-ocean">◼</span></div><p class="mt-3 text-3xl font-black text-ink">{{ $total }}</p><p class="mt-1 text-xs text-slate-400">Seluruh dokumen masuk</p>
        </div>
        <div class="rounded-2xl border border-slate-200 border-l-4 border-l-emerald-500 bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Approved</p><span class="rounded-lg bg-emerald-50 px-2 py-1 text-emerald-600">✓</span></div><p class="mt-3 text-3xl font-black text-emerald-700">{{ $approved }}</p><p class="mt-1 text-xs text-slate-400">Dokumen tervalidasi</p>
        </div>
        <div class="rounded-2xl border border-slate-200 border-l-4 border-l-amber-500 bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pending</p><span class="rounded-lg bg-amber-50 px-2 py-1 text-amber-600">◷</span></div><p class="mt-3 text-3xl font-black text-amber-700">{{ $pending }}</p><p class="mt-1 text-xs text-slate-400">Menunggu verifikasi</p>
        </div>
        <div class="rounded-2xl border border-slate-200 border-l-4 border-l-rose-500 bg-white p-5 shadow-sm"><div class="flex items-start justify-between"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rejected</p><span class="rounded-lg bg-rose-50 px-2 py-1 text-rose-600">!</span></div><p class="mt-3 text-3xl font-black text-rose-700">{{ $rejected }}</p><p class="mt-1 text-xs text-slate-400">Perlu tindak lanjut</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-ocean">Performance</p><h2 class="mt-1 font-display text-2xl font-bold text-ink">Kepatuhan per Kategori</h2></div><span class="text-xs text-slate-400">Target K3</span></div>
            <div class="space-y-4">
                @foreach($chart as $item)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['percent'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full bg-ocean" style="width: {{ $item['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-end justify-between"><div><p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Audit Ready</p><h2 class="mt-1 font-display text-2xl font-bold text-ink">Dokumen Valid</h2></div><a href="{{ route('atasan.evidence') }}" class="text-xs font-bold text-ocean hover:underline">Lihat semua</a></div>
            <div class="space-y-3">
                @foreach($uploads->where('status', 'approved')->take(6) as $upload)
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <div class="font-semibold">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}</div>
                        </div>
                        <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="shrink-0 rounded-lg bg-skywash px-3 py-1.5 text-xs font-bold text-ocean transition hover:bg-cyan-100">Preview</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
