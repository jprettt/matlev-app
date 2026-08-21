@extends('layouts.fore')

@section('title', 'Riwayat Aktivitas Pengunggahan')

@section('content')
<div class="space-y-8" x-data="{
    activeHistoryTab: 'mine',
    statusFilter: 'all',
    matchesFilter(status) {
        if (this.statusFilter === 'all') return true;
        return this.statusFilter === status;
    }
}">

    <!-- PAGE HEADER (FORE STYLE) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-stone-100 text-stone-800 text-xs font-bold">
                <span>🕒 LOG & AUDIT TRAIL</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-display">
                Riwayat Aktivitas Pengunggahan
            </h1>
            <p class="text-stone-600 text-xs sm:text-sm leading-relaxed">
                Pantau riwayat upload Anda sendiri dan bandingkan dengan riwayat seluruh user dalam satu halaman.
            </p>
        </div>

        <div class="space-y-2.5">
            <div class="flex flex-wrap gap-1.5 bg-stone-100 p-1.5 rounded-full border border-stone-200 text-xs">
                <button @click="activeHistoryTab = 'mine'; statusFilter = 'all'"
                        :class="activeHistoryTab === 'mine' ? 'bg-fore-900 text-white font-bold' : 'text-stone-600 hover:text-stone-900'"
                        class="px-3.5 py-1.5 rounded-full transition-all duration-200">
                    Riwayat Saya ({{ count($myHistories) }})
                </button>
                <button @click="activeHistoryTab = 'all'; statusFilter = 'all'"
                        :class="activeHistoryTab === 'all' ? 'bg-fore-900 text-white font-bold' : 'text-stone-600 hover:text-stone-900'"
                        class="px-3.5 py-1.5 rounded-full transition-all duration-200">
                    Riwayat Semua User ({{ count($allHistories) }})
                </button>
            </div>

            <!-- Filter status -->
            <div class="flex flex-wrap gap-1.5 bg-cream-100 p-1.5 rounded-full border border-stone-200 text-xs">
            <button @click="statusFilter = 'all'" 
                    :class="statusFilter === 'all' ? 'bg-fore-900 text-white font-bold' : 'text-stone-600 hover:text-stone-900'"
                    class="px-3.5 py-1.5 rounded-full transition-all duration-200">
                <span x-show="activeHistoryTab === 'mine'">Semua ({{ $myStats['total'] }})</span>
                <span x-show="activeHistoryTab === 'all'">Semua ({{ count($allHistories) }})</span>
            </button>
            <button @click="statusFilter = 'approved'" 
                    :class="statusFilter === 'approved' ? 'bg-emerald-700 text-white font-bold' : 'text-stone-600 hover:text-emerald-800'"
                    class="px-3.5 py-1.5 rounded-full transition-all duration-200">
                <span x-show="activeHistoryTab === 'mine'">Disetujui ({{ $myStats['approved'] }})</span>
                <span x-show="activeHistoryTab === 'all'">Disetujui ({{ $stats['totalApproved'] }})</span>
            </button>
            <button @click="statusFilter = 'pending'" 
                    :class="statusFilter === 'pending' ? 'bg-amber-600 text-white font-bold' : 'text-stone-600 hover:text-amber-800'"
                    class="px-3.5 py-1.5 rounded-full transition-all duration-200">
                <span x-show="activeHistoryTab === 'mine'">Menunggu ({{ $myStats['pending'] }})</span>
                <span x-show="activeHistoryTab === 'all'">Menunggu ({{ $stats['totalPending'] }})</span>
            </button>
            <button @click="statusFilter = 'rejected'" 
                    :class="statusFilter === 'rejected' ? 'bg-rose-600 text-white font-bold' : 'text-stone-600 hover:text-rose-800'"
                    class="px-3.5 py-1.5 rounded-full transition-all duration-200">
                <span x-show="activeHistoryTab === 'mine'">Ditolak ({{ $myStats['rejected'] }})</span>
                <span x-show="activeHistoryTab === 'all'">Ditolak ({{ $stats['totalRejected'] }})</span>
            </button>
            </div>
        </div>
    </div>

    <!-- TABLE RIWAYAT -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-stone-700">
                <thead class="bg-cream-100/90 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                    <tr>
                        <th class="p-4 sm:px-6">Waktu Upload</th>
                        <th class="p-4 sm:px-6">Pengunggah</th>
                        <th class="p-4 sm:px-6">Kriteria & Sub</th>
                        <th class="p-4 sm:px-6">Level</th>
                        <th class="p-4 sm:px-6">Nama Berkas</th>
                        <th class="p-4 sm:px-6">Status</th>
                        <th class="p-4 sm:px-6">Catatan Evaluator</th>
                        <th class="p-4 sm:px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @php
                        $combinedHistories = array_merge(
                            array_map(fn($item) => $item + ['tab_scope' => 'mine'], $myHistories),
                            array_map(fn($item) => $item + ['tab_scope' => 'all'], $allHistories)
                        );
                    @endphp
                    @foreach($combinedHistories as $history)
                        <tr class="hover:bg-cream-100/40 transition-colors"
                            x-show="activeHistoryTab === '{{ $history['tab_scope'] }}' && matchesFilter('{{ $history['status'] }}')">
                            <!-- Waktu Upload -->
                            <td class="p-4 sm:px-6 text-stone-500 text-xs whitespace-nowrap">
                                <span class="font-bold text-stone-700 block">{{ \Carbon\Carbon::parse($history['time'])->format('d M Y') }}</span>
                                <span class="text-[11px] text-stone-400">{{ \Carbon\Carbon::parse($history['time'])->format('H:i') }} WIB</span>
                            </td>

                            <!-- Pengunggah -->
                            <td class="p-4 sm:px-6 text-xs text-stone-700 whitespace-nowrap">
                                {{ $history['uploader'] ?? '-' }}
                            </td>

                            <!-- Kriteria / Sub -->
                            <td class="p-4 sm:px-6">
                                <span class="inline-block px-2 py-0.5 rounded bg-stone-100 font-mono text-[10px] font-bold text-stone-700 mb-0.5">
                                    {{ $history['criteria_code'] }} - {{ $history['sub_code'] }}
                                </span>
                                <p class="font-bold text-stone-900 text-xs truncate max-w-[200px]" title="{{ $history['sub_title'] }}">
                                    {{ $history['sub_title'] }}
                                </p>
                            </td>

                            <!-- Level -->
                            <td class="p-4 sm:px-6">
                                <span class="px-2.5 py-1 bg-stone-100 text-fore-900 font-extrabold text-xs rounded-full border border-stone-200 whitespace-nowrap">
                                    Lvl {{ $history['level'] }}
                                </span>
                            </td>

                            <!-- Nama File -->
                            <td class="p-4 sm:px-6">
                                <div class="flex items-center gap-2 max-w-[220px]">
                                    <span class="text-rose-600 font-bold text-xs">PDF</span>
                                    <span class="truncate text-stone-800 text-xs font-medium" title="{{ $history['filename'] }}">
                                        {{ $history['filename'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="p-4 sm:px-6 whitespace-nowrap">
                                @if($history['status'] == 'pending')
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-1 rounded-full text-[11px] font-bold">
                                        <span>⏳</span>
                                        <span>Menunggu Penilaian</span>
                                    </span>
                                @elseif($history['status'] == 'approved')
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-200 px-2.5 py-1 rounded-full text-[11px] font-bold">
                                        <span>✓</span>
                                        <span>Disetujui</span>
                                    </span>
                                @elseif($history['status'] == 'rejected')
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-800 border border-rose-200 px-2.5 py-1 rounded-full text-[11px] font-bold">
                                        <span>✕</span>
                                        <span>Ditolak / Perlu Revisi</span>
                                    </span>
                                @elseif($history['status'] == 'deleted')
                                    <span class="inline-flex items-center gap-1 bg-stone-100 text-stone-700 border border-stone-300 px-2.5 py-1 rounded-full text-[11px] font-bold">
                                        <span>🗑</span>
                                        <span>File Dihapus</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Catatan Evaluator -->
                            <td class="p-4 sm:px-6 text-xs text-stone-500 max-w-xs">
                                @if($history['status'] == 'deleted')
                                    <span class="text-rose-800 bg-rose-50/70 p-1.5 rounded-lg border border-rose-100 block">
                                        File dihapus{{ $history['deleted_by'] ? ' oleh ' . $history['deleted_by'] : '' }}{{ $history['deleted_at'] ? ' pada ' . \Carbon\Carbon::parse($history['deleted_at'])->format('d M Y H:i') : '' }}.
                                    </span>
                                @elseif($history['note'])
                                    <span class="text-rose-800 italic bg-rose-50/70 p-1.5 rounded-lg border border-rose-100 block">
                                        "{{ $history['note'] }}"
                                    </span>
                                @else
                                    <span class="text-stone-300">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="p-4 sm:px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($history['status'] !== 'deleted')
                                        <a href="{{ asset('storage/' . $history['file_path']) }}" target="_blank" 
                                           class="px-2.5 py-1 bg-white border border-stone-300 hover:border-fore-600 hover:text-fore-900 text-stone-700 text-xs font-semibold rounded-lg transition shadow-2xs">
                                            Preview
                                        </a>
                                        <a href="{{ asset('storage/' . $history['file_path']) }}" download 
                                           class="px-2.5 py-1 bg-fore-900 hover:bg-fore-800 text-white text-xs font-semibold rounded-lg transition shadow-2xs">
                                            Unduh
                                        </a>
                                    @else
                                        <span class="text-[11px] text-stone-400">File fisik sudah dihapus</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <tr x-show="activeHistoryTab === 'mine' && {{ count($myHistories) }} === 0">
                        <td colspan="8" class="p-12 text-center text-stone-400 text-sm">
                            Anda belum pernah mengunggah berkas eviden.
                        </td>
                    </tr>
                    <tr x-show="activeHistoryTab === 'all' && {{ count($allHistories) }} === 0">
                        <td colspan="8" class="p-12 text-center text-stone-400 text-sm">
                            Belum ada berkas eviden yang diunggah ke dalam sistem.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
