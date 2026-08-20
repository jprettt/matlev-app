<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Atasan - Matlev K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">
<div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Atasan / Auditor / Manajemen</p>
            <h1 class="text-3xl font-bold mt-2">Executive Dashboard K3</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('atasan.export.summary') }}" class="bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded-lg text-white font-semibold">Export PDF/Excel</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-900 hover:bg-slate-700 px-4 py-2 rounded-lg text-white font-semibold">Logout</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Dokumen</p>
            <p class="text-3xl font-bold mt-2">{{ $total }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Approved</p>
            <p class="text-3xl font-bold mt-2 text-emerald-600">{{ $approved }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Pending</p>
            <p class="text-3xl font-bold mt-2 text-amber-600">{{ $pending }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Rejected</p>
            <p class="text-3xl font-bold mt-2 text-rose-600">{{ $rejected }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-xl font-bold mb-4">Persentase Kepatuhan K3 per Kategori</h2>
            <div class="space-y-4">
                @foreach($chart as $item)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['percent'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $item['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <h2 class="text-xl font-bold mb-4">Dokumen Valid yang Siap Audit</h2>
            <div class="space-y-3">
                @foreach($uploads->where('status', 'approved')->take(6) as $upload)
                    <div class="flex justify-between items-start border-b border-slate-200 pb-2">
                        <div>
                            <div class="font-semibold">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}</div>
                        </div>
                        <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-blue-700 underline text-sm">Preview</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <section class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold">Rekapitulasi Status Dokumen</h2>
            <a href="{{ route('atasan.evidence') }}" class="text-blue-700 font-semibold">Lihat Eviden Valid</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-slate-600 uppercase text-[11px]">
                <tr>
                    <th class="px-4 py-3 text-left">Kriteria</th>
                    <th class="px-4 py-3 text-left">Sub Kriteria</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                </tr>
                </thead>
                <tbody>
                @foreach($uploads as $upload)
                    <tr class="border-t border-slate-200">
                        <td class="px-4 py-3">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $upload->user->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($upload->status === 'approved') bg-emerald-100 text-emerald-700
                                @elseif($upload->status === 'rejected') bg-rose-100 text-rose-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ ucfirst($upload->status ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $upload->uploaded_at?->format('d-m-Y H:i') ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
</body>
</html>
