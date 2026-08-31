@extends('layouts.admin')

@section('title', 'Antrean Verifikasi')

@section('content')
@php
    $firstCriteria = $criterias->first();
    $initialCriteria = $selectedCriteriaId ?? ($firstCriteria?->id ?? '');
    $firstSub = $firstCriteria?->subKriterias->first();
    $initialSub = $selectedSubId ?? ($firstSub?->id ?? '');
    $firstLevel = $firstSub?->maturityLevels->first();
    $initialLevel = $selectedLevelId ?? ($firstLevel?->id ?? '');
@endphp

<div class="min-h-screen bg-slate-50/40" x-data="{
    activeCriteria: '{{ $initialCriteria }}',
    activeSub: '{{ $initialSub }}',
    activeLevel: '{{ $initialLevel }}',
    updateTabState() {
        const params = new URLSearchParams(window.location.search);
        if (this.activeCriteria) {
            params.set('criteria_id', this.activeCriteria);
        } else {
            params.delete('criteria_id');
        }
        if (this.activeSub) {
            params.set('sub_criteria_id', this.activeSub);
        } else {
            params.delete('sub_criteria_id');
        }
        if (this.activeLevel) {
            params.set('level_id', this.activeLevel);
        } else {
            params.delete('level_id');
        }

        const url = new URL(window.location.href);
        url.search = params.toString();
        window.history.replaceState({}, '', url.toString());
    },
    syncFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const criteriaId = params.get('criteria_id');
        const subId = params.get('sub_criteria_id');
        const levelId = params.get('level_id');

        if (criteriaId) this.activeCriteria = criteriaId;
        if (subId) this.activeSub = subId;
        if (levelId) this.activeLevel = levelId;
    },
    scrollTabToTop(selector) {
        const el = document.querySelector(selector);
        if (!el) return;
        const startY = window.scrollY;
        const targetY = Math.max(0, startY + el.getBoundingClientRect().top - 12);
        const distance = targetY - startY;
        const duration = 500;
        const startTime = performance.now();
        const tick = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            window.scrollTo(0, startY + distance * eased);
            if (progress < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }
}" x-init="syncFromUrl()">
    <div class="mx-auto max-w-[1500px] px-4 pt-6 sm:px-6 lg:px-8">
        <header class="flex flex-col justify-between gap-5 border-b border-stone-200 pb-7 md:flex-row md:items-end">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.2em] text-pln-700">Evaluasi Maturity Level K3</p>
                <h1 class="mt-2 font-display text-2xl font-extrabold text-slate-950 sm:text-3xl">Antrean Verifikasi</h1>
                <p class="mt-2 text-sm text-stone-600">Tinjau berkas pending per level. Verifikator harus mengevaluasi dari level paling bawah terlebih dahulu agar level berikutnya dapat dinilai.</p>
            </div>
        </header>

        <nav id="criteria-tabs" class="mt-7 flex gap-2 overflow-x-auto pb-1" role="tablist">
            @foreach($criterias as $tab)
                <button type="button"
                    @click="activeCriteria='{{ $tab->id }}'; activeSub='{{ $tab->subKriterias->first()->id ?? '' }}'; activeLevel='{{ $tab->subKriterias->first()?->maturityLevels->first()->id ?? '' }}'; updateTabState(); scrollTabToTop('#criteria-tabs');"
                    :class="activeCriteria === '{{ $tab->id }}' ? 'bg-[#b89416] text-white shadow-lg shadow-yellow-950/20' : 'border border-stone-200 bg-white text-stone-600'"
                    class="shrink-0 rounded-xl px-5 py-3 text-sm font-extrabold">
                    {{ $tab->code }}
                </button>
            @endforeach
        </nav>

        <div class="mt-6 space-y-5">
            @forelse($criterias as $criteria)
                @php
                    $firstSubInCriteria = $criteria->subKriterias->first();
                    $firstLevelInCriteria = $firstSubInCriteria?->maturityLevels->first();
                @endphp

                <section x-show="activeCriteria === '{{ $criteria->id }}'" class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="border-b border-stone-200 px-5 py-5 sm:px-7">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <span class="rounded-lg bg-[#b89416] px-3 py-1 text-xs font-extrabold text-white">{{ $criteria->code }}</span>
                                <h2 class="mt-3 font-display text-xl font-extrabold text-slate-950">{{ $criteria->title }}</h2>
                            </div>
                            <div class="rounded-xl bg-[#fff7d6] px-4 py-3 text-right">
                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-[#8d6700]">Level Prioritas</p>
                                <p class="mt-1 text-2xl font-extrabold text-[#6f4f00]">
                                    {{ $criteria->subKriterias->flatMap(fn($sub) => $sub->maturityLevels)->filter(fn($level) => ($level->review_status ?? 'neutral') === 'red')->count() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-b border-stone-200 bg-stone-50 px-4 py-4 sm:px-6">
                        <p class="mb-3 text-xs font-extrabold uppercase tracking-widest text-stone-500">Pilih Sub Kriteria</p>
                        <nav id="subcriteria-tabs-{{ $criteria->id }}" class="flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Sub kriteria">
                            <div class="flex min-w-max gap-2">
                                @foreach($criteria->subKriterias as $subTab)
                                    <button type="button" @click="activeSub='{{ $subTab->id }}'; activeLevel='{{ $subTab->maturityLevels->first()->id ?? '' }}'; updateTabState(); scrollTabToTop('#subcriteria-tabs-{{ $criteria->id }}');" :class="activeSub === '{{ $subTab->id }}' ? 'bg-[#b89416] text-white' : 'border border-stone-200 bg-white text-stone-700'" class="max-w-xs rounded-lg px-4 py-2.5 text-left text-xs font-extrabold">
                                        {{ $subTab->code }}
                                        <span class="mt-1 block max-w-[260px] truncate font-semibold opacity-80">{{ $subTab->title }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </nav>
                    </div>

                    <div class="p-4 sm:p-6">
                        @foreach($criteria->subKriterias as $sub)
                            @php
                                $levels = $sub->maturityLevels->sortBy('level')->values();
                            @endphp

                            <article x-show="activeSub === '{{ $sub->id }}'" class="rounded-xl border border-stone-200 bg-white">
                                <div class="flex w-full items-start justify-between gap-4 p-5 text-left">
                                    <span>
                                        <span class="rounded bg-stone-100 px-2 py-1 text-xs font-extrabold text-[#7a5d00]">{{ $sub->code }}</span>
                                        <b class="ml-2 text-stone-900">{{ $sub->title }}</b>
                                        @if($sub->description)
                                            <span class="mt-2 block text-sm text-stone-600">{{ $sub->description }}</span>
                                        @endif
                                    </span>
                                </div>

                                <div class="border-t border-stone-100 bg-stone-50/60 p-4 sm:p-6">
                                    <div id="level-tabs-{{ $sub->id }}" class="mb-4 flex gap-2 overflow-x-auto pb-1">
                                        @foreach($levels as $levelTab)
                                            @php
                                                $count = $levelTab->review_pending_count ?? 0;
                                                $state = $levelTab->review_status ?? 'neutral';
                                                $buttonClass = $state === 'red'
                                                    ? 'bg-[#d93025] text-white border border-[#d93025]'
                                                    : ($state === 'yellow'
                                                        ? 'bg-[#f3c13a] text-[#4d3b00] border border-[#f3c13a]'
                                                        : 'border border-stone-200 bg-white text-stone-600');
                                            @endphp
                                            <button type="button" @click="activeLevel='{{ $levelTab->id }}'; updateTabState(); scrollTabToTop('#level-tabs-{{ $sub->id }}');" :class="activeLevel === '{{ $levelTab->id }}' ? '{{ $buttonClass }}' : '{{ $buttonClass }}'" class="relative shrink-0 rounded-lg px-4 py-2.5 text-xs font-extrabold">
                                                Level {{ $levelTab->level }}
                                                @if($count > 0)
                                                    <span class="ml-2 inline-flex min-w-5 items-center justify-center rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] font-black">{{ $count }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>

                                    @foreach($levels as $level)
                                        @php
                                            $pendingUploads = $level->evidenceUploads
                                                ->where('status', 'pending')
                                                ->sortByDesc('uploaded_at')
                                                ->values();
                                            $reviewedUploads = $level->evidenceUploads
                                                ->reject(fn ($upload) => $upload->status === 'pending')
                                                ->sortByDesc('uploaded_at')
                                                ->values();
                                            $state = $level->review_status ?? 'neutral';
                                            $warningText = $state === 'red'
                                                ? 'File perlu dinilai'
                                                : ($state === 'yellow'
                                                    ? 'Menunggu level sebelumnya selesai dinilai'
                                                    : ($reviewedUploads->isNotEmpty() ? 'Sudah dinilai' : 'Tidak ada file pending'));
                                        @endphp

                                        <div x-show="activeLevel === '{{ $level->id }}'" x-transition class="space-y-3">
                                            <div class="mb-2 flex items-center justify-between">
                                                <span class="text-[11px] font-extrabold uppercase tracking-[.18em] text-stone-500">Level {{ $level->level }}</span>
                                                @if($pendingUploads->isNotEmpty())
                                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider {{ $state === 'red' ? 'border-red-200 bg-red-50 text-red-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                                        {{ $warningText }}
                                                    </span>
                                                @elseif($reviewedUploads->isNotEmpty())
                                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Sudah dinilai</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full border border-stone-200 bg-stone-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-stone-600">Belum upload</span>
                                                @endif
                                            </div>

                                           @if($state === 'yellow')
                                                <div class="rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900 shadow-sm">
                                                    <div class="flex items-start gap-3">
                                                        <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-200 text-base font-bold text-amber-900">!</span>
                                                        <span>Level terkunci: dokumen pada level sebelumnya masih perlu revisi atau belum selesai dinilai.</span>
                                                    </div>
                                                </div>
                                           @elseif($level->evidenceUploads->isEmpty())                                                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">                                                    @forelse($level->evidenceRequirements as $requirement)                                                        <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <span class="text-[10px] font-extrabold uppercase tracking-[.18em] text-stone-500">
                                                                    Bukti {{ $loop->iteration }} @if($requirement->is_required) · Wajib @endif
                                                                </span>
                                                                <span class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-stone-100 px-2.5 py-1 text-[10px] font-bold text-stone-600">
                                                                    <span class="inline-block h-2 w-2 rounded-full bg-stone-400"></span>
                                                                    Belum upload
                                                                </span>
                                                            </div>
                                                            <h4 class="mt-3 text-lg font-extrabold text-stone-900">{{ $requirement->name }}</h4>
                                                            @if($requirement->description)
                                                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $requirement->description }}</p>
                                                            @endif
                                                            @php
                                                                $formatText = $requirement->allowed_file_types ?: $requirement->allowed_file_type;
                                                                $maxSize = $requirement->max_file_size ? round($requirement->max_file_size / 1024, 1) . ' MB' : 'Tidak dibatasi';
                                                            @endphp
                                                            @if($formatText || $maxSize)
                                                                <p class="mt-3 text-xs text-stone-500">
                                                                    Format: <span class="font-bold text-stone-700">{{ strtoupper($formatText ?: 'PDF') }}</span>
                                                                    · Maksimal <span class="font-bold text-stone-700">{{ $maxSize }}</span>
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="rounded-xl border border-dashed border-stone-200 bg-white px-4 py-6 text-sm text-stone-500">
                                                            Belum ada keterangan bukti untuk level {{ $level->level }}.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            @else
                                                @php
                                                   $uploads = $level->evidenceUploads
                                                       ->sortByDesc('uploaded_at')
                                                       ->values();
                                               @endphp

                                        @foreach($uploads as $upload)
                                                    @php
                                                        $requirement = $upload->evidenceRequirement;
                                                        $fileName = $upload->original_filename ?: basename($upload->file_path ?? '');
                                                        $isReviewed = in_array($upload->status, ['approved', 'rejected'], true);
                                                        $uploadStatus = match ($upload->status) {
                                                            'approved' => 'Disetujui',
                                                            'rejected' => 'Ditolak',
                                                            default => ($state === 'red' ? 'Perlu dinilai' : 'Menunggu prioritas'),
                                                        };
                                                        $uploadStatusClass = match ($upload->status) {
                                                            'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                                            'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                                                            default => ($state === 'red' ? 'border-red-200 bg-red-50 text-red-700' : 'border-amber-200 bg-amber-50 text-amber-700'),
                                                        };
                                                        $uploadDotClass = match ($upload->status) {
                                                            'approved' => 'bg-emerald-500',
                                                            'rejected' => 'bg-rose-500',
                                                            default => ($state === 'red' ? 'bg-red-500' : 'bg-amber-500'),
                                                        };
                                                    @endphp

                                                    <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                                                        <div class="mb-3 flex items-center justify-between gap-3">
                                                            <span class="text-[10px] font-extrabold uppercase tracking-[.18em] text-stone-500">
                                                                @if($requirement){{ $requirement->name }}@else Bukti file @endif
                                                            </span>
                                                            <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $uploadStatusClass }}">
                                                                <span class="inline-block h-2 w-2 rounded-full {{ $uploadDotClass }}"></span>
                                                                {{ $uploadStatus }}
                                                            </span>
                                                        </div>

                                                        <div class="grid gap-4 @if($upload->status === 'pending') lg:grid-cols-[1.2fr_1.3fr_0.9fr] @else lg:grid-cols-[1.2fr_1.3fr] @endif lg:items-start">
                                                            <div class="min-w-0">
                                                                <div class="text-xs font-bold uppercase tracking-[.18em] text-stone-500">
                                                                    {{ $upload->uploaded_at ? $upload->uploaded_at->timezone(config('app.timezone'))->format('d M Y H:i') . ' WITA' : '-' }}
                                                                </div>
                                                                <div class="mt-2 text-sm font-extrabold text-stone-800">{{ $upload->user->name ?? '-' }}</div>
                                                                @if($requirement)
                                                                    <div class="mt-1 text-base font-bold text-stone-900">{{ $requirement->name }}</div>
                                                                    @if($requirement->description)
                                                                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $requirement->description }}</p>
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="min-w-0">
                                                                <div class="text-[11px] font-extrabold uppercase tracking-[.18em] text-stone-500">Dokumen</div>
                                                                <div class="mt-2 flex items-center gap-2 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2">
                                                                    <span class="text-xs font-semibold text-stone-700">{{ strtoupper(pathinfo($fileName, PATHINFO_EXTENSION)) }}</span>
                                                                    <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="truncate text-sm font-semibold text-[#7a5d00] underline underline-offset-2 hover:text-[#5d4700]">
                                                                        {{ $fileName }}
                                                                    </a>
                                                                </div>
                                                            </div>

                                                            @if($upload->status === 'pending')
                                                                <div x-data="{
                                                                    approveOpen: false,
                                                                    rejectOpen: false,
                                                                    rejectionReason: ''
                                                                }" class="space-y-2">
                                                                    <button type="button" @click="approveOpen = true" class="w-full rounded-xl bg-[#1b8f5a] px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#147a4d]">
                                                                        Setujui
                                                                    </button>

                                                                    <button type="button" @click="rejectOpen = true" class="w-full rounded-xl bg-[#d93025] px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#bc2a20]">
                                                                        Tolak & Simpan Catatan
                                                                    </button>

                                                                    <div x-show="approveOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" style="display: none;">
                                                                        <div @click.self="approveOpen = false" class="w-full max-w-md rounded-2xl border border-stone-200 bg-white p-6 shadow-2xl">
                                                                            <div class="flex items-center justify-between gap-4">
                                                                                <div>
                                                                                    <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-stone-500">Konfirmasi</p>
                                                                                    <h3 class="mt-2 text-xl font-extrabold text-stone-900">Yakin setujui dokumen ini?</h3>
                                                                                </div>
                                                                            </div>
                                                                            <p class="mt-4 text-sm leading-6 text-stone-600">Tindakan ini akan menyetujui berkas yang sedang dinilai. Pastikan Anda sudah mengecek kelengkapan dokumen sebelum melanjutkan.</p>
                                                                            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                                                                <button type="button" @click="approveOpen = false" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-bold text-stone-700 transition hover:bg-stone-50">
                                                                                    Batal
                                                                                </button>
                                                                                <form action="{{ route('admin.verify', $upload->id) }}" method="POST">
                                                                                    @csrf
                                                                                    <input type="hidden" name="status" value="approved">
                                                                                    <button type="submit" class="w-full rounded-xl bg-[#1b8f5a] px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-[#147a4d]">
                                                                                        Ya, Setujui
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div x-show="rejectOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" style="display: none;">
                                                                        <div @click.self="rejectOpen = false" class="w-full max-w-xl rounded-2xl border border-stone-200 bg-white p-5 shadow-2xl">
                                                                            <div class="mb-4">
                                                                                <p class="text-[10px] font-extrabold uppercase tracking-[.22em] text-stone-500">Alasan penolakan</p>
                                                                                <h3 class="mt-2 text-xl font-extrabold text-stone-900">Tolak dokumen</h3>
                                                                            </div>

                                                                            <form action="{{ route('admin.verify', $upload->id) }}" method="POST" class="space-y-3">
                                                                                @csrf
                                                                                <input type="hidden" name="status" value="rejected">
                                                                                <textarea x-model="rejectionReason" name="rejection_note" rows="4" required placeholder="Tulis alasan verifikator menolak dokumen..." class="w-full rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-2.5 text-sm text-stone-700 placeholder:text-rose-300 focus:border-rose-300 focus:ring-2 focus:ring-rose-100"></textarea>
                                                                                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                                                                    <button type="button" @click="rejectOpen = false" class="rounded-xl border border-stone-200 bg-white px-4 py-2.5 text-sm font-bold text-stone-700 transition hover:bg-stone-50">
                                                                                        Batal
                                                                                    </button>
                                                                                    <button type="submit" class="rounded-xl bg-[#d93025] px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-[#bc2a20]">
                                                                                        Tolak & Simpan Catatan
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="rounded-xl border border-stone-200 bg-stone-50 p-3">
                                                                    <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-stone-500">Status penilaian</p>
                                                                    <p class="mt-2 text-sm font-extrabold text-stone-800">
                                                                        {{ $upload->status === 'approved' ? 'Dokumen telah disetujui' : 'Dokumen telah ditolak' }}
                                                                    </p>
                                                                    @if($upload->status === 'rejected' && $upload->rejection_note)
                                                                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $upload->rejection_note }}</p>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center text-stone-500">
                    Tidak ada kriteria untuk ditampilkan.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
