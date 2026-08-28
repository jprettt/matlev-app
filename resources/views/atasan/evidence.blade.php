@extends('layouts.atasan')

@section('title', 'Eviden Valid')

@section('content')
<div class="space-y-6">
    <div class="border-b border-slate-200 pb-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Audit Ready</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-ink">Eviden Valid</h1>
            <p class="mt-2 text-sm text-slate-600">Dokumen yang telah disetujui dan siap digunakan untuk kebutuhan audit.</p>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($uploads as $upload)
            <div class="group rounded-2xl border border-slate-200 border-l-4 border-l-emerald-500 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex flex-col md:flex-row justify-between gap-3">
                    <div>
                        <div class="font-display text-lg font-bold text-ink">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                        <div class="text-sm text-slate-500">{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                    </div>
                    <div class="flex gap-3 items-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Approved</span>
                        <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="rounded-lg bg-ocean px-3 py-2 text-sm font-semibold text-white transition hover:bg-cyan-800">Preview</a>
                    </div>
                </div>
                <div class="mt-3 text-sm text-slate-600">
                    <div>Uploader: {{ $upload->user->name ?? '-' }}</div>
                    <div>Unit: {{ $upload->user->unit_kerja ?? '-' }}</div>
                    <div>Nama file: {{ $upload->original_filename }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
