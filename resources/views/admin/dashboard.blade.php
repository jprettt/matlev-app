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

    <!-- Progress Bar -->
    @if($totalCount > 0)
    <div class="bg-white border border-stone-200 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-stone-900 mb-4 uppercase tracking-wide">Persentase Status Dokumen</h3>
        <div class="space-y-3">
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-green-600">Disetujui</span>
                    <span class="text-green-600">{{ round(($approvedCount/$totalCount)*100) }}%</span>
                </div>
                <div class="w-full bg-stone-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ round(($approvedCount/$totalCount)*100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-orange-600">Menunggu</span>
                    <span class="text-orange-600">{{ round(($pendingCount/$totalCount)*100) }}%</span>
                </div>
                <div class="w-full bg-stone-200 rounded-full h-2">
                    <div class="bg-orange-500 h-2 rounded-full" style="width: {{ round(($pendingCount/$totalCount)*100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs font-semibold mb-1">
                    <span class="text-red-600">Ditolak</span>
                    <span class="text-red-600">{{ round(($rejectedCount/$totalCount)*100) }}%</span>
                </div>
                <div class="w-full bg-stone-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ round(($rejectedCount/$totalCount)*100) }}%"></div>
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