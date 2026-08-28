@extends('layouts.atasan')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <div>
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Atasan / Auditor / Manajemen</p>
            <h1 class="text-3xl font-bold mt-2">Executive Dashboard K3</h1>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Dokumen</p>
            <p class="text-3xl font-bold mt-2">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Approved</p>
            <p class="text-3xl font-bold mt-2 text-emerald-600">{{ $approved }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pending</p>
            <p class="text-3xl font-bold mt-2 text-amber-600">{{ $pending }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Rejected</p>
            <p class="text-3xl font-bold mt-2 text-rose-600">{{ $rejected }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-xl font-bold mb-4">Persentase Kepatuhan K3 per Kategori</h2>
            <div class="space-y-4">
                @foreach($chart as $item)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['percent'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $item['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-xl font-bold mb-4">Dokumen Valid yang Siap Audit</h2>
            <div class="space-y-3">
                @foreach($uploads->where('status', 'approved')->take(6) as $upload)
                    <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                        <div>
                            <div class="font-semibold">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}</div>
                        </div>
                        <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-blue-700 underline text-sm">Preview</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
