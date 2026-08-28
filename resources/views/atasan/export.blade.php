@extends('layouts.atasan')

@section('title', 'Export Laporan')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <header>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-ocean">Pusat Laporan</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-ink">Export Laporan</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Pilih periode tanggal upload dokumen yang ingin dimasukkan ke dalam laporan.</p>
    </header>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg shadow-slate-900/5">
        <div class="border-b border-slate-100 bg-skywash px-6 py-5 sm:px-8">
            <h2 class="font-display text-xl font-bold text-ink">Periode Laporan</h2>
            <p class="mt-1 text-xs text-slate-500">Tanggal akhir termasuk dalam hasil laporan.</p>
        </div>
        <div class="p-6 sm:p-8">
            <form method="GET" action="{{ route('atasan.export.summary') }}" class="space-y-6" x-data="{ fromDate: '', toDate: '' }">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <label class="block"><span class="text-xs font-bold uppercase tracking-wider text-slate-600">Dari tanggal</span><input type="date" name="from_date" x-model="fromDate" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-ocean focus:ring-2 focus:ring-cyan-100"></label>
                    <label class="block"><span class="text-xs font-bold uppercase tracking-wider text-slate-600">Sampai tanggal</span><input type="date" name="to_date" x-model="toDate" :min="fromDate" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-ocean focus:ring-2 focus:ring-cyan-100"></label>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-600">Kosongkan kedua tanggal untuk mengekspor seluruh dokumen.</div>
                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <button type="submit" formaction="{{ route('atasan.export.pdf') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>Export PDF</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-ocean px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-800"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14"/></svg>Export Excel / CSV</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
