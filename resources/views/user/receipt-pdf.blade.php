<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Tanda Terima</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-900 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-start mb-8 border-b pb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-blue-700">Bukti Tanda Terima</p>
                <h1 class="text-3xl font-bold mt-2">Rekapitulasi Checklist Dokumen</h1>
            </div>
            <button onclick="window.print()" class="bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold">Cetak ke PDF</button>
        </div>

        <table class="w-full border-collapse text-sm">
            <thead>
            <tr class="bg-slate-100">
                <th class="border px-3 py-2 text-left">No</th>
                <th class="border px-3 py-2 text-left">Kriteria</th>
                <th class="border px-3 py-2 text-left">Sub Kriteria</th>
                <th class="border px-3 py-2 text-left">Level</th>
                <th class="border px-3 py-2 text-left">Status</th>
                <th class="border px-3 py-2 text-left">Tanggal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($uploads as $index => $upload)
                <tr>
                    <td class="border px-3 py-2">{{ $index + 1 }}</td>
                    <td class="border px-3 py-2">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ $upload->maturityLevel->subkriteria->title ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ $upload->maturityLevel->level ?? '-' }}</td>
                    <td class="border px-3 py-2">{{ ucfirst($upload->status ?? 'pending') }}</td>
                    <td class="border px-3 py-2">{{ $upload->uploaded_at?->format('d-m-Y H:i') ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
