<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Dokumen - Maturity Level K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8 pb-4 border-b border-gray-700">
            <div>
                <h1 class="text-2xl font-bold text-cyan-400">Pengajuan Dokumen Maturity Level</h1>
                <p class="text-sm text-gray-400">Unit Kerja: {{ Auth::user()->name ?? 'User' }}</p>
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

        <!-- List Kriteria & Subkriteria -->
        <div class="space-y-6">
            @forelse($kriterias as $kriteria)
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 shadow-lg">
                    <h2 class="text-lg font-bold text-cyan-300 mb-4 border-b border-gray-700/50 pb-2">
                        {{ $kriteria->nama_kriteria }}
                    </h2>

                    <div class="space-y-4">
                        @foreach($kriteria->subkriterias as $sub)
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-gray-900/60 rounded-lg border border-gray-700/50 gap-4">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-white text-sm">{{ $sub->nama_subkriteria }}</h3>
                                    <div class="flex items-center gap-3 mt-2 text-xs">
                                        <span class="text-gray-400">Status:</span>
                                        <span class="px-2 py-0.5 rounded-full font-semibold
                                            {{ $sub->status == 'Disetujui' ? 'bg-green-900/60 text-green-300 border border-green-500' : '' }}
                                            {{ $sub->status == 'Ditolak' ? 'bg-red-900/60 text-red-300 border border-red-500' : '' }}
                                            {{ $sub->status == 'Pending' ? 'bg-yellow-900/60 text-yellow-300 border border-yellow-500' : '' }}">
                                            {{ $sub->status ?? 'Belum Diunggah' }}
                                        </span>
                                        @if($sub->skor)
                                            <span class="text-cyan-400 font-bold">Skor: {{ $sub->skor }}</span>
                                        @endif
                                    </div>
                                    @if($sub->catatan)
                                        <p class="mt-2 text-xs text-yellow-400 bg-yellow-950/40 p-2 rounded border border-yellow-800/50">
                                            Catatan Evaluator: {{ $sub->catatan }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Form Upload -->
                                <form action="{{ route('user.upload.submit', $sub->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                    @csrf
                                    <input type="file" name="document" accept=".pdf" required class="block w-full text-xs text-gray-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-cyan-900/50 file:text-cyan-300 hover:file:bg-cyan-800/50 cursor-pointer">
                                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-3 py-1.5 rounded-md text-xs font-semibold shrink-0 transition">
                                        Upload PDF
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-gray-800 rounded-xl p-8 text-center text-gray-400 border border-gray-700">
                    Belum ada data kriteria dalam sistem.
                </div>
            @empty
            @endforelse
        </div>
    </div>
</body>
</html>