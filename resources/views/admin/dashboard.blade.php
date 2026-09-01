@extends('layouts.admin')

@section('title', 'Dashboard Verifikator')

@section('content')
<div class="space-y-6">
    <div class="py-2 text-stone-950">
        <p class="text-xs uppercase tracking-[0.2em] text-amber-950 font-bold">Pusat Verifikasi</p>
        <h1 class="text-2xl font-extrabold font-display mt-2">Dashboard Verifikasi Dokumen</h1>
        <p class="text-sm text-amber-950/80 mt-1">Pantau status dokumen dan kelola berkas pending dari semua unit kerja.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Card -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Total Dokumen</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ $totalCount }}</p>
                </div>
                <div class="text-4xl text-blue-300">📊</div>
            </div>
            <p class="text-xs text-blue-600 mt-3">Seluruh dokumen yang diupload</p>
        </div>

        <!-- Pending Card -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide">Menunggu Verifikasi</p>
                    <p class="text-3xl font-bold text-orange-900 mt-2">{{ $pendingCount }}</p>
                </div>
                <div class="text-4xl text-orange-300">⏳</div>
            </div>
            <p class="text-xs text-orange-600 mt-3">Dokumen yang belum diproses</p>
        </div>

        <!-- Approved Card -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wide">Disetujui</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ $approvedCount }}</p>
                </div>
                <div class="text-4xl text-green-300">✅</div>
            </div>
            <p class="text-xs text-green-600 mt-3">Dokumen yang disetujui</p>
        </div>

        <!-- Rejected Card -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Ditolak</p>
                    <p class="text-3xl font-bold text-red-900 mt-2">{{ $rejectedCount }}</p>
                </div>
                <div class="text-4xl text-red-300">❌</div>
            </div>
            <p class="text-xs text-red-600 mt-3">Dokumen yang ditolak</p>
        </div>
    </div>

    <!-- Pie Chart -->
    @if($totalCount > 0)
    @php
        $circumference = 2 * pi() * 42;
        $approvedPercent = $totalCount > 0 ? round(($approvedCount / $totalCount) * 100) : 0;
        $pendingPercent = $totalCount > 0 ? round(($pendingCount / $totalCount) * 100) : 0;
        $rejectedPercent = $totalCount > 0 ? round(($rejectedCount / $totalCount) * 100) : 0;
        $approvedDash = $totalCount > 0 ? ($approvedCount / $totalCount) * $circumference : 0;
        $pendingDash = $totalCount > 0 ? ($pendingCount / $totalCount) * $circumference : 0;
        $rejectedDash = $totalCount > 0 ? ($rejectedCount / $totalCount) * $circumference : 0;
    @endphp
    <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-stone-900 mb-5 uppercase tracking-wide">Persentase Status Dokumen</h3>
        <div class="flex flex-col lg:flex-row items-center gap-6">
            <div class="relative h-52 w-52 shrink-0">
                <svg class="h-52 w-52 -rotate-90" viewBox="0 0 120 120" aria-label="Pie chart status dokumen">
                    <circle cx="60" cy="60" r="42" fill="none" stroke="#e7e5e4" stroke-width="18"/>
                    @if($approvedCount > 0)
                        <circle cx="60" cy="60" r="42" fill="none" stroke="#22c55e" stroke-width="18" stroke-linecap="round" stroke-dasharray="{{ $approvedDash }} {{ $circumference - $approvedDash }}" stroke-dashoffset="0" transform="rotate(-90 60 60)"/>
                    @endif
                    @if($pendingCount > 0)
                        <circle cx="60" cy="60" r="42" fill="none" stroke="#f59e0b" stroke-width="18" stroke-linecap="round" stroke-dasharray="{{ $pendingDash }} {{ $circumference - $pendingDash }}" stroke-dashoffset="-{{ $approvedDash }}" transform="rotate(-90 60 60)"/>
                    @endif
                    @if($rejectedCount > 0)
                        <circle cx="60" cy="60" r="42" fill="none" stroke="#ef4444" stroke-width="18" stroke-linecap="round" stroke-dasharray="{{ $rejectedDash }} {{ $circumference - $rejectedDash }}" stroke-dashoffset="-{{ $approvedDash + $pendingDash }}" transform="rotate(-90 60 60)"/>
                    @endif
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                    <span class="text-3xl font-black text-stone-900">{{ $totalCount }}</span>
                    <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-500">Dokumen</span>
                </div>
            </div>

            <div class="w-full space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                        <span class="text-sm font-semibold text-emerald-700">Disetujui</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-bold text-emerald-700">{{ $approvedPercent }}%</span>
                        <span class="text-[11px] text-emerald-600">{{ $approvedCount }} dokumen</span>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                        <span class="text-sm font-semibold text-amber-700">Menunggu</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-bold text-amber-700">{{ $pendingPercent }}%</span>
                        <span class="text-[11px] text-amber-600">{{ $pendingCount }} dokumen</span>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50 px-3 py-2">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full bg-red-500"></span>
                        <span class="text-sm font-semibold text-red-700">Ditolak</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-bold text-red-700">{{ $rejectedPercent }}%</span>
                        <span class="text-[11px] text-red-600">{{ $rejectedCount }} dokumen</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Uploads & Top Contributors Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Uploads -->
        <div class="lg:col-span-2 bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-stone-900 mb-4 uppercase tracking-wide">📄 Upload Terbaru</h3>
            <div class="space-y-3 max-h-72 overflow-y-auto">
                @forelse($recentUploads as $upload)
                    <div class="flex items-start gap-3 pb-3 border-b border-stone-100 last:border-b-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-stone-900 truncate">{{ $upload->user->name ?? 'Tidak diketahui' }}</p>
                            <p class="text-xs text-stone-600 mt-1">{{ $upload->maturityLevel->subkriteria->kriteria->code ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</p>
                            <p class="text-[11px] text-stone-500 mt-1">{{ $upload->uploaded_at ? $upload->uploaded_at->format('d M Y H:i') : '-' }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            @if($upload->status === 'approved')
                                <span class="inline-block px-2 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">✓ Disetujui</span>
                            @elseif($upload->status === 'pending')
                                <span class="inline-block px-2 py-1 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700">⏳ Pending</span>
                            @elseif($upload->status === 'rejected')
                                <span class="inline-block px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700">✕ Ditolak</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 text-center py-4">Tidak ada upload</p>
                @endforelse
            </div>
        </div>

        <!-- Top Contributors -->
        <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-stone-900 mb-4 uppercase tracking-wide">👥 Top Kontributor</h3>
            <div class="space-y-3">
                @forelse($topContributors as $index => $contributor)
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-xs font-bold text-blue-900">{{ $index + 1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-stone-900 truncate">{{ $contributor->user->name ?? 'Tidak diketahui' }}</p>
                            <p class="text-[11px] text-stone-500">{{ $contributor->total_uploads }} dokumen</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-stone-400 text-center py-4">Belum ada kontributor</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Action Button to Queue -->
    <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 shadow-sm text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold">Mulai Penilaian Kinerja</h3>
                <p class="text-sm text-amber-100 mt-1">Masuk ke penilaian kinerja untuk meninjau {{ $pendingCount }} dokumen yang menunggu</p>
            </div>
            <a href="{{ route('admin.queue') }}" class="inline-block bg-amber-900 hover:bg-amber-950 text-white font-bold py-3 px-6 rounded-xl transition">
                Ke Penilaian Kinerja →
            </a>
        </div>
    </div>
</div>
@endsection