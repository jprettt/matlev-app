@extends('layouts.fore')

@section('title', 'Daftar Kriteria & Upload Bukti')

@section('content')
@php
    $selectedLevel = (int) request('level');
    $selectedCriteria = $selectedLevel
        ? $criterias->first(fn ($criteria) => $criteria->subKriterias->flatMap(fn ($sub) => $sub->maturityLevels)->contains('id', $selectedLevel))
        : $criterias->firstWhere('id', (int) request('criteria_id'));
    $initialCriteria = (string) ($selectedCriteria?->id ?? $criterias->first()->id ?? '');
@endphp
<style>.criteria-tabs-scroll,.level-tabs-scroll{scrollbar-width:none;-ms-overflow-style:none}.criteria-tabs-scroll::-webkit-scrollbar,.level-tabs-scroll::-webkit-scrollbar{display:none}</style>
<div class="min-h-screen bg-slate-50/70 pb-12" x-data="{ activeCriteria: '{{ $initialCriteria }}', search: '' }">
<div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
<header class="flex flex-col justify-between gap-5 border-b border-slate-200 pb-7 md:flex-row md:items-end">
<div><p class="text-xs font-extrabold uppercase tracking-[.2em] text-pln-700">Evaluasi Maturity Level K3</p><h1 class="mt-2 font-display text-2xl font-extrabold text-slate-950 sm:text-3xl">Daftar Kriteria & Upload Dokumen</h1><p class="mt-2 text-sm text-slate-600">Pilih sub kriteria, level kematangan, dan lengkapi setiap evidence requirement.</p></div>
<label class="relative w-full md:w-80"><span class="sr-only">Cari kriteria atau subkriteria</span><input type="search" x-model="search" placeholder="Cari kriteria atau subkriteria..." class="w-full rounded-xl border border-slate-300 bg-white py-3 px-4 text-sm shadow-sm focus:border-pln-600 focus:ring-2 focus:ring-pln-100"></label>
</header>
<nav class="criteria-tabs-scroll mt-7 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Daftar kriteria">
@foreach($criterias as $tab)
<button type="button" @click="activeCriteria='{{ $tab->id }}'" :class="activeCriteria==='{{ $tab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-600'" class="shrink-0 rounded-xl px-5 py-3 text-sm font-extrabold">{{ $tab->code ?? 'Kriteria' }}</button>
@endforeach
</nav>
<div class="mt-6 space-y-5">
@forelse($criterias as $criteria)
@php
    $levels = $criteria->subKriterias->flatMap(fn ($sub) => $sub->maturityLevels);
    $complete = $levels->filter(fn ($level) => $level->evidenceRequirements->isNotEmpty() && $level->evidenceRequirements->every(fn ($req) => $req->evidenceUploads->sortByDesc('id')->first()?->status === 'approved'))->count();
    $criteriaText = strtolower(($criteria->code ?? '') . ' ' . ($criteria->title ?? ''));
@endphp
<section x-show="activeCriteria==='{{ $criteria->id }}' && (search==='' || '{{ addslashes($criteriaText) }}'.includes(search.toLowerCase()))" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" role="tabpanel">
<div class="border-b border-slate-200 px-5 py-5 sm:px-7"><div class="flex flex-col justify-between gap-4 sm:flex-row"><div><span class="rounded-lg bg-pln-900 px-3 py-1 text-xs font-extrabold text-white">{{ $criteria->code ?? 'KRIT' }}</span><h2 class="mt-3 font-display text-xl font-extrabold text-slate-950">{{ $criteria->title ?? 'Kriteria K3' }}</h2><p class="mt-2 text-xs text-slate-500">{{ $complete }} dari {{ $levels->count() }} level lengkap</p></div><div class="w-full sm:w-56"><div class="flex justify-between text-xs font-bold"><span>Kelengkapan</span><span>{{ $levels->count() ? round($complete / $levels->count() * 100) : 0 }}%</span></div><div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-full rounded-full bg-pln-700" style="width:{{ $levels->count() ? round($complete / $levels->count() * 100) : 0 }}%"></div></div></div></div></div>
<div class="space-y-4 p-4 sm:p-6">
@forelse($criteria->subKriterias as $subIndex => $sub)
@php $firstLevel = $selectedLevel && $sub->maturityLevels->contains('id', $selectedLevel) ? $selectedLevel : ($sub->maturityLevels->first()->id ?? ''); $subText = strtolower(($sub->code ?? '') . ' ' . ($sub->title ?? '') . ' ' . ($sub->description ?? '')); @endphp
<article x-data="{ open:{{ $subIndex === 0 ? 'true' : 'false' }}, level:'{{ $firstLevel }}' }" x-show="search==='' || '{{ addslashes($subText) }}'.includes(search.toLowerCase())" class="rounded-xl border border-slate-200 bg-white">
<button type="button" @click="open=!open" class="flex w-full items-start justify-between gap-4 p-5 text-left sm:p-6"><span class="min-w-0"><span class="flex flex-wrap gap-2"><b class="rounded bg-slate-100 px-2 py-1 text-xs text-pln-800">{{ $sub->code ?? 'SUB' }}</b><b class="text-slate-900">{{ $sub->title ?? 'Sub Kriteria' }}</b></span>@if($sub->description)<span class="mt-2 block text-sm text-slate-600">{{ $sub->description }}</span>@endif</span><span class="shrink-0 text-xs font-bold text-pln-700" x-text="open?'Tutup':'Lihat Detail'"></span></button>
<p class="px-5 pb-4 text-xs font-semibold text-slate-500">Nilai SK: <span class="font-extrabold text-pln-800">{{ $sub->maturityLevels->filter(fn ($level) => $level->evidenceRequirements->isNotEmpty() && $level->evidenceRequirements->every(fn ($req) => $req->evidenceUploads->sortByDesc('id')->first()?->status === 'approved'))->max('level') ?? 0 }}</span></p>
<div x-cloak x-show="open" x-transition class="border-t border-slate-100 bg-slate-50/60 p-4 sm:p-6"><div class="level-tabs-scroll flex gap-2 overflow-x-auto pb-1">
@foreach($sub->maturityLevels as $levelTab)
<button type="button" @click="level='{{ $levelTab->id }}'" :class="level==='{{ $levelTab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-600'" class="shrink-0 rounded-lg px-4 py-2.5 text-xs font-extrabold">Level {{ $levelTab->level }}</button>
@endforeach
</div>
@foreach($sub->maturityLevels as $level)
@php $canUpload = $level->level === 1 || $sub->maturityLevels->firstWhere('level', $level->level - 1)?->evidenceUploads->isNotEmpty(); @endphp
<div x-cloak x-show="level==='{{ $level->id }}'" class="mt-5"><div class="rounded-xl border border-pln-100 bg-pln-50/60 p-4"><p class="text-[10px] font-extrabold uppercase tracking-widest text-pln-700">Persyaratan Level {{ $level->level }}</p><p class="mt-2 text-sm font-semibold text-slate-800">{{ $level->description ?: 'Belum ada keterangan level.' }}</p></div><h4 class="mt-5 font-display text-lg font-extrabold text-slate-950">Evidence Requirement</h4><div class="mt-3 space-y-4">
@forelse($level->evidenceRequirements as $requirement)
@php $current = $requirement->evidenceUploads->sortByDesc('id')->first(); $status = $current?->status ?? 'empty'; $statusText = $status === 'approved' ? 'Diterima' : ($status === 'rejected' ? 'Ditolak' : ($status === 'pending' ? 'Menunggu Penilaian' : 'Belum Upload')); $statusColor = $status === 'approved' ? 'bg-emerald-50 text-emerald-800' : ($status === 'rejected' ? 'bg-rose-50 text-rose-800' : ($status === 'pending' ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-600')); @endphp
<div x-data="{ history:false, revise:false, permission:false }" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"><div class="flex flex-col justify-between gap-3 sm:flex-row"><div><p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Evidence {{ $loop->iteration }} @if($requirement->is_required) · WAJIB @endif</p><h5 class="mt-1 font-bold text-slate-900">📄 {{ $requirement->name }}</h5><p class="mt-2 text-xs text-slate-600">{{ $requirement->description ?: 'Dokumen pendukung untuk level ini.' }}</p><p class="mt-2 text-[11px] font-semibold text-slate-500">Format {{ strtoupper($requirement->allowed_file_type) }} · Maksimum {{ round($requirement->max_file_size / 1024, 1) }} MB</p></div><span class="h-fit rounded-full px-3 py-1 text-[11px] font-extrabold {{ $statusColor }}">{{ $statusText }}</span></div>
@if($current)
<div class="mt-4 rounded-lg bg-slate-50 p-3"><a href="{{ asset('storage/' . $current->file_path) }}" target="_blank" class="break-all text-sm font-bold text-pln-800">📕 {{ $current->original_filename }}</a><p class="mt-1 text-xs text-slate-500">{{ $current->file_size ? number_format($current->file_size / 1048576, 1) . ' MB' : 'Ukuran tidak tersedia' }} · 👤 {{ $current->user->name ?? '-' }} · {{ $current->uploaded_at ? $current->uploaded_at->format('d M Y, H:i') . ' WITA' : '-' }}</p></div>
@if($status === 'rejected')<div class="mt-3 rounded-lg bg-rose-50 p-3 text-xs text-rose-900"><b>Alasan penolakan</b><p class="mt-1">{{ $current->rejection_note ?? 'Dokumen belum sesuai persyaratan.' }}</p></div>@endif
<div class="mt-4 flex flex-wrap gap-2"><a href="{{ asset('storage/' . $current->file_path) }}" target="_blank" class="rounded-lg border px-3 py-2 text-xs font-bold">Lihat</a><a href="{{ asset('storage/' . $current->file_path) }}" download class="rounded-lg bg-pln-800 px-3 py-2 text-xs font-bold text-white">Download</a>@if($current->revisions->isNotEmpty())<button type="button" @click="history=!history" class="rounded-lg border px-3 py-2 text-xs font-bold" x-text="history?'Tutup Riwayat':'Lihat Riwayat'"></button>@endif</div>
@if($status === 'rejected' && $canUpload)<button type="button" @click="revise=!revise" class="mt-3 text-xs font-bold text-rose-700 underline" x-text="revise?'Batal revisi':'Upload Revisi'"></button><form x-show="revise" x-cloak action="{{ route('evidence.upload', $requirement) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-2">@csrf<input type="hidden" name="criteria_id" value="{{ $criteria->id }}"><input type="file" name="document" accept=".pdf" required class="block w-full text-xs"><button class="w-full rounded-lg bg-rose-700 px-3 py-2 text-xs font-bold text-white">Kirim Revisi</button></form>@endif
@if($current->revisions->isNotEmpty())<div x-show="history" x-cloak class="mt-4 border-t pt-3 text-xs">@foreach($current->revisions as $revision)@if($revision->status !== 'deleted')<p class="py-1">REV {{ $revision->version_number }} · {{ $revision->original_filename }} · {{ ucfirst($revision->status) }}</p>@endif @endforeach</div>@endif
@else
<div class="mt-4 rounded-lg border border-dashed p-5 text-center"><p class="text-sm font-semibold text-slate-600">Belum ada dokumen</p>@if($canUpload)<form action="{{ route('evidence.upload', $requirement) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-2">@csrf<input type="hidden" name="criteria_id" value="{{ $criteria->id }}"><input type="file" name="document" accept=".pdf" required class="block w-full text-xs"><button class="w-full rounded-lg bg-pln-800 px-4 py-2.5 text-xs font-bold text-white">+ Upload Dokumen</button></form>@else<p class="mt-2 text-xs text-slate-500">Upload Level {{ $level->level - 1 }} terlebih dahulu.</p>@endif</div>
@endif
@if($current && $status !== 'approved' && (int) $current->user_id !== (int) Auth::id())<button type="button" @click="permission=true" class="mt-4 text-xs font-bold text-pln-700 underline">Request Permission to Replace</button><div x-show="permission" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4"><div @click.outside="permission=false" class="w-full max-w-md rounded-2xl bg-white p-6"><h3 class="font-display text-lg font-extrabold">Request Permission</h3><p class="mt-2 text-sm">Izin mengganti <b>{{ $requirement->name }}</b></p><form action="{{ route('documents.permission.request', $current) }}" method="POST" class="mt-4 space-y-3">@csrf<input type="hidden" name="action" value="edit"><textarea name="reason" required rows="4" placeholder="Alasan penggantian" class="w-full rounded-lg border p-3 text-sm"></textarea><div class="flex justify-end gap-2"><button type="button" @click="permission=false" class="rounded-lg border px-4 py-2 text-sm font-bold">Batal</button><button class="rounded-lg bg-pln-800 px-4 py-2 text-sm font-bold text-white">Kirim Request</button></div></form></div></div>@endif
</div>
@empty<div class="rounded-xl border border-dashed p-6 text-center text-sm text-slate-500">Belum ada evidence requirement untuk level ini.</div>@endforelse
</div></div>
@endforeach
</div></article>
@empty<p class="rounded-xl border border-dashed p-8 text-center text-sm text-slate-500">Belum ada subkriteria.</p>@endforelse
</div></section>
@empty<div class="rounded-2xl bg-white p-12 text-center text-sm text-slate-500">Belum ada data kriteria.</div>@endforelse
</div></div>
@endsection
