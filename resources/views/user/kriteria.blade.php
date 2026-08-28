@extends('layouts.fore')

@section('title', 'Kriteria dan Bukti')

@section('content')
@php
    $selectedLevel = (int) request('level');
    $selectedCriteria = $selectedLevel
        ? $criterias->first(fn ($criteria) => $criteria->subKriterias->flatMap(fn ($sub) => $sub->maturityLevels)->contains('id', $selectedLevel))
        : $criterias->firstWhere('id', (int) request('criteria_id'));
    $initialCriteria = (string) ($selectedCriteria?->id ?? $criterias->first()->id ?? '');
@endphp
<div class="min-h-screen bg-slate-50 pb-12" x-data="{ activeCriteria: '{{ $initialCriteria }}', search: '', uploadOpen: false, uploadAction: '', uploadTitle: '', uploadSlot: '', uploadDescription: '', uploadFormat: '', uploadLimit: '', uploadRevision: false }" @keydown.escape.window="uploadOpen=false">
    <div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <header class="flex flex-col justify-between gap-5 border-b border-slate-200 pb-7 md:flex-row md:items-end">
            <div><p class="text-xs font-extrabold uppercase tracking-[.2em] text-pln-700">Evaluasi Maturity Level K3</p><h1 class="mt-2 font-display text-2xl font-extrabold text-slate-950 sm:text-3xl">Kriteria dan Bukti</h1><p class="mt-2 text-sm text-slate-600">Pilih sub kriteria, level, lalu kelola bukti pada slot yang sesuai.</p></div>
            <input type="search" x-model="search" placeholder="Cari kriteria atau sub kriteria..." class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm md:w-80">
        </header>
        <nav class="mt-7 flex gap-2 overflow-x-auto pb-1" role="tablist">
            @foreach($criterias as $tab)<button type="button" @click="activeCriteria='{{ $tab->id }}'" :class="activeCriteria==='{{ $tab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-600'" class="shrink-0 rounded-xl px-5 py-3 text-sm font-extrabold">{{ $tab->code }}</button>@endforeach
        </nav>
        <div class="mt-6 space-y-5">
        @forelse($criterias as $criteria)
            @php $criteriaText = strtolower($criteria->code . ' ' . $criteria->title); $selectedSub = $selectedLevel ? $criteria->subKriterias->first(fn ($item) => $item->maturityLevels->contains('id', $selectedLevel)) : null; $initialSub = (string) ($selectedSub?->id ?? $criteria->subKriterias->first()->id ?? ''); @endphp
            <section x-data="{ activeSub:'{{ $initialSub }}' }" x-show="activeCriteria==='{{ $criteria->id }}' && (search==='' || '{{ addslashes($criteriaText) }}'.includes(search.toLowerCase()))" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-5 sm:px-7"><span class="rounded-lg bg-pln-900 px-3 py-1 text-xs font-extrabold text-white">{{ $criteria->code }}</span><h2 class="mt-3 font-display text-xl font-extrabold text-slate-950">{{ $criteria->title }}</h2></div>
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-4 sm:px-6"><p class="mb-3 text-xs font-extrabold uppercase tracking-widest text-slate-500">Pilih Sub Kriteria</p><nav class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Sub kriteria"><div class="flex min-w-max gap-2">@foreach($criteria->subKriterias as $subTab)<button type="button" @click="activeSub='{{ $subTab->id }}'" :class="activeSub==='{{ $subTab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-700'" class="max-w-xs rounded-lg px-4 py-2.5 text-left text-xs font-extrabold">{{ $subTab->code }}<span class="mt-1 block max-w-[260px] truncate font-semibold opacity-80">{{ $subTab->title }}</span></button>@endforeach</div></nav></div>
                <div class="p-4 sm:p-6">
                @forelse($criteria->subKriterias as $subIndex => $sub)
                    @php $firstLevel = $selectedLevel && $sub->maturityLevels->contains('id', $selectedLevel) ? $selectedLevel : ($sub->maturityLevels->first()->id ?? ''); $subText = strtolower($sub->code . ' ' . $sub->title . ' ' . ($sub->description ?? '')); @endphp
                    <article x-data="{ open:true, level:'{{ $firstLevel }}' }" x-show="activeSub==='{{ $sub->id }}' && (search==='' || '{{ addslashes($subText) }}'.includes(search.toLowerCase()))" class="rounded-xl border border-slate-200 bg-white">
                        <button type="button" @click="open=!open" class="flex w-full items-start justify-between gap-4 p-5 text-left"><span><span class="rounded bg-slate-100 px-2 py-1 text-xs font-extrabold text-pln-800">{{ $sub->code }}</span><b class="ml-2 text-slate-900">{{ $sub->title }}</b>@if($sub->description)<span class="mt-2 block text-sm text-slate-600">{{ $sub->description }}</span>@endif</span><span class="shrink-0 text-xs font-bold text-pln-700" x-text="open?'Tutup':'Lihat Detail'"></span></button>
                        <p class="px-5 pb-4 text-xs font-semibold text-slate-500">Nilai SK: <span class="font-extrabold text-pln-800">{{ $sub->maturityLevels->filter(fn ($item) => $item->computed_status === 'COMPLETED')->max('level') ?? 0 }}</span></p>
                        <div x-cloak x-show="open" x-transition class="border-t border-slate-100 bg-slate-50/60 p-4 sm:p-6">
                            <div class="flex gap-2 overflow-x-auto pb-1">@foreach($sub->maturityLevels as $levelTab)<button type="button" @click="level='{{ $levelTab->id }}'" :class="level==='{{ $levelTab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-600'" class="shrink-0 rounded-lg px-4 py-2.5 text-xs font-extrabold">Level {{ $levelTab->level }}</button>@endforeach</div>
                            @foreach($sub->maturityLevels as $level)
                                <div x-cloak x-show="level==='{{ $level->id }}'" class="mt-5">
                                    @php $levelStatus = $level->computed_status; $statusLabels = ['COMPLETED' => 'Terpenuhi', 'UNDER_REVIEW' => 'Dalam Penilaian', 'NEEDS_REVISION' => 'Perlu Revisi', 'NOT_STARTED' => 'Belum Terpenuhi']; $statusColors = ['COMPLETED' => 'bg-emerald-50 text-emerald-800', 'UNDER_REVIEW' => 'bg-amber-50 text-amber-800', 'NEEDS_REVISION' => 'bg-rose-50 text-rose-800', 'NOT_STARTED' => 'bg-slate-100 text-slate-600']; @endphp
                                    <div class="rounded-xl border border-pln-100 bg-pln-50/60 p-5"><div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs font-extrabold uppercase tracking-widest text-pln-700">Level {{ $level->level }}</p><span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $statusColors[$levelStatus] }}">{{ $statusLabels[$levelStatus] }}</span></div><p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-800">{{ $level->overall_description ?: $level->description ?: 'Belum ada keterangan level.' }}</p></div>
                                    @if(strtoupper((string) $level->evidence_mode) === 'NONE')
                                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-7 text-center"><p class="text-3xl text-emerald-600">&#10003;</p><p class="mt-2 font-display text-lg font-extrabold text-emerald-900">LEVEL TERPENUHI</p><p class="mt-1 text-sm text-emerald-800">Level ini tidak memerlukan dokumen atau file sebagai bukti pemenuhan.</p></div>
                                    @else
                                        <h4 class="mt-6 font-display text-lg font-extrabold text-slate-950">Bukti yang Dibutuhkan</h4>
                                        <div class="mt-3 space-y-4">
                                        @foreach($level->evidenceRequirements as $requirement)
                                            @php $slots = $requirement->slots; $uploads = $slots->map(fn ($slot) => $slot->currentEvidence)->filter(); $approved = $uploads->where('status', 'approved')->count(); $pending = $uploads->where('status', 'pending')->count(); $rejected = $uploads->where('status', 'rejected')->count(); @endphp
                                            <section x-data="{ expanded:false }" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"><button type="button" @click="expanded=!expanded" class="flex w-full flex-wrap items-start justify-between gap-3 text-left"><span><span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Bukti {{ $loop->iteration }} @if($requirement->is_required) · WAJIB @endif</span><h5 class="mt-1 font-bold text-slate-900">{{ $requirement->name }}</h5></span><span class="flex items-center gap-2"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $approved }} / {{ $slots->count() }} selesai</span><span class="text-xs font-bold text-pln-700" x-text="expanded?'Tutup':'Buka Bukti'"></span></span></button><div class="mt-3 text-xs text-slate-500">&#128994; {{ $approved }} Diterima &nbsp; &#128993; {{ $pending }} Menunggu &nbsp; &#128308; {{ $rejected }} Ditolak &nbsp; &#9898; {{ max(0, $slots->count() - $uploads->count()) }} Belum Upload</div>
                                                @if($requirement->description)<div x-show="expanded" x-cloak x-data="{ descriptionOpen:false }" class="mt-3 border-t border-slate-100 pt-3"><p class="whitespace-pre-line text-sm leading-6 text-slate-600">{{ $requirement->description }}</p></div>@endif
                                                <div x-show="expanded" x-cloak>
                                                <div class="mt-4 space-y-3">@foreach($slots as $slot) @php $current = $slot->currentEvidence; $status = $current?->status ?? 'empty'; $permission = $current?->permissionRequests?->where('requester_id', Auth::id())->sortByDesc('id')->first(); $canReplace = !$current || (int) $current->user_id === (int) Auth::id() || $permission?->status === 'approved'; @endphp
                                                    <div class="rounded-lg border border-slate-200 p-4"><div class="flex flex-wrap justify-between gap-2"><h6 class="font-bold text-slate-900">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $slot->name }}</h6><span class="text-xs font-extrabold {{ $status === 'approved' ? 'text-emerald-700' : ($status === 'rejected' ? 'text-rose-700' : ($status === 'pending' ? 'text-amber-700' : 'text-slate-500')) }}">{{ $status === 'approved' ? 'Diterima' : ($status === 'rejected' ? 'Ditolak' : ($status === 'pending' ? 'Menunggu Penilaian' : 'Belum Upload')) }}</span></div>@if($slot->description)<p class="mt-2 text-sm leading-6 text-slate-600">{{ $slot->description }}</p>@endif
                                                        @if($current)
                                                            <div class="mt-3 rounded-lg bg-slate-50 p-3"><a href="{{ asset('storage/' . $current->file_path) }}" target="_blank" class="break-all text-sm font-bold text-pln-800">{{ $current->original_filename }}</a><p class="mt-1 text-xs text-slate-500">{{ $current->user->name ?? '-' }} · {{ $current->uploaded_at?->format('d M Y, H:i') }} WITA</p></div>
                                                            @if($status === 'rejected')
                                                                <p class="mt-3 rounded-lg bg-rose-50 p-3 text-xs text-rose-900"><b>Alasan penolakan:</b> {{ $current->rejection_reason ?: $current->rejection_note }}</p>
                                                            @endif
                                                            @if($current->revisions->isNotEmpty())
                                                                <details class="mt-3 text-xs"><summary class="cursor-pointer font-bold text-pln-700">Lihat Riwayat</summary>
                                                                    @foreach($current->revisions as $revision)<p class="border-b border-slate-100 py-2">REV {{ $revision->version_number }} · {{ $revision->original_filename }} · {{ ucfirst($revision->status) }} · {{ $revision->uploaded_at?->format('d M Y') }}</p>@endforeach
                                                                </details>
                                                            @endif
                                                        @endif
                                                        @if($canReplace && (!$current || $status === 'rejected'))<button type="button" @click="uploadOpen=true; uploadAction='{{ route('evidence.slot.upload', $slot) }}'; uploadTitle='{{ addslashes($requirement->name) }}'; uploadSlot='{{ addslashes($slot->name) }}'; uploadDescription='{{ addslashes($slot->description ?: $requirement->description ?: '') }}'; uploadFormat='{{ strtoupper($requirement->allowed_file_types ?: $requirement->allowed_file_type) }}'; uploadLimit='{{ round($requirement->max_file_size / 1024, 1) }} MB'; uploadRevision={{ $status === 'rejected' ? 'true' : 'false' }}" class="mt-4 rounded-lg bg-pln-800 px-4 py-2 text-xs font-bold text-white">{{ $status === 'rejected' ? 'Upload Revisi' : 'Upload File' }}</button>@endif
                                                        @if($current && (int) $current->user_id !== (int) Auth::id() && $status !== 'approved' && $permission?->status !== 'approved')
                                                            @if($permission?->status === 'pending')<p class="mt-3 text-xs font-bold text-amber-700">Permintaan izin sedang diproses.</p>@elseif($permission?->status === 'rejected')<p class="mt-3 text-xs font-bold text-rose-700">Permintaan izin ditolak.</p>@else<form action="{{ route('documents.permission.request', $current) }}" method="POST" class="mt-4 space-y-2">@csrf<input type="hidden" name="action" value="edit"><textarea name="reason" required rows="2" placeholder="Alasan meminta izin mengganti dokumen" class="w-full rounded-lg border border-slate-300 p-2 text-xs"></textarea><button class="rounded-lg border border-pln-700 px-3 py-2 text-xs font-bold text-pln-800">Minta Izin Mengganti</button></form>@endif
                                                        @endif
                                                    </div>@endforeach</div></div>
                                            </section>
                                        @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty <p class="rounded-xl border border-dashed p-8 text-center text-sm text-slate-500">Belum ada sub kriteria.</p> @endforelse
                </div>
            </section>
        @empty <div class="rounded-2xl bg-white p-12 text-center text-sm text-slate-500">Belum ada data kriteria.</div> @endforelse
        </div>
    </div>
<div x-cloak x-show="uploadOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 p-0 sm:items-center sm:p-4" role="dialog" aria-modal="true" aria-label="Upload bukti">
    <div x-show="uploadOpen" x-transition class="w-full max-w-lg rounded-t-2xl bg-white p-5 shadow-2xl sm:rounded-2xl sm:p-6">
        <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-extrabold uppercase tracking-widest text-pln-700" x-text="uploadRevision ? 'Upload Revisi' : 'Upload Bukti'"></p><h2 class="mt-1 font-display text-xl font-extrabold text-slate-950" x-text="uploadTitle"></h2></div><button type="button" @click="uploadOpen=false" class="text-2xl leading-none text-slate-400" aria-label="Tutup">&times;</button></div>
        <div class="mt-5 rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Slot</p><p class="mt-1 font-bold text-slate-900" x-text="uploadSlot"></p><p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600" x-text="uploadDescription"></p></div>
        <form :action="uploadAction" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">@csrf<div><label class="block text-sm font-bold text-slate-800">Pilih file</label><input type="file" name="document" required class="mt-2 block w-full rounded-lg border border-slate-300 p-2 text-sm" :accept="'.' + uploadFormat.toLowerCase()"><p class="mt-2 text-xs text-slate-500">Format: <span class="font-bold" x-text="uploadFormat"></span> · Maksimum <span class="font-bold" x-text="uploadLimit"></span></p></div><div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"><button type="button" @click="uploadOpen=false" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700">Batal</button><button type="submit" class="rounded-lg bg-pln-800 px-4 py-2.5 text-sm font-bold text-white" x-text="uploadRevision ? 'Kirim Revisi' : 'Upload File'"></button></div></form>
    </div>
</div>
</div>
@endsection