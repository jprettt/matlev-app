@extends('layouts.fore')

@section('title', 'Kriteria dan Bukti')

@section('content')
@php
    $selectedLevel = (int) request('level');
    $selectedRequirement = (int) request('requirement');
    $selectedCriteria = $selectedLevel
        ? $criterias->first(fn ($criteria) => $criteria->subKriterias->flatMap(fn ($sub) => $sub->maturityLevels)->contains('id', $selectedLevel))
        : $criterias->firstWhere('id', (int) request('criteria_id'));
    $initialCriteria = (string) ($selectedCriteria?->id ?? $criterias->first()->id ?? '');
@endphp
<div class="min-h-screen bg-slate-50 pb-12" x-data="{ activeCriteria: '{{ $initialCriteria }}', uploadOpen: false, uploadAction: '', uploadTitle: '', uploadSlot: '', uploadDescription: '', uploadFormat: '', uploadLimit: '', uploadRevision: false, scrollTabToTop(selector) { const el = document.querySelector(selector); if (!el) return; const startY = window.scrollY; const targetY = Math.max(0, startY + el.getBoundingClientRect().top - 12); const distance = targetY - startY; const duration = 700; const startTime = performance.now(); const tick = (now) => { const progress = Math.min((now - startTime) / duration, 1); const eased = 1 - Math.pow(1 - progress, 3); window.scrollTo(0, startY + distance * eased); if (progress < 1) requestAnimationFrame(tick); }; requestAnimationFrame(tick); } }" @keydown.escape.window="uploadOpen=false">
    <div class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <header class="flex flex-col justify-between gap-5 border-b border-slate-200 pb-7 md:flex-row md:items-end">
            <div><p class="text-xs font-extrabold uppercase tracking-[.2em] text-pln-700">Evaluasi Maturity Level K3</p><h1 class="mt-2 font-display text-2xl font-extrabold text-slate-950 sm:text-3xl">Kriteria dan Bukti</h1><p class="mt-2 text-sm text-slate-600">Pilih kriteria, sub kriteria, level, lalu kelola bukti pada slot yang sesuai.</p></div>
        </header>
        <nav id="criteria-tabs" class="mt-7 flex gap-2 overflow-x-auto pb-1" role="tablist">
            @foreach($criterias as $tab)<button type="button" @click="activeCriteria='{{ $tab->id }}'; scrollTabToTop('#criteria-tabs');" :class="activeCriteria==='{{ $tab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-600'" class="shrink-0 rounded-xl px-5 py-3 text-sm font-extrabold">{{ $tab->code }}</button>@endforeach
        </nav>
        <div class="mt-6 space-y-5">
        @forelse($criterias as $criteria)
            @php $selectedSub = $selectedLevel ? $criteria->subKriterias->first(fn ($item) => $item->maturityLevels->contains('id', $selectedLevel)) : null; $initialSub = (string) ($selectedSub?->id ?? $criteria->subKriterias->first()->id ?? ''); @endphp
            <section x-data="{ activeSub:'{{ $initialSub }}' }" x-show="activeCriteria==='{{ $criteria->id }}'" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-5 sm:px-7"><div class="flex flex-wrap items-start justify-between gap-3"><div><span class="rounded-lg bg-pln-900 px-3 py-1 text-xs font-extrabold text-white">{{ $criteria->code }}</span><h2 class="mt-3 font-display text-xl font-extrabold text-slate-950">{{ $criteria->title }}</h2></div><div class="rounded-xl bg-pln-50 px-4 py-3 text-right"><p class="text-[10px] font-extrabold uppercase tracking-widest text-pln-700">Nilai Kriteria</p><p class="mt-1 text-2xl font-extrabold text-pln-900">{{ number_format($criteria->scoreForUser(Auth::id()), 2) }} <span class="text-xs">/ 5</span></p><p class="text-[10px] text-slate-500">Rata-rata nilai SK</p></div></div></div>
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-4 sm:px-6"><p class="mb-3 text-xs font-extrabold uppercase tracking-widest text-slate-500">Pilih Sub Kriteria</p><nav id="subcriteria-tabs-{{ $criteria->id }}" class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Sub kriteria"><div class="flex min-w-max gap-2">@foreach($criteria->subKriterias as $subTab)<button type="button" @click="activeSub='{{ $subTab->id }}'; scrollTabToTop('#subcriteria-tabs-{{ $criteria->id }}');" :class="activeSub==='{{ $subTab->id }}'?'bg-pln-800 text-white':'border border-slate-200 bg-white text-slate-700'" class="max-w-xs rounded-lg px-4 py-2.5 text-left text-xs font-extrabold">{{ $subTab->code }}<span class="mt-1 block max-w-[260px] truncate font-semibold opacity-80">{{ $subTab->title }}</span></button>@endforeach</div></nav></div>
                <div class="p-4 sm:p-6">
                @forelse($criteria->subKriterias as $subIndex => $sub)
                    @php $firstLevel = $selectedLevel && $sub->maturityLevels->contains('id', $selectedLevel) ? $selectedLevel : ($sub->maturityLevels->first()->id ?? ''); @endphp
                    @php $summaryFiles = $sub->maturityLevels->flatMap(fn ($item) => $item->evidenceRequirements)->pluck('name')->filter(fn ($name) => $name && $name !== '-')->map(fn ($name) => trim($name))->unique()->values(); @endphp
                    <article x-data="{ level:'{{ $firstLevel }}', summary:false }" x-show="activeSub==='{{ $sub->id }}'" class="rounded-xl border border-slate-200 bg-white">
                        <div class="flex w-full items-start justify-between gap-4 p-5 text-left"><span><span class="rounded bg-slate-100 px-2 py-1 text-xs font-extrabold text-pln-800">{{ $sub->code }}</span><b class="ml-2 text-slate-900">{{ $sub->title }}</b>@if($sub->description)<span class="mt-2 block text-sm text-slate-600">{{ $sub->description }}</span>@endif</span><button type="button" @click="summary=!summary" class="shrink-0 rounded-lg border border-pln-200 px-3 py-2 text-xs font-bold text-pln-700 hover:bg-pln-50" x-text="summary?'Tutup Ringkasan':'Lihat Ringkasan File'"></button></div>
                        <div x-cloak x-show="summary" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-label="Ringkasan file"><div @click.outside="summary=false" x-transition class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl sm:p-7"><div class="flex items-start justify-between gap-4"><div><p class="text-xs font-extrabold uppercase tracking-widest text-pln-700">Ringkasan File</p><h3 class="mt-1 font-display text-xl font-extrabold text-slate-950">Sub Kriteria {{ $sub->code }}</h3></div><button type="button" @click="summary=false" class="text-2xl leading-none text-slate-400" aria-label="Tutup">&times;</button></div><p class="mt-2 text-sm text-slate-600">{{ $sub->title }}</p>@if($summaryFiles->isNotEmpty())<ol class="mt-5 grid max-h-[60vh] gap-3 overflow-y-auto text-sm text-slate-700">@foreach($summaryFiles as $fileName)<li class="flex gap-3 rounded-lg border border-slate-100 bg-slate-50 px-3 py-3"><span class="font-bold text-pln-700">{{ $loop->iteration }}.</span><span>{{ $fileName }}</span></li>@endforeach</ol>@else<p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Tidak ada file yang diperlukan pada sub kriteria ini.</p>@endif</div></div>
                        <p class="px-5 pb-4 text-xs font-semibold text-slate-500">Nilai SK: <span class="font-extrabold text-pln-800">{{ $sub->scoreForUser() }}</span> / 5</p>
                        <div class="border-t border-slate-100 bg-slate-50/60 p-4 sm:p-6">
                            <div id="level-tabs-{{ $sub->id }}" class="flex gap-2 overflow-x-auto pb-1">@foreach($sub->maturityLevels as $levelTab) @php $levelTabCompleted = $levelTab->statusForUser() === 'COMPLETED'; @endphp<button type="button" @click="level='{{ $levelTab->id }}'; scrollTabToTop('#level-tabs-{{ $sub->id }}');" :class="level==='{{ $levelTab->id }}' ? '{{ $levelTabCompleted ? 'bg-emerald-600 text-white' : 'bg-pln-800 text-white' }}' : '{{ $levelTabCompleted ? 'border border-emerald-200 bg-emerald-50 text-emerald-800' : 'border border-slate-200 bg-white text-slate-600' }}'" class="shrink-0 rounded-lg px-4 py-2.5 text-xs font-extrabold">Level {{ $levelTab->level }}</button>@endforeach</div>
                            @foreach($sub->maturityLevels as $level)
                                <div x-cloak x-show="level==='{{ $level->id }}'" class="mt-5">
                                    @php $previousLevel = $level->level > 1 ? $sub->maturityLevels->firstWhere('level', $level->level - 1) : null; $canUploadLevel = !$previousLevel || $previousLevel->hasAllRequiredFiles(); $levelStatus = $level->statusForUser(); $statusLabels = ['COMPLETED' => 'Terpenuhi', 'UNDER_REVIEW' => 'Dalam Penilaian', 'NEEDS_REVISION' => 'Perlu Revisi', 'NOT_STARTED' => 'Belum Terpenuhi']; $statusColors = ['COMPLETED' => 'bg-emerald-50 text-emerald-800', 'UNDER_REVIEW' => 'bg-amber-50 text-amber-800', 'NEEDS_REVISION' => 'bg-rose-50 text-rose-800', 'NOT_STARTED' => 'bg-slate-100 text-slate-600']; @endphp
                                    <div class="rounded-xl border border-pln-100 bg-pln-50/60 p-5"><div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs font-extrabold uppercase tracking-widest text-pln-700">Level {{ $level->level }}</p></div><p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-800">{{ $level->overall_description ?: $level->description ?: 'Belum ada keterangan level.' }}</p></div>
                                    @if(strtoupper((string) $level->evidence_mode) === 'NONE')
                                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-7 text-center"><p class="text-3xl text-emerald-600">&#10003;</p><p class="mt-2 font-display text-lg font-extrabold text-emerald-900">LEVEL TERPENUHI</p><p class="mt-1 text-sm text-emerald-800">Level ini tidak memerlukan dokumen atau file sebagai bukti pemenuhan.</p></div>
                                    @else
                                        @if(!$canUploadLevel)<div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-5"><p class="font-bold text-amber-900">Level {{ $level->level }} belum dapat diisi.</p><p class="mt-1 text-sm text-amber-800">Upload bukti Level {{ $previousLevel->level }} terlebih dahulu sebelum mengisi level ini.</p></div>@endif
                                        <h4 class="mt-6 font-display text-lg font-extrabold text-slate-950">Bukti yang Dibutuhkan</h4>
                                        <div class="mt-3 space-y-4">
                                        @foreach($level->evidenceRequirements as $requirement)
                                            @php $slots = $requirement->slots; $uploads = $slots->map(fn ($slot) => $level->currentUploadForUser($slot, null))->filter(); $approved = $uploads->where('status', 'approved')->count(); $pending = $uploads->where('status', 'pending')->count(); $rejected = $uploads->where('status', 'rejected')->count(); $notUploaded = max(0, $slots->count() - $uploads->count()); @endphp
                                            @php
                                                $emptyCount = $slots->filter(fn ($slot) => !$level->currentUploadForUser($slot, null))->count();
                                                if ($emptyCount > 0) {
                                                    $cardStatus = 'incomplete';
                                                } elseif ($rejected > 0) {
                                                    $cardStatus = 'rejected';
                                                } elseif ($pending > 0) {
                                                    $cardStatus = 'pending';
                                                } elseif ($approved === $slots->count() && $slots->count() > 0) {
                                                    $cardStatus = 'approved';
                                                } else {
                                                    $cardStatus = 'incomplete';
                                                }

                                                $statusMeta = [
                                                    'incomplete' => ['label' => 'Belum Lengkap', 'icon' => '•', 'container' => 'border border-slate-200 bg-white shadow-sm', 'badge' => 'border border-slate-200 bg-slate-100 text-slate-700', 'text' => 'text-slate-700'],
                                                    'rejected' => ['label' => 'Ditolak', 'icon' => '×', 'container' => 'border border-rose-200 bg-rose-50/80 shadow-sm', 'badge' => 'border border-rose-200 bg-rose-100 text-rose-700', 'text' => 'text-rose-700'],
                                                    'pending' => ['label' => 'Menunggu Verifikasi', 'icon' => '◔', 'container' => 'border border-amber-200 bg-amber-50/80 shadow-sm', 'badge' => 'border border-amber-200 bg-amber-100 text-amber-700', 'text' => 'text-amber-700'],
                                                    'approved' => ['label' => 'Diterima', 'icon' => '✓', 'container' => 'border border-emerald-200 bg-emerald-50/80 shadow-sm', 'badge' => 'border border-emerald-200 bg-emerald-100 text-emerald-700', 'text' => 'text-emerald-700'],
                                                ];
                                                $currentStatus = $statusMeta[$cardStatus];
                                                $statusSummary = collect([
                                                    ['count' => $approved, 'label' => 'diterima', 'icon' => '✓', 'class' => 'bg-emerald-50 text-emerald-700'],
                                                    ['count' => $pending, 'label' => 'menunggu', 'icon' => '◔', 'class' => 'bg-amber-50 text-amber-700'],
                                                    ['count' => $rejected, 'label' => 'ditolak', 'icon' => '×', 'class' => 'bg-rose-50 text-rose-700'],
                                                    ['count' => $notUploaded, 'label' => 'belum upload', 'icon' => '◌', 'class' => 'bg-slate-100 text-slate-600'],
                                                ])->filter(fn ($item) => $item['count'] > 0)->values();
                                            @endphp
                                            <section id="requirement-{{ $requirement->id }}" x-data="{ expanded: {{ (int) $selectedRequirement === (int) $requirement->id ? 'true' : 'false' }} }" class="relative rounded-xl p-4 sm:p-5" :class="expanded && {{ $slots->count() > 1 ? 'true' : 'false' }} ? 'border border-slate-200 bg-white shadow-sm' : '{{ $currentStatus['container'] }}'"><div class="flex w-full flex-wrap items-start justify-between gap-3 text-left"><div class="min-w-0 flex-1"><span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Bukti {{ $loop->iteration }} @if($requirement->is_required) · WAJIB @endif</span><h5 class="mt-1 font-bold text-slate-900">{{ $requirement->name }}</h5>@if($requirement->description)<p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $requirement->description }}</p>@endif</div><div class="flex items-center gap-2">@if($statusSummary->isNotEmpty())<div class="flex flex-wrap items-center gap-1.5 text-[11px] font-bold">@foreach($statusSummary as $item)<span class="inline-flex items-center gap-1 rounded-full px-2 py-1 {{ $item['class'] }}">{{ $item['icon'] }} {{ $item['count'] }} {{ $item['label'] }}</span>@endforeach</div>@endif<button type="button" @click="expanded=!expanded" class="text-xs font-bold text-pln-700" x-text="expanded?'Tutup':'Buka Bukti'"></button></div></div>
                                                <div x-show="expanded" x-cloak>
                                                <div class="mt-4 @if($slots->count() > 1) grid gap-3 md:grid-cols-2 @else space-y-3 @endif">@foreach($slots as $slot) @php $current = $level->currentUploadForUser($slot, null); $activeRevision = $current?->revisions?->first(fn ($revision) => $revision->is_current && $revision->status !== 'deleted'); $status = $current?->status ?? 'empty'; $permission = $current?->permissionRequests?->where('requester_id', Auth::id())->sortByDesc('id')->first(); $canReplace = !$current || $status === 'rejected' || (int) $current->user_id === (int) Auth::id() || $permission?->status === 'approved'; $slotLabel = $slots->count() === 2 ? 'Triwulan ' . $loop->iteration : ($slots->count() === 6 ? 'Bulan ' . $loop->iteration : $slot->name); @endphp
                                                    <div class="rounded-lg border p-4 shadow-sm {{ $slots->count() > 1 ? ($status === 'approved' ? 'border-emerald-200 bg-emerald-50/80' : ($status === 'rejected' ? 'border-rose-200 bg-rose-50/80' : ($status === 'pending' ? 'border-amber-200 bg-amber-50/80' : 'border-slate-200 bg-white'))) : 'border-slate-200 bg-white' }}"><div class="flex flex-wrap justify-between gap-2">@if($slots->count() > 1)<h6 class="font-bold text-slate-900">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} · {{ $slotLabel }}</h6>@endif<span class="text-xs font-extrabold {{ $status === 'approved' ? 'text-emerald-700' : ($status === 'rejected' ? 'text-rose-700' : ($status === 'pending' ? 'text-amber-700' : 'text-slate-500')) }}">{{ $status === 'approved' ? 'Diterima' : ($status === 'rejected' ? 'Ditolak' : ($status === 'pending' ? 'Menunggu Penilaian' : 'Belum Upload')) }}</span></div>
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
                                                        @if($current && $status !== 'approved' && (int) $current->user_id === (int) Auth::id())<form action="{{ $activeRevision && $activeRevision->status === 'pending' ? route('documents.revisions.delete', $activeRevision) : route('documents.delete', $current) }}" method="POST" class="mt-3">@csrf @method('DELETE')<button class="text-xs font-bold text-rose-700">Hapus File</button></form>@endif
                                                        @if($canUploadLevel && $canReplace && (!$current || $status === 'rejected'))<button type="button" @click="uploadOpen=true; uploadAction='{{ route('evidence.slot.upload', $slot) }}'; uploadTitle='{{ addslashes($requirement->name) }}'; uploadSlot='{{ addslashes($slotLabel) }}'; uploadDescription='{{ $slots->count() > 1 ? addslashes($slot->description ?: $requirement->description ?: '') : '' }}'; uploadFormat='{{ strtoupper($requirement->allowed_file_types ?: $requirement->allowed_file_type) }}'; uploadLimit='{{ round($requirement->max_file_size / 1024, 1) }} MB'; uploadRevision={{ $status === 'rejected' ? 'true' : 'false' }}" class="mt-4 rounded-lg bg-pln-800 px-4 py-2 text-xs font-bold text-white">{{ $status === 'rejected' ? 'Upload Revisi' : 'Upload File' }}</button>@endif
                                                        @if($canUploadLevel && $current && (int) $current->user_id !== (int) Auth::id() && $status === 'pending' && $permission?->status !== 'approved')
                                                            @if($permission?->status === 'pending')<p class="mt-3 text-xs font-bold text-amber-700">Permintaan izin sedang diproses.</p>@elseif($permission?->status === 'rejected')<p class="mt-3 text-xs font-bold text-rose-700">Permintaan izin ditolak.</p>@else<form action="{{ route('documents.permission.request', $current) }}" method="POST" class="mt-4 space-y-2">@csrf<input type="hidden" name="action" value="edit"><textarea name="reason" required rows="2" placeholder="Alasan meminta izin mengganti dokumen" class="w-full rounded-lg border border-slate-300 p-2 text-xs"></textarea><button class="rounded-lg border border-pln-700 px-3 py-2 text-xs font-bold text-pln-800">Minta Izin Mengganti</button></form>@endif
                                                        @endif
                                                        @if($current && $status !== 'approved' && (int) $current->user_id !== (int) Auth::id() && $permission?->status === 'approved')
                                                            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                                                <p class="text-xs font-bold text-emerald-900">{{ $current->user->name ?? 'Pemilik file' }} mengizinkan pergantian file.</p>
                                                                <form action="{{ route('documents.delete', $current) }}" method="POST" class="mt-3">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="rounded-lg bg-rose-600 px-3 py-2 text-xs font-bold text-white">Hapus File</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                        @if($current && $status !== 'approved')
                                                            @foreach($current->permissionRequests->where('owner_id', Auth::id())->where('status', 'pending') as $permissionRequest)<div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3"><p class="text-xs font-bold text-amber-900">{{ $permissionRequest->requester->name ?? 'User' }} meminta izin mengganti file ini.</p><p class="mt-1 text-xs text-amber-800">{{ $permissionRequest->reason ?: 'Tidak ada alasan.' }}</p><div class="mt-3 flex flex-wrap gap-2"><form action="{{ route('documents.permission.respond', $permissionRequest) }}" method="POST">@csrf<input type="hidden" name="status" value="approved"><button class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white">Berikan Izin</button></form><form action="{{ route('documents.permission.respond', $permissionRequest) }}" method="POST">@csrf<input type="hidden" name="status" value="rejected"><button class="rounded-lg border border-rose-300 px-3 py-2 text-xs font-bold text-rose-700">Tolak</button></form></div></div>@endforeach
                                                            @foreach($current->permissionRequests->where('owner_id', Auth::id())->where('status', 'approved') as $permissionRequest)
                                                                <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                                                    <p class="text-xs font-bold text-emerald-900">{{ $permissionRequest->owner->name ?? 'Pemilik file' }} mengizinkan pergantian file.</p>
                                                                </div>
                                                            @endforeach
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