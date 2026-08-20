<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Matlev K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
<div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-cyan-400">Admin Master Data & Evaluator</p>
            <h1 class="text-3xl font-bold mt-2">Dashboard Verifikasi Dokumen</h1>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="bg-rose-600 hover:bg-rose-500 px-4 py-2 rounded-lg font-semibold">Logout</button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5">
            <p class="text-sm text-slate-400">Total Upload</p>
            <p class="text-3xl font-bold mt-2">{{ $uploads->count() }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5">
            <p class="text-sm text-slate-400">Pending</p>
            <p class="text-3xl font-bold mt-2 text-amber-400">{{ $uploads->where('status', 'pending')->count() }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5">
            <p class="text-sm text-slate-400">Approved</p>
            <p class="text-3xl font-bold mt-2 text-emerald-400">{{ $uploads->where('status', 'approved')->count() }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-5">
            <p class="text-sm text-slate-400">Rejected</p>
            <p class="text-3xl font-bold mt-2 text-rose-400">{{ $uploads->where('status', 'rejected')->count() }}</p>
        </div>
    </div>

    <div class="space-y-8">
        <section class="bg-slate-900 rounded-2xl p-5 border border-slate-700">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-semibold">Filter Antrean Evaluasi</h2>
                <div class="flex gap-2">
                    @php $statusFilter = request('status', 'all'); @endphp
                    <a href="{{ route('admin.dashboard', ['status' => 'all']) }}" class="px-3 py-1.5 rounded {{ $statusFilter === 'all' ? 'bg-cyan-600' : 'bg-slate-800' }} text-sm">Semua</a>
                    <a href="{{ route('admin.dashboard', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded {{ $statusFilter === 'pending' ? 'bg-cyan-600' : 'bg-slate-800' }} text-sm">Pending</a>
                    <a href="{{ route('admin.dashboard', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded {{ $statusFilter === 'approved' ? 'bg-cyan-600' : 'bg-slate-800' }} text-sm">Approved</a>
                    <a href="{{ route('admin.dashboard', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded {{ $statusFilter === 'rejected' ? 'bg-cyan-600' : 'bg-slate-800' }} text-sm">Rejected</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-800 text-slate-300 uppercase text-[11px] tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Kriteria / Sub</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Berkas</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-left">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($uploads as $upload)
                        <tr class="border-t border-slate-700 align-top">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-white">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                                <div class="text-cyan-400 text-xs">{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $upload->user->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $upload->user->unit_kerja ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-cyan-400 underline">{{ $upload->original_filename }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($upload->status === 'approved') bg-emerald-900 text-emerald-300
                                    @elseif($upload->status === 'rejected') bg-rose-900 text-rose-300
                                    @else bg-amber-900 text-amber-300 @endif">
                                    {{ ucfirst($upload->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-300 max-w-xs">
                                {{ $upload->rejection_note ?: 'Belum ada catatan.' }}
                            </td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.verify', $upload->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <select name="status" class="bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm text-white">
                                        <option value="pending" {{ $upload->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $upload->status === 'approved' ? 'selected' : '' }}>Approve</option>
                                        <option value="rejected" {{ $upload->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                    <textarea name="rejection_note" rows="2" placeholder="Catatan alasan bila ditolak" class="w-full bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm text-white"></textarea>
                                    <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 px-3 py-2 rounded font-semibold text-sm">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada data unggahan.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-slate-900 rounded-2xl p-5 border border-slate-700">
            <h2 class="text-xl font-semibold mb-5">Manajemen Data Master Kriteria</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-3 text-cyan-400">Tambah Kriteria</h3>
                    <form action="{{ route('admin.criteria.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="text" name="code" placeholder="Kode (contoh: 1)" class="w-full bg-slate-800 border border-slate-600 rounded px-3 py-2" required>
                        <input type="text" name="title" placeholder="Judul kriteria" class="w-full bg-slate-800 border border-slate-600 rounded px-3 py-2" required>
                        <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 px-4 py-2 rounded font-semibold">Simpan Kriteria</button>
                    </form>
                </div>
                <div>
                    <h3 class="font-semibold mb-3 text-cyan-400">Daftar Kriteria</h3>
                    <div class="space-y-3">
                        @foreach($criteria as $item)
                            <div class="bg-slate-800 rounded-xl p-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <div class="font-semibold">{{ $item->code }} - {{ $item->title }}</div>
                                    </div>
                                    <form action="{{ route('admin.criteria.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus kriteria ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-400 text-sm">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>