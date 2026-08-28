@extends('layouts.atasan')

@section('title', 'Eviden Valid')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Eviden Valid</p>
            <h1 class="text-3xl font-bold mt-2">Daftar Dokumen yang Telah Disetujui</h1>
        </div>
    </div>

    <div class="space-y-4">
        @foreach($uploads as $upload)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex flex-col md:flex-row justify-between gap-3">
                    <div>
                        <div class="font-bold text-lg">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                        <div class="text-sm text-slate-500">{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                    </div>
                    <div class="flex gap-3 items-center">
                        <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Approved</span>
                        <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">Preview</a>
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
