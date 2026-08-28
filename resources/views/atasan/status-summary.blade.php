@extends('layouts.atasan')

@section('title', 'Rekap Status Dokumen')

@section('content')
<div class="space-y-6">
    <header>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-ocean">Monitoring Dokumen</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-ink">Rekapitulasi Status</h1>
        <p class="mt-2 text-sm text-slate-600">Ringkasan seluruh dokumen berdasarkan kriteria, pengguna, status, dan waktu upload.</p>
    </header>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach([['label' => 'Total Dokumen', 'value' => $uploads->count(), 'color' => 'text-ink', 'bg' => 'bg-white'], ['label' => 'Disetujui', 'value' => $uploads->where('status', 'approved')->count(), 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50'], ['label' => 'Menunggu / Revisi', 'value' => $uploads->whereIn('status', ['pending', 'rejected'])->count(), 'color' => 'text-amber-700', 'bg' => 'bg-amber-50']] as $stat)
            <div class="rounded-2xl border border-slate-200 {{ $stat['bg'] }} p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-black {{ $stat['color'] }}">{{ $stat['value'] }}</p><div class="mt-3 h-1 w-12 rounded-full bg-current opacity-30 {{ $stat['color'] }}"></div></div>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4"><div><h2 class="font-display text-xl font-bold text-ink">Daftar Dokumen</h2><p class="mt-1 text-xs text-slate-500">Diurutkan dari upload terbaru.</p></div><span class="rounded-full bg-skywash px-3 py-1 text-xs font-bold text-ocean">{{ $uploads->count() }} dokumen</span></div>
        <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3 text-left">Kriteria</th><th class="px-5 py-3 text-left">Sub Kriteria</th><th class="px-5 py-3 text-left">User</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Tanggal</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($uploads as $upload)
                <tr class="transition hover:bg-skywash/40"><td class="px-5 py-4 font-semibold text-slate-800">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</td><td class="px-5 py-4 text-slate-600">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}<span class="ml-1 text-xs text-slate-400">/ L{{ $upload->maturityLevel->level ?? '-' }}</span></td><td class="px-5 py-4 text-slate-600">{{ $upload->user->name ?? '-' }}</td><td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold @if($upload->status === 'approved') bg-emerald-100 text-emerald-700 @elseif($upload->status === 'rejected') bg-rose-100 text-rose-700 @else bg-amber-100 text-amber-700 @endif">{{ $upload->status === 'approved' ? 'Disetujui' : ($upload->status === 'rejected' ? 'Perlu Revisi' : 'Menunggu') }}</span></td><td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $upload->uploaded_at ? $upload->uploaded_at->timezone(config('app.timezone'))->format('d-m-Y H:i') . ' WITA' : '-' }}</td></tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Belum ada dokumen.</td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
