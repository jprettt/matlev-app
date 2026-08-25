@extends('layouts.fore')

@section('title', 'Riwayat Aktivitas')

@section('content')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
<div class="space-y-8" x-data="{ activityFilter: 'all', actorFilter: 'all', openFilter: null }" @click.window="openFilter = null">
    <div class="w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 pt-6 sm:pt-10 pb-6">
        <div class="space-y-1 mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-stone-100 text-stone-800 text-xs font-bold">
                <span>LOG AKTIVITAS</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-display">Riwayat Aktivitas</h1>
            <p class="text-stone-600 text-xs sm:text-sm">Pantau seluruh aktivitas upload, izin, dan penilaian dokumen.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <div class="relative" @click.stop>
                <button type="button" @click.stop="openFilter = openFilter === 'activity' ? null : 'activity'"
                        class="inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-stone-100 px-3.5 py-2.5 font-bold text-stone-700 hover:bg-stone-200 transition-colors">
                    <svg class="h-4 w-4 text-pln-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16l-6 7v5l-4 2v-7L4 5z" />
                    </svg>
                    <span>Aktivitas:</span>
                    <span class="text-pln-900" x-text="{ all: 'Semua Aktivitas', upload: 'Upload', revision_upload: 'Upload File Revisi', permission_request: 'Minta Izin', permission_granted: 'Mengizinkan', evaluation: 'Menilai', delete: 'Menghapus' }[activityFilter]"></span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                </button>
                <div x-show="openFilter === 'activity'" x-transition class="absolute left-0 z-20 mt-2 w-52 rounded-xl border border-stone-200 bg-white p-1.5 shadow-xl" x-cloak>
                    @foreach([
                        'all' => 'Semua Aktivitas',
                        'upload' => 'Upload',
                        'revision_upload' => 'Upload File Revisi',
                        'permission_request' => 'Minta Izin',
                        'permission_granted' => 'Mengizinkan',
                        'evaluation' => 'Menilai',
                        'delete' => 'Menghapus',
                    ] as $type => $label)
                        <button type="button" @click.stop="activityFilter = '{{ $type }}'; openFilter = null"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-stone-700 hover:bg-stone-100 transition-colors">
                            <span>{{ $label }}</span>
                            <span x-show="activityFilter === '{{ $type }}'" class="font-bold text-pln-900">✓</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="relative" @click.stop>
                <button type="button" @click.stop="openFilter = openFilter === 'actor' ? null : 'actor'"
                        class="inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-stone-100 px-3.5 py-2.5 font-bold text-stone-700 hover:bg-stone-200 transition-colors">
                    <svg class="h-4 w-4 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 8a7 7 0 0114 0" />
                    </svg>
                    <span>Aktor:</span>
                    <span class="text-emerald-700" x-text="{ all: 'Semua Aktor', mine: 'Saya', team: 'Tim' }[actorFilter]"></span>
                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                </button>
                <div x-show="openFilter === 'actor'" x-transition class="absolute left-0 z-20 mt-2 w-44 rounded-xl border border-stone-200 bg-white p-1.5 shadow-xl" x-cloak>
                    @foreach([
                        'all' => 'Semua Aktor',
                        'mine' => 'Saya',
                        'team' => 'Tim',
                    ] as $actorType => $label)
                        <button type="button" @click.stop="actorFilter = '{{ $actorType }}'; openFilter = null"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-stone-700 hover:bg-stone-100 transition-colors">
                            <span>{{ $label }}</span>
                            <span x-show="actorFilter === '{{ $actorType }}'" class="font-bold text-emerald-700">✓</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm text-stone-700">
                <thead class="bg-stone-100/90 text-stone-600 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200 text-center">
                    <tr>
                        <th class="p-4 sm:px-6">Waktu</th>
                        <th class="p-4 sm:px-6">Aktor</th>
                        <th class="p-4 sm:px-6">Sub Kriteria</th>
                        <th class="p-4 sm:px-6">Level</th>
                        <th class="p-4 sm:px-6">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($activityLogs as $log)
                        @php
                            $actor = $log->actor->name ?? 'User';
                            $actorRole = $log->actor->role ?? 'user';
                            $requester = $log->targetUser->name ?? 'user';
                            $filename = $log->filename ?? 'dokumen';
                            [$descriptionBeforeFile, $descriptionAfterFile] = match ($log->activity_type) {
                                'upload' => ["$actor mengupload file", ''],
                                'revision_upload' => ["$actor mengupload file revisi", ''],
                                'permission_request' => ["$actor meminta izin ganti file", ''],
                                'permission_granted' => ["$actor mengizinkan $requester untuk mengganti file", ''],
                                'permission_rejected' => ["$actor menolak permintaan ganti file", ''],
                                'evaluation' => ['Verifikator ' . ($log->status === 'approved' ? 'menyetujui' : 'menolak') . ' file', ''],
                                'delete' => ["$actor menghapus file", ''],
                                default => ["$actor melakukan aktivitas pada file", ''],
                            };
                            $typeForFilter = in_array($log->activity_type, ['permission_request', 'permission_rejected'], true)
                                ? 'permission_request'
                                : $log->activity_type;
                            $actorForFilter = (int) $log->actor_id === (int) Auth::id()
                                ? 'mine'
                                : (($log->actor->role ?? null) === 'user' ? 'team' : 'admin');
                            $detailUrl = route('user.kriteria', ['level' => $log->maturity_level_id]) . '#level-' . $log->maturity_level_id;
                        @endphp
                        <tr @click="window.location.href = '{{ $detailUrl }}'"
                            x-show="(activityFilter === 'all' || activityFilter === '{{ $typeForFilter }}') && (actorFilter === 'all' || actorFilter === '{{ $actorForFilter }}' || (actorFilter === 'team' && '{{ $actorForFilter }}' === 'mine'))"
                            class="cursor-pointer hover:bg-blue-50/60 transition-colors focus-within:bg-blue-50/60"
                            tabindex="0"
                            @keydown.enter="window.location.href = '{{ $detailUrl }}'">
                            <td class="p-4 sm:px-6 whitespace-nowrap">
                                <span class="font-bold text-stone-800 block">{{ $log->occurred_at?->timezone(config('app.timezone'))->format('d M Y') ?? '-' }}</span>
                                <span class="text-[11px] text-stone-500">{{ $log->occurred_at?->timezone(config('app.timezone'))->format('H:i') ?? '-' }} WITA</span>
                            </td>
                            <td class="p-4 sm:px-6 text-center whitespace-nowrap">
                                <span class="font-bold {{ $actorRole === 'user' ? 'text-pln-700' : 'text-rose-700' }}">
                                    {{ $actor }}
                                </span>
                            </td>
                            <td class="p-4 sm:px-6 text-stone-800">{{ $log->maturityLevel->subkriteria->title ?? '-' }}</td>
                            <td class="p-4 sm:px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-stone-100 text-pln-900 font-extrabold text-xs rounded-full border border-stone-200">Lvl {{ $log->maturityLevel->level ?? '-' }}</span>
                            </td>
                            <td class="p-4 sm:px-6 text-stone-700 min-w-[280px]">
                                {{ $descriptionBeforeFile }}
                                <span class="font-bold text-emerald-700">{{ $filename }}</span>
                                {{ $descriptionAfterFile }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-stone-500">Belum ada aktivitas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
