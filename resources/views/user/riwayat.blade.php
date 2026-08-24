@extends('layouts.fore')

@section('title', 'Riwayat Aktivitas')

@section('content')
<div class="space-y-8" x-data="{ activityFilter: 'all', actorFilter: 'all' }">
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm">
        <div class="space-y-1 mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-stone-100 text-stone-800 text-xs font-bold">
                <span>LOG AKTIVITAS</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 font-display">Riwayat Aktivitas</h1>
            <p class="text-stone-600 text-xs sm:text-sm">Pantau seluruh aktivitas upload, izin, dan penilaian dokumen.</p>
        </div>

        <div class="inline-flex flex-wrap gap-1.5 bg-stone-100 p-1.5 rounded-2xl border border-stone-200 text-xs w-fit max-w-full">
            @foreach([
                'all' => 'Semua Aktivitas',
                'upload' => 'Upload',
                'revision_upload' => 'Upload File Revisi',
                'permission_request' => 'Minta Izin',
                'permission_granted' => 'Mengizinkan',
                'evaluation' => 'Menilai',
                'delete' => 'Menghapus',
            ] as $type => $label)
                <button type="button" @click="activityFilter = '{{ $type }}'"
                        :class="activityFilter === '{{ $type }}' ? 'bg-pln-900 text-white font-bold' : 'text-stone-700 hover:bg-white hover:text-stone-900'"
                        class="px-3.5 py-2 rounded-xl transition-all duration-200">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="mt-3 inline-flex flex-wrap gap-1.5 bg-stone-100 p-1.5 rounded-2xl border border-stone-200 text-xs w-fit max-w-full">
            @foreach([
                'all' => 'Semua Aktor',
                'mine' => 'Saya',
                'team' => 'Tim',
            ] as $actorType => $label)
                <button type="button" @click="actorFilter = '{{ $actorType }}'"
                        :class="actorFilter === '{{ $actorType }}' ? 'bg-emerald-700 text-white font-bold' : 'text-stone-700 hover:bg-white hover:text-stone-900'"
                        class="px-3.5 py-2 rounded-xl transition-all duration-200">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-stone-700">
                <thead class="bg-stone-100/90 text-stone-600 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
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
                            $requester = $log->targetUser->name ?? 'user';
                            $filename = $log->filename ?? 'dokumen';
                            $description = match ($log->activity_type) {
                                'upload' => "$actor mengupload file $filename",
                                'revision_upload' => "$actor mengupload file revisi $filename",
                                'permission_request' => "$actor meminta izin ganti file $filename",
                                'permission_granted' => "$actor mengizinkan $requester untuk mengganti file $filename",
                                'permission_rejected' => "$actor menolak permintaan ganti file $filename",
                                'evaluation' => 'Verifikator ' . ($log->status === 'approved' ? 'menyetujui' : 'menolak') . " file $filename",
                                'delete' => "$actor menghapus file $filename",
                                default => "$actor melakukan aktivitas pada file $filename",
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
                            <td class="p-4 sm:px-6 font-semibold text-stone-800 whitespace-nowrap">{{ $actor }}</td>
                            <td class="p-4 sm:px-6 text-stone-800">{{ $log->maturityLevel->subkriteria->title ?? '-' }}</td>
                            <td class="p-4 sm:px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-stone-100 text-pln-900 font-extrabold text-xs rounded-full border border-stone-200">Lvl {{ $log->maturityLevel->level ?? '-' }}</span>
                            </td>
                            <td class="p-4 sm:px-6 text-stone-700 min-w-[280px]">{{ $description }}</td>
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
@endsection
