<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Maturity Level K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
            <div>
                <h1 class="text-2xl font-bold text-cyan-400">Dashboard Evaluator / Admin</h1>
                <p class="text-sm text-gray-400">Evaluasi Dokumen Maturity Level K3 Unit Kerja</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
                    Logout
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-900/50 border border-green-500 text-green-200 p-4 rounded-lg mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Daftar Dokumen untuk Dievaluasi -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-xl">
            <div class="p-4 bg-gray-800/80 border-b border-gray-700">
                <h2 class="font-bold text-lg text-gray-200">Daftar Pengajuan Dokumen</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-gray-900/50 text-gray-400 uppercase text-xs border-b border-gray-700">
                        <tr>
                            <th class="p-4">Kriteria / Subkriteria</th>
                            <th class="p-4">Unit Kerja / User</th>
                            <th class="p-4">Dokumen</th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Skor</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($subkriterias as $item)
                            <tr class="hover:bg-gray-700/30">
                                <td class="p-4">
                                    <div class="font-semibold text-white">{{ $item->nama_subkriteria }}</div>
                                    <div class="text-xs text-cyan-400">{{ $item->kriteria->nama_kriteria ?? '-' }}</div>
                                </td>
                                <td class="p-4">{{ $item->user->name ?? 'User' }}</td>
                                <td class="p-4">
                                    @if($item->file_path)
                                        <a href="{{ asset('storage/' . $item->file_path) }}" target="_blank" class="text-cyan-400 hover:underline flex items-center gap-1">
                                            📄 Lihat File
                                        </a>
                                    @else
                                        <span class="text-gray-500 italic">Belum diunggah</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $item->status == 'Disetujui' ? 'bg-green-900/60 text-green-300 border border-green-500' : '' }}
                                        {{ $item->status == 'Ditolak' ? 'bg-red-900/60 text-red-300 border border-red-500' : '' }}
                                        {{ $item->status == 'Pending' ? 'bg-yellow-900/60 text-yellow-300 border border-yellow-500' : '' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-white">{{ $item->skor ?? 0 }}</td>
                                <td class="p-4">
                                    <form action="{{ route('admin.evaluate', $item->id) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="number" name="skor" value="{{ $item->skor }}" placeholder="Nilai" class="w-16 px-2 py-1 bg-gray-900 border border-gray-700 rounded text-sm text-center">
                                        <select name="status" class="bg-gray-900 border border-gray-700 rounded px-2 py-1 text-sm">
                                            <option value="Pending" {{ $item->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Disetujui" {{ $item->status == 'Disetujui' ? 'selected' : '' }}>Setujui</option>
                                            <option value="Ditolak" {{ $item->status == 'Ditolak' ? 'selected' : '' }}>Tolak</option>
                                        </select>
                                        <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 px-3 py-1 rounded text-xs font-semibold">Simpan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500">Belum ada pengajuan dokumen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>