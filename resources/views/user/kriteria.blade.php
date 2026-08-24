@extends('layouts.fore')

@section('title', 'Daftar Kriteria & Upload Bukti')

@section('content')
<div class="space-y-8" x-data="{ searchQuery: '' }">

    <!-- PAGE HEADER (FORE STYLE) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fore-100 text-fore-900 text-xs font-bold">
                <span>📁 FORM PENGISIAN EVIDEN</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-display">
                Daftar Kriteria & Upload Dokumen
            </h1>
            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                Pilih level kematangan yang ingin dipenuhi lalu lampirkan berkas bukti eviden dalam format <strong>PDF (Maks. 10MB)</strong>.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="w-full md:w-72 relative">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Cari kriteria atau subkriteria..." 
                   class="w-full bg-cream-100 border border-stone-300 rounded-full py-2.5 pl-10 pr-4 text-xs sm:text-sm focus:outline-none focus:border-fore-700 focus:bg-white transition-colors">
            <svg class="w-4 h-4 text-stone-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
    </div>

    <!-- LIST CRITERIAS -->
    <div class="space-y-6">
        @forelse($criterias as $criteria)
            @php
                $critSlots = 0;
                $critApproved = 0;
                $critPending = 0;
                $critRejected = 0;
                foreach($criteria->subKriterias as $sub) {
                    foreach($sub->maturityLevels as $lvl) {
                        $critSlots++;
                        if($lvl->evidenceUpload) {
                            if($lvl->evidenceUpload->status == 'approved') $critApproved++;
                            elseif($lvl->evidenceUpload->status == 'pending') $critPending++;
                            elseif($lvl->evidenceUpload->status == 'rejected') $critRejected++;
                        }
                    }
                }
                $critPercent = $critSlots > 0 ? round(($critApproved / $critSlots) * 100) : 0;
                $criteriaFilterText = strtolower(($criteria->code ?? '') . ' ' . ($criteria->title ?? $criteria->nama ?? ''));
            @endphp

            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden"
                 x-show="searchQuery === '' || '{{ addslashes($criteriaFilterText) }}'.includes(searchQuery.toLowerCase())">
                
                <!-- Criteria Header -->
                <div class="bg-cream-100/90 p-5 sm:p-6 border-b border-stone-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5">
                            <span class="px-3 py-1 bg-fore-900 text-white font-extrabold text-xs rounded-full">
                                {{ $criteria->code ?? $criteria->kode ?? 'KRIT' }}
                            </span>
                            <h2 class="text-lg sm:text-xl font-extrabold text-stone-900 font-display">
                                {{ $criteria->title ?? $criteria->nama ?? 'Kriteria K3' }}
                            </h2>
                        </div>
                        <p class="text-xs text-stone-500">
                            Pencapaian Kriteria: <strong>{{ $critApproved }}</strong> dari <strong>{{ $critSlots }}</strong> level disetujui ({{ $critPercent }}%)
                        </p>
                    </div>

                    <div class="w-full md:w-56 space-y-1">
                        <div class="flex justify-between text-[11px] font-bold text-stone-600">
                            <span>Kelengkapan</span>
                            <span class="text-fore-900">{{ $critPercent }}%</span>
                        </div>
                        <div class="w-full bg-stone-200 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-fore-700 h-2.5 rounded-full transition-all duration-500" style="width: {{ $critPercent }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Subcriterias Body -->
                <div class="p-5 sm:p-6 space-y-6">
                    @forelse($criteria->subKriterias as $sub)
                        <div class="bg-cream-200/50 p-5 rounded-2xl border border-stone-200/90 space-y-4">
                            
                            <!-- Subcriteria Header -->
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-stone-200">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 bg-stone-200 text-stone-800 text-xs font-bold rounded">
                                            {{ $sub->code ?? $sub->kode ?? 'SUB' }}
                                        </span>
                                        <h3 class="font-bold text-stone-900 text-sm sm:text-base">
                                            {{ $sub->title ?? $sub->nama ?? 'Sub Kriteria' }}
                                        </h3>
                                    </div>
                                    @if($sub->description)
                                        <p class="text-xs text-stone-500 mt-1">{{ $sub->description }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-stone-200 shadow-2xs text-xs">
                                    <span class="text-stone-500">Nilai SK:</span>
                                    <span class="font-bold text-fore-900 bg-fore-50 px-2 py-0.5 rounded border border-fore-200">
                                        {{ $sub->skor_level ?? '0' }}
                                    </span>
                                </div>
                            </div>

                            <!-- 5 Level Cards Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3.5">
                                @forelse($sub->maturityLevels as $lvl)
                                    @php
                                        $previousLevel = $sub->maturityLevels->firstWhere('level', $lvl->level - 1);
                                        $canUpload = $lvl->level === 1 || ($previousLevel && $previousLevel->evidenceUpload);
                                        $currentRevision = $lvl->evidenceUpload?->revisions->first(fn ($revision) => $revision->is_current && $revision->status !== 'deleted');
                                    @endphp
                                    <div class="{{ $canUpload ? 'bg-white' : 'bg-stone-50' }} p-3.5 rounded-2xl border border-stone-200/90 shadow-xs flex flex-col justify-between hover:border-fore-300 transition-colors">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="px-2 py-0.5 bg-stone-100 text-fore-900 font-extrabold text-[11px] rounded-md border border-stone-200">
                                                    Level {{ $lvl->level }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-stone-600 leading-relaxed mb-4">
                                                {{ $lvl->evidence_requirement }}
                                            </p>
                                        </div>

                                        <div class="pt-2 border-t border-stone-100">
                                            @if($lvl->evidenceUpload)
                                                @php $st = $lvl->evidenceUpload->status ?? 'pending'; @endphp

                                                @if($st == 'pending')
                                                    <!-- Status: Menunggu Penilaian -->
                                                    <div x-data="{ showDetails: false }" class="bg-amber-50 border border-amber-200 text-amber-900 p-2.5 rounded-xl text-xs space-y-1.5">
                                                        <div class="flex items-center gap-1 font-bold text-[11px]">
                                                            <span>⏳</span>
                                                            <span>Menunggu Evaluasi</span>
                                                        </div>
                                                        <p class="text-[10px] text-stone-500 truncate" title="{{ $lvl->evidenceUpload->original_filename }}">
                                                            {{ $lvl->evidenceUpload->original_filename }}
                                                        </p>
                                                        <button type="button" @click="showDetails = !showDetails" class="text-[10px] font-bold text-amber-900 underline decoration-dotted underline-offset-2">
                                                            <span x-show="!showDetails">Details</span>
                                                            <span x-show="showDetails">Tutup Details</span>
                                                        </button>
                                                        <div x-show="showDetails" x-transition class="text-[10px] text-amber-900 bg-white/80 border border-amber-200 rounded-lg p-2 space-y-0.5">
                                                            <p><strong>Pengunggah:</strong> {{ $lvl->evidenceUpload->user->name ?? '-' }}</p>
                                                            <p><strong>Waktu Upload:</strong> {{ $lvl->evidenceUpload->uploaded_at ? $lvl->evidenceUpload->uploaded_at->timezone(config('app.timezone'))->format('d M Y H:i') . ' WITA' : '-' }}</p>
                                                        </div>
                                                        <div class="flex gap-1 pt-1">
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" target="_blank" 
                                                               class="flex-1 text-center bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 py-1 rounded text-[10px] font-semibold transition">
                                                                Preview
                                                            </a>
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" download 
                                                               class="flex-1 text-center bg-amber-600 text-white hover:bg-amber-700 py-1 rounded text-[10px] font-semibold transition">
                                                                Unduh
                                                            </a>
                                                        </div>
                                                        @if($currentRevision)
                                                            <div class="mt-2 pt-2 border-t border-amber-200 space-y-1.5">
                                                                <p class="text-[10px] text-amber-800 font-semibold">File revisi sedang menunggu verifikasi.</p>
                                                                <a href="{{ asset('storage/' . $currentRevision->file_path) }}" target="_blank" class="block w-full text-center bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 py-1.5 rounded text-[10px] font-semibold">Preview File Revisi</a>
                                                                <form action="{{ route('documents.revisions.delete', $currentRevision) }}" method="POST" onsubmit="return confirm('Hapus file revisi ini? Level akan kembali berstatus perlu revisi.')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="w-full text-[10px] font-bold py-1.5 px-2 rounded-lg bg-white border border-rose-300 text-rose-700 hover:bg-rose-50">Hapus File Revisi</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>

                                                @elseif($st == 'approved')
                                                    <!-- Status: Disetujui -->
                                                    <div x-data="{ showDetails: false }" class="bg-emerald-50 border border-emerald-200 text-emerald-900 p-2.5 rounded-xl text-xs space-y-1.5">
                                                        <div class="flex items-center gap-1 font-bold text-[11px]">
                                                            <span>✓</span>
                                                            <span>Disetujui</span>
                                                        </div>
                                                        <p class="text-[10px] text-stone-500 truncate" title="{{ $lvl->evidenceUpload->original_filename }}">
                                                            {{ $lvl->evidenceUpload->original_filename }}
                                                        </p>
                                                        <button type="button" @click="showDetails = !showDetails" class="text-[10px] font-bold text-emerald-900 underline decoration-dotted underline-offset-2">
                                                            <span x-show="!showDetails">Details</span>
                                                            <span x-show="showDetails">Tutup Details</span>
                                                        </button>
                                                        <div x-show="showDetails" x-transition class="text-[10px] text-emerald-900 bg-white/80 border border-emerald-200 rounded-lg p-2 space-y-0.5">
                                                            <p><strong>Pengunggah:</strong> {{ $lvl->evidenceUpload->user->name ?? '-' }}</p>
                                                            <p><strong>Waktu Upload:</strong> {{ $lvl->evidenceUpload->uploaded_at ? $lvl->evidenceUpload->uploaded_at->timezone(config('app.timezone'))->format('d M Y H:i') . ' WITA' : '-' }}</p>
                                                        </div>
                                                        <div class="flex gap-1 pt-1">
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" target="_blank" 
                                                               class="flex-1 text-center bg-white border border-emerald-300 text-emerald-900 hover:bg-emerald-100 py-1 rounded text-[10px] font-semibold transition">
                                                                Preview
                                                            </a>
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" download 
                                                               class="flex-1 text-center bg-emerald-700 text-white hover:bg-emerald-800 py-1 rounded text-[10px] font-semibold transition">
                                                                Unduh
                                                            </a>
                                                        </div>
                                                    </div>

                                                @elseif($st == 'rejected')
                                                    <!-- Status: Ditolak / Perlu Revisi -->
                                                    <div x-data="{ showDetails: false, selectedFile: false }" class="space-y-2">
                                                        <div class="bg-rose-50 border border-rose-200 text-rose-900 p-2 rounded-xl text-[11px] space-y-1">
                                                            <div class="flex items-center gap-1 font-bold text-rose-700">
                                                                <span>✕</span>
                                                                <span>Perlu Revisi</span>
                                                            </div>
                                                            <p class="text-[10px] text-rose-800 leading-tight">
                                                                <strong>Catatan:</strong> {{ $lvl->evidenceUpload->rejection_note ?? 'Dokumen belum sesuai.' }}
                                                            </p>
                                                        </div>

                                                        <button type="button" @click="showDetails = !showDetails" class="text-[10px] font-bold text-rose-800 underline decoration-dotted underline-offset-2">
                                                            <span x-show="!showDetails">Details</span>
                                                            <span x-show="showDetails">Tutup Details</span>
                                                        </button>
                                                        <div x-show="showDetails" x-transition class="text-[10px] text-rose-900 bg-white/80 border border-rose-200 rounded-lg p-2 space-y-0.5">
                                                            <p><strong>Pengunggah:</strong> {{ $lvl->evidenceUpload->user->name ?? '-' }}</p>
                                                            <p><strong>Waktu Upload:</strong> {{ $lvl->evidenceUpload->uploaded_at ? $lvl->evidenceUpload->uploaded_at->timezone(config('app.timezone'))->format('d M Y H:i') . ' WITA' : '-' }}</p>
                                                        </div>

                                                        @if($canUpload)
                                                        <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" target="_blank" class="block w-full text-center bg-white border border-rose-300 text-rose-900 hover:bg-rose-100 py-1.5 rounded text-[10px] font-semibold transition">Preview File Lama</a>
                                                        <!-- Form Re-upload Revisi -->
                                                        <form action="{{ route('matlev.upload', $lvl->id) }}" method="POST" enctype="multipart/form-data" class="space-y-1.5">
                                                            @csrf
                                                            <input type="file" name="pdf_file" accept="application/pdf" required @change="selectedFile = true"
                                                                   class="block w-full text-[10px] text-stone-500 file:mr-1 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-rose-100 file:text-rose-800 hover:file:bg-rose-200 cursor-pointer">
                                                            <button type="submit" 
                                                                    :disabled="!selectedFile"
                                                                    :class="selectedFile ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-rose-300 text-rose-100 cursor-not-allowed'"
                                                                    class="w-full text-[10px] font-bold py-1.5 px-2 rounded-lg transition shadow-xs">
                                                                Upload Revisi
                                                            </button>
                                                        </form>
                                                        @else
                                                            <p class="text-[10px] text-stone-500 bg-stone-100 border border-stone-200 rounded-lg p-2">Upload level sebelumnya terlebih dahulu.</p>
                                                        @endif
                                                    </div>
                                                @endif

                                            @else
                                                @if($canUpload)
                                                <!-- Belum Ada Dokumen / Form Upload Baru (Fungsionalitas Asli) -->
                                                <form x-data="{ selectedFile: false }" action="{{ route('matlev.upload', $lvl->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                                    @csrf
                                                    <input type="file" name="pdf_file" accept="application/pdf" required @change="selectedFile = true"
                                                           class="block w-full text-[10px] text-stone-500 file:mr-1.5 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200 cursor-pointer">
                                                    <button type="submit" 
                                                            :disabled="!selectedFile"
                                                            :class="selectedFile ? 'bg-fore-900 hover:bg-fore-800 text-white' : 'bg-stone-300 text-stone-100 cursor-not-allowed'"
                                                            class="w-full text-[11px] font-bold py-1.5 px-3 rounded-lg transition shadow-xs">
                                                        Upload PDF
                                                    </button>
                                                </form>
                                                @else
                                                    <p class="text-[10px] text-stone-500 bg-stone-100 border border-stone-200 rounded-lg p-2">Upload Level {{ $lvl->level - 1 }} terlebih dahulu.</p>
                                                @endif
                                            @endif

                                            @if($lvl->evidenceUpload && $lvl->evidenceUpload->status !== 'rejected')
                                                @php
                                                    $document = $lvl->evidenceUpload;
                                                    $myPermission = $document->permissionRequests->first(fn ($permission) => (int) $permission->requester_id === (int) Auth::id() && $permission->status === 'approved' && is_null($permission->used_at));
                                                    $isOwner = (int) $document->user_id === (int) Auth::id();
                                                    $canEdit = $isOwner || ($myPermission && $myPermission->action === 'edit');
                                                    $canDelete = $isOwner || ($myPermission && $myPermission->action === 'delete');
                                                @endphp
                                                <div class="mt-2 pt-2 border-t border-stone-100 space-y-1.5">
                                                    @if($canEdit || $canDelete)
                                                        @if($myPermission)
                                                            <p class="text-[10px] text-emerald-700 font-bold">Izin pemilik disetujui untuk aksi {{ $myPermission->action === 'edit' ? 'penggantian' : 'penghapusan' }}.</p>
                                                        @endif
                                                        @if($canEdit)
                                                            <form action="{{ route('documents.edit', $document) }}" method="POST" enctype="multipart/form-data" class="space-y-1">
                                                                @csrf
                                                                <input type="file" name="pdf_file" accept="application/pdf" required class="block w-full text-[10px] text-stone-500 file:mr-1 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:bg-blue-100 file:text-blue-800 cursor-pointer">
                                                                <button class="w-full text-[10px] font-bold py-1.5 px-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Ganti Dokumen</button>
                                                            </form>
                                                        @endif
                                                        @if($canDelete)
                                                            <form action="{{ route('documents.delete', $document) }}" method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="w-full text-[10px] font-bold py-1.5 px-2 rounded-lg bg-white border border-rose-300 text-rose-700 hover:bg-rose-50">Hapus Dokumen</button>
                                                            </form>
                                                        @endif
                                                    @elseif($document->permissionRequests->first(fn ($permission) => (int) $permission->requester_id === (int) Auth::id() && $permission->status === 'pending'))
                                                        <p class="text-[10px] text-amber-700 font-semibold">Permintaan izin sedang menunggu persetujuan pemilik.</p>
                                                    @else
                                                        <div class="grid grid-cols-2 gap-1.5">
                                                            <form action="{{ route('documents.permission.request', $document) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="action" value="edit">
                                                                <button class="w-full text-[10px] font-bold py-1.5 px-1 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100">Minta Izin Edit</button>
                                                            </form>
                                                            <form action="{{ route('documents.permission.request', $document) }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="action" value="delete">
                                                                <button class="w-full text-[10px] font-bold py-1.5 px-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100">Minta Izin Hapus</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-stone-400 col-span-5">Belum ada indikator level kematangan.</p>
                                @endforelse
                            </div>

                        </div>
                    @empty
                        <p class="text-xs text-stone-400">Belum ada subkriteria pada kriteria ini.</p>
                    @endforelse
                </div>

            </div>
        @empty
            <div class="bg-white p-12 text-center rounded-3xl border border-stone-200 text-stone-400 text-sm">
                Belum ada data kriteria di dalam sistem.
            </div>
        @endforelse
    </div>

</div>
@endsection
