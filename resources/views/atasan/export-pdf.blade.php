<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-900 p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex justify-between items-start mb-8 border-b pb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Laporan Eksekutif</p>
                <h1 class="text-3xl font-bold mt-2">Rekapitulasi Nilai dan Pencapaian K3</h1>
            </div>
            <button onclick="window.print()" class="bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Cetak ke PDF</button>
        </div>

        <table class="w-full border-collapse text-sm">
            <thead>
            <tr class="bg-slate-100">
                <th class="border px-3 py-2 text-left">Nama User</th>
                <th class="border px-3 py-2 text-left">Unit</th>
                <th class="border px-3 py-2 text-left">Kriteria</th>
                <th class="border px-3 py-2 text-left">Sub Kriteria</th>
                <th class="border px-3 py-2 text-left">Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($uploads as $upload)
                <tr>
                    <td class="border px-3 py-2">{{ $upload->user->name ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ $upload->user->unit_kerja ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ ucfirst($upload->status ?? 'pending') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
