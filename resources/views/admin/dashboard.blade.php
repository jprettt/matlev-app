@extends('layouts.admin')

@section('title', 'Dashboard Verifikator')

@section('content')
@php
    $total = $pendingCount + $approvedCount + $rejectedCount;
    $pendingPct = $total > 0 ? round(($pendingCount / $total) * 100, 2) : 0;
    $approvedPct = $total > 0 ? round(($approvedCount / $total) * 100, 2) : 0;
    $rejectedPct = $total > 0 ? round(($rejectedCount / $total) * 100, 2) : 0;
    $donutStyle = "background: conic-gradient(#facc15 0% {$pendingPct}%, #22c55e {$pendingPct}% " . ($pendingPct + $approvedPct) . "%, #ef4444 " . ($pendingPct + $approvedPct) . "% 100%);";
@endphp

<div class="space-y-6">
    <div class="bg-white border border-stone-200 rounded-3xl p-6 sm:p-7 shadow-sm">
        <p class="text-xs uppercase tracking-[0.2em] text-pln-700 font-bold">Admin Master Data & Evaluator</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-display text-stone-900 mt-2">Dashboard Verifikasi Dokumen</h1>
        <p class="text-sm text-stone-500 mt-2">Pantau beban kerja harian verifikator dan dokumen yang butuh tindak lanjut prioritas.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-xs text-amber-800 font-bold uppercase tracking-wide">Menunggu Verifikasi</p>
            <p class="text-3xl font-extrabold text-amber-900 mt-2">{{ $pendingCount }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
            <p class="text-xs text-emerald-800 font-bold uppercase tracking-wide">Disetujui</p>
            <p class="text-3xl font-extrabold text-emerald-900 mt-2">{{ $approvedCount }}</p>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
            <p class="text-xs text-rose-800 font-bold uppercase tracking-wide">Ditolak / Revisi</p>
            <p class="text-3xl font-extrabold text-rose-900 mt-2">{{ $rejectedCount }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
            <p class="text-xs text-blue-800 font-bold uppercase tracking-wide">Rata-Rata SLA</p>
            <p class="text-3xl font-extrabold text-blue-900 mt-2">{{ $avgSlaHours }} jam</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <section class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm xl:col-span-1">
            <h2 class="text-lg font-extrabold font-display text-stone-900">Grafik Ringkasan Evaluasi</h2>
            <p class="text-xs text-stone-500 mt-1">Perbandingan dokumen pending, disetujui, dan ditolak.</p>

            <div class="mt-6 flex flex-col items-center gap-5">
                <div class="w-52 h-52 rounded-full relative" style="{{ $donutStyle }}">
                    <div class="absolute inset-8 bg-white rounded-full border border-stone-100 flex flex-col items-center justify-center">
                        <p class="text-[11px] text-stone-500">Total Dokumen</p>
                        <p class="text-3xl font-extrabold text-stone-900">{{ $total }}</p>
                    </div>
                </div>

                <div class="w-full space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span><span>Pending</span></div>
                        <span class="font-bold">{{ $pendingCount }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span>Approved</span></div>
                        <span class="font-bold">{{ $approvedCount }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500"></span><span>Rejected</span></div>
                        <span class="font-bold">{{ $rejectedCount }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-extrabold font-display text-stone-900">Antrean Prioritas</h2>
                    <p class="text-xs text-stone-500">Daftar berkas pending paling lama untuk diproses terlebih dahulu.</p>
                </div>
                <a href="{{ route('admin.queue') }}" class="px-3 py-2 text-xs font-bold rounded-lg bg-pln-700 text-white hover:bg-pln-800 transition">Buka Antrean Verifikasi</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-stone-100 text-stone-600 text-[11px] uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal Upload</th>
                            <th class="px-4 py-3 text-left">Pengunggah</th>
                            <th class="px-4 py-3 text-left">Kriteria</th>
                            <th class="px-4 py-3 text-left">Berkas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($recentPendingUploads as $upload)
                            <tr>
                                <td class="px-4 py-3 text-xs text-stone-500 whitespace-nowrap">{{ $upload->uploaded_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-stone-800">{{ $upload->user->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs text-stone-600">
                                    <div class="font-semibold text-stone-800">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                                    <div>{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs"><a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-pln-700 hover:text-pln-900 underline">{{ $upload->original_filename }}</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-stone-400">Tidak ada dokumen pending saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection