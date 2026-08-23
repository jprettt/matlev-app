<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengajuan Dokumen Maturity Level</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js untuk sistem Tab & Interaktivitas -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-950 text-white min-h-screen p-6" x-data="{ currentTab: 'dashboard' }">

    <div class="max-w-7xl mx-auto">
        
        <!-- Header & Logout -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-800">
            <div>
                <h1 class="text-2xl font-bold">Dashboard Pengajuan Dokumen Maturity Level</h1>
                <p class="text-sm text-slate-400">Selamat datang, {{ auth()->user()->name ?? 'Pengguna' }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2 px-4 rounded transition">
                    Logout
                </button>
            </form>
        </div>

        <!-- Alert Pesan Sukses / Error -->
        @if(session('success'))
            <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-900/50 border border-rose-500 text-rose-200 px-4 py-3 rounded mb-6 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-amber-900/50 border border-amber-500 text-amber-200 px-4 py-3 rounded mb-6 text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Hitung Statistik Data untuk Tab & Widget -->
        @php
            $totalSlots = 0;
            $totalApproved = 0;
            $totalPending = 0;
            $totalRejected = 0;
            $rejectedItems = [];
            $allHistories = [];

            foreach($criterias as $crit) {
                foreach($crit->subKriterias as $sub) {
                    foreach($sub->maturityLevels as $lvl) {
                        $totalSlots++;
                        if($lvl->evidenceUpload) {
                            $st = $lvl->evidenceUpload->status ?? 'pending';
                            if($st == 'approved') $totalApproved++;
                            elseif($st == 'pending') $totalPending++;
                            elseif($st == 'rejected') {
                                $totalRejected++;
                                $rejectedItems[] = [
                                    'criteria' => $crit->title ?? $crit->nama,
                                    'sub' => $sub->title ?? $sub->nama,
                                    'level' => $lvl->level,
                                    'requirement' => $lvl->evidence_requirement,
                                    'upload' => $lvl->evidenceUpload
                                ];
                            }

                            // Kumpulkan data untuk tab riwayat
                            $allHistories[] = [
                                'criteria_code' => $crit->code ?? $crit->kode ?? '',
                                'sub_code' => $sub->code ?? $sub->kode ?? '',
                                'level' => $lvl->level,
                                'filename' => $lvl->evidenceUpload->original_filename,
                                'status' => $st,
                                'note' => $lvl->evidenceUpload->rejection_note,
                                'time' => $lvl->evidenceUpload->uploaded_at ?? $lvl->evidenceUpload->created_at
                            ];
                        }
                    }
                }
            }
            usort($allHistories, function ($a, $b) {
                return \Carbon\Carbon::parse($b['time'])->timestamp <=> \Carbon\Carbon::parse($a['time'])->timestamp;
            });
            $globalPercent = $totalSlots > 0 ? round(($totalApproved / $totalSlots) * 100) : 0;
        @endphp

        <!-- NAVIGASI TAB UTAMA -->
        <div class="flex flex-wrap gap-2 mb-8 bg-slate-900 p-2 rounded-xl border border-slate-800">
            <button @click="currentTab = 'dashboard'" :class="currentTab === 'dashboard' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="flex-1 min-w-[140px] text-xs font-semibold py-2.5 px-4 rounded-lg transition text-center">
                📊 Dashboard
            </button>
            <button @click="currentTab = 'criterias'" :class="currentTab === 'criterias' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="flex-1 min-w-[140px] text-xs font-semibold py-2.5 px-4 rounded-lg transition text-center">
                📁 Daftar Kriteria & Upload
            </button>
            <button @click="currentTab = 'revisions'" :class="currentTab === 'revisions' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="flex-1 min-w-[140px] text-xs font-semibold py-2.5 px-4 rounded-lg transition text-center flex items-center justify-center gap-1.5">
                ❌ Perlu Revisi 
                @if($totalRejected > 0)
                    <span class="bg-white text-rose-600 px-1.5 py-0.2 rounded-full text-[10px] font-bold">{{ $totalRejected }}</span>
                @endif
            </button>
            <button @click="currentTab = 'history'" :class="currentTab === 'history' ? 'bg-cyan-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800'" class="flex-1 min-w-[140px] text-xs font-semibold py-2.5 px-4 rounded-lg transition text-center">
                🕒 Riwayat Aktivitas
            </button>
        </div>


        <!-- ========================================== -->
        <!-- TAB 1: DASHBOARD / RINGKASAN -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'dashboard'" x-transition class="space-y-6">
            
            <!-- Global Progress Bar Card -->
            <div class="bg-slate-900 p-6 rounded-xl border border-slate-800">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-base font-semibold text-cyan-400">Pencapaian Keseluruhan Maturity Level</h2>
                    <span class="text-sm font-bold text-cyan-400">{{ $globalPercent }}% Selesai</span>
                </div>
                <div class="w-full bg-slate-950 rounded-full h-4 p-0.5 border border-slate-800">
                    <div class="bg-cyan-500 h-3 rounded-full transition-all duration-500" style="width: {{ $globalPercent }}%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-2">Total {{ $totalApproved }} dari {{ $totalSlots }} dokumen level telah divalidasi dan disetujui oleh admin.</p>
            </div>

            <!-- Widget Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400">Total Slot Dokumen</p>
                        <p class="text-xl font-bold text-white">{{ $totalSlots }} Level</p>
                    </div>
                    <div class="p-3 bg-slate-800 rounded-lg text-slate-300">📁</div>
                </div>
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400">Menunggu Penilaian</p>
                        <p class="text-xl font-bold text-amber-400">{{ $totalPending }} File</p>
                    </div>
                    <div class="p-3 bg-amber-950/50 rounded-lg text-amber-400">⏳</div>
                </div>
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400">Disetujui Admin</p>
                        <p class="text-xl font-bold text-emerald-400">{{ $totalApproved }} File</p>
                    </div>
                    <div class="p-3 bg-emerald-950/50 rounded-lg text-emerald-400">✅</div>
                </div>
                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-400">Perlu Revisi (Ditolak)</p>
                        <p class="text-xl font-bold text-rose-400">{{ $totalRejected }} File</p>
                    </div>
                    <div class="p-3 bg-rose-950/50 rounded-lg text-rose-400">❌</div>
                </div>
            </div>

            <!-- Welcome Banner / Quick Guide -->
            <div class="bg-gradient-to-r from-cyan-950/40 to-slate-900 p-6 rounded-xl border border-cyan-900/50 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="font-semibold text-white text-sm mb-1">Panduan Pengisian Cepat</h3>
                    <p class="text-xs text-slate-300">Silakan buka tab <strong>"Daftar Kriteria & Upload"</strong> untuk mulai melampirkan dokumen PDF pada masing-masing level yang tersedia.</p>
                </div>
                <button @click="currentTab = 'criterias'" class="bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold py-2 px-4 rounded-lg transition whitespace-nowrap">
                    Mulai Upload Dokumen &rarr;
                </button>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- TAB 2: DAFTAR KRITERIA & UPLOAD -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'criterias'" x-transition class="space-y-6">
            @forelse($criterias as $criteria)
                @php
                    $critSlots = 0;
                    $critApproved = 0;
                    foreach($criteria->subKriterias as $sub) {
                        foreach($sub->maturityLevels as $lvl) {
                            $critSlots++;
                            if($lvl->evidenceUpload && $lvl->evidenceUpload->status == 'approved') {
                                $critApproved++;
                            }
                        }
                    }
                    $critPercent = $critSlots > 0 ? round(($critApproved / $critSlots) * 100) : 0;
                @endphp

                <div class="bg-slate-900 p-6 rounded-xl border border-slate-800">
                    <!-- Header Kriteria & Progress Bar -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b border-slate-800 gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-cyan-400">
                                {{ $criteria->code ?? $criteria->kode ?? '' }} {{ $criteria->title ?? $criteria->nama ?? '' }}
                            </h2>
                            <p class="text-xs text-slate-400">Pencapaian: {{ $critApproved }} / {{ $critSlots }} level valid ({{ $critPercent }}%)</p>
                        </div>
                        <div class="w-full md:w-48 bg-slate-950 rounded-full h-2.5 p-0.5 border border-slate-800">
                            <div class="bg-cyan-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ $critPercent }}%"></div>
                        </div>
                    </div>

                    @forelse($criteria->subKriterias as $sub)
                        <div class="bg-slate-950 p-4 rounded-lg mb-4 border border-slate-800">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b border-slate-800/60 gap-4">
                                <div>
                                    <p class="font-medium text-slate-200 text-sm">
                                        {{ $sub->code ?? $sub->kode ?? '' }} {{ $sub->title ?? $sub->nama ?? '' }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $sub->description ?? '' }}</p>
                                </div>
                                <div class="bg-slate-900 px-3 py-2 rounded border border-slate-800 text-xs flex items-center gap-2">
                                    <span class="text-slate-400">Nilai SK:</span>
                                    <span class="font-bold text-emerald-400 bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-800">
                                        {{ $sub->skor_level ?? 'Belum' }}
                                    </span>
                                </div>
                            </div>

                            <!-- 5 Level Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                @forelse($sub->maturityLevels as $lvl)
                                    <div class="bg-slate-900 p-3 rounded border border-slate-800 flex flex-col justify-between">
                                        <div>
                                            <span class="text-xs font-bold text-blue-400">Level {{ $lvl->level }}</span>
                                            <p class="text-xs text-slate-300 mt-1 mb-3">{{ $lvl->evidence_requirement }}</p>
                                        </div>

                                        <div>
                                            @if($lvl->evidenceUpload)
                                                @php $st = $lvl->evidenceUpload->status ?? 'pending'; @endphp

                                                @if($st == 'pending')
                                                    <div class="text-xs bg-amber-950/40 text-amber-300 p-2.5 rounded border border-amber-800/60 space-y-2">
                                                        <p class="font-semibold">⏳ Menunggu Penilaian</p>
                                                        <p class="text-[10px] text-slate-300 truncate">{{ $lvl->evidenceUpload->original_filename }}</p>
                                                        <div class="flex gap-1 pt-1">
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" target="_blank" class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-cyan-300 py-1 rounded text-[10px]">Preview</a>
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" download class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-emerald-300 py-1 rounded text-[10px]">Download</a>
                                                        </div>
                                                    </div>
                                                @elseif($st == 'approved')
                                                    <div class="text-xs bg-emerald-950/50 text-emerald-300 p-2.5 rounded border border-emerald-800 space-y-2">
                                                        <p class="font-semibold">✅ Disetujui</p>
                                                        <p class="text-[10px] text-slate-300 truncate">{{ $lvl->evidenceUpload->original_filename }}</p>
                                                        <div class="flex gap-1 pt-1">
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" target="_blank" class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-cyan-300 py-1 rounded text-[10px]">Preview</a>
                                                            <a href="{{ asset('storage/' . $lvl->evidenceUpload->file_path) }}" download class="flex-1 text-center bg-slate-800 hover:bg-slate-700 text-emerald-300 py-1 rounded text-[10px]">Download</a>
                                                        </div>
                                                    </div>
                                                @elseif($st == 'rejected')
                                                    <div class="text-xs bg-rose-950/50 text-rose-300 p-2 rounded border border-rose-800 space-y-1 mb-2">
                                                        <p class="font-semibold">❌ Ditolak</p>
                                                        <p class="text-[10px]">Catatan: {{ $lvl->evidenceUpload->rejection_note }}</p>
                                                    </div>
                                                    <form action="{{ route('matlev.upload', $lvl->id) }}" method="POST" enctype="multipart/form-data" class="space-y-1.5">
                                                        @csrf
                                                        <input type="file" name="pdf_file" accept="application/pdf" required class="block w-full text-[10px] text-slate-400 file:mr-1 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-800 file:text-white">
                                                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white text-[10px] font-semibold py-1 px-2 rounded">Upload Revisi</button>
                                                    </form>
                                                @endif
                                            @else
                                                <form action="{{ route('matlev.upload', $lvl->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                                    @csrf
                                                    <input type="file" name="pdf_file" accept="application/pdf" required class="block w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-800 file:text-white hover:file:bg-slate-700">
                                                    <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-semibold py-1.5 px-3 rounded transition">
                                                        Upload PDF
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400 col-span-5">Belum ada level.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada subkriteria.</p>
                    @endforelse
                </div>
            @empty
                <p class="text-slate-400 text-center py-8">Belum ada data kriteria.</p>
            @endforelse
        </div>


        <!-- ========================================== -->
        <!-- TAB 3: PERLU REVISI (DITOLAK) -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'revisions'" x-transition class="space-y-6">
            <div class="bg-slate-900 p-6 rounded-xl border border-slate-800">
                <h2 class="text-lg font-semibold text-rose-400 mb-1">Daftar Dokumen yang Memerlukan Revisi</h2>
                <p class="text-xs text-slate-400 mb-6">Berikut adalah dokumen yang ditolak oleh admin. Perbaiki file sesuai catatan lalu upload ulang pada form di bawah.</p>

                @forelse($rejectedItems as $item)
                    <div class="bg-slate-950 p-4 rounded-xl border border-rose-900/50 mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="bg-rose-950 text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded border border-rose-800">Level {{ $item['level'] }}</span>
                                <h3 class="text-sm font-semibold text-white">{{ $item['criteria'] }} &rsaquo; {{ $item['sub'] }}</h3>
                            </div>
                            <p class="text-xs text-slate-300"><strong>Persyaratan:</strong> {{ $item['requirement'] }}</p>
                            <div class="bg-rose-950/40 p-2.5 rounded border border-rose-900/60 text-xs text-rose-200 mt-2">
                                <strong>Catatan Penolakan Admin:</strong> {{ $item['upload']->rejection_note ?? 'Tidak ada catatan.' }}
                            </div>
                        </div>

                        <div class="w-full md:w-72 bg-slate-900 p-3 rounded-lg border border-slate-800 shrink-0">
                            <form action="{{ route('matlev.upload', $item['upload']->maturity_level_id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                @csrf
                                <p class="text-[10px] text-slate-400">Pilih file PDF revisi:</p>
                                <input type="file" name="pdf_file" accept="application/pdf" required class="block w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-800 file:text-white">
                                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold py-1.5 px-3 rounded transition">
                                    🔄 Kirim File Revisi
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="text-4xl mb-2">🎉</div>
                        <h3 class="text-sm font-semibold text-white">Kerja Bagus!</h3>
                        <p class="text-xs text-slate-400 mt-1">Tidak ada dokumen yang perlu direvisi saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>


        <!-- ========================================== -->
        <!-- TAB 4: RIWAYAT & LOG AKTIVITAS -->
        <!-- ========================================== -->
        <div x-show="currentTab === 'history'" x-transition class="space-y-6">
            <div class="bg-slate-900 p-6 rounded-xl border border-slate-800">
                <h2 class="text-lg font-semibold text-cyan-400 mb-1">Riwayat & Log Pengunggahan File</h2>
                <p class="text-xs text-slate-400 mb-6">Rekam jejak seluruh file yang pernah di-upload beserta status validasinya saat ini.</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="p-3">Waktu Upload</th>
                                <th class="p-3">Kriteria / Sub</th>
                                <th class="p-3">Level</th>
                                <th class="p-3">Nama File</th>
                                <th class="p-3">Status Terakhir</th>
                                <th class="p-3">Catatan Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($allHistories as $history)
                                <tr class="hover:bg-slate-950/40">
                                    <td class="p-3 text-slate-400 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($history['time'])->format('d M Y, H:i') }}
                                    </td>
                                    <td class="p-3 font-medium text-white">
                                        [{{ $history['criteria_code'] }} - {{ $history['sub_code'] }}]
                                    </td>
                                    <td class="p-3">
                                        <span class="bg-slate-800 text-cyan-400 px-2 py-0.5 rounded font-bold text-[10px]">Lvl {{ $history['level'] }}</span>
                                    </td>
                                    <td class="p-3 text-slate-300 truncate max-w-xs">
                                        {{ $history['filename'] }}
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        @if($history['status'] == 'pending')
                                            <span class="bg-amber-950/60 text-amber-400 px-2 py-1 rounded border border-amber-800/60 text-[10px] font-semibold">⏳ Menunggu</span>
                                        @elseif($history['status'] == 'approved')
                                            <span class="bg-emerald-950/60 text-emerald-400 px-2 py-1 rounded border border-emerald-800 text-[10px] font-semibold">✅ Diterima</span>
                                        @elseif($history['status'] == 'rejected')
                                            <span class="bg-rose-950/60 text-rose-400 px-2 py-1 rounded border border-rose-800 text-[10px] font-semibold">❌ Ditolak</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-400 italic">
                                        {{ $history['note'] ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-500">Belum ada riwayat aktivitas pengunggahan dokumen.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>