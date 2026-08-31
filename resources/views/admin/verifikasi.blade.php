@extends('layouts.admin')

@section('title', 'Antrean Verifikasi')

@section('content')
<div class="space-y-6">
    <div class="py-2 text-stone-950">
        <p class="text-xs uppercase tracking-[0.2em] text-amber-950 font-bold">Dokumen Pending</p>
        <h1 class="text-2xl font-extrabold font-display mt-2">Antrean Verifikasi</h1>
        <p class="text-sm text-amber-950/80 mt-1">Daftar lengkap dokumen yang menunggu verifikasi dari semua unit kerja.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filter Section -->
    <section class="bg-white border border-stone-200 rounded-2xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.verifikasi') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-stone-600 uppercase">Tanggal Unggah</label>
                <input type="date" name="upload_date" value="{{ request('upload_date') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm bg-white">
            </div>

            <div>
                <label class="text-xs font-bold text-stone-600 uppercase">Kriteria K3</label>
                <select name="criteria_id" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm bg-white">
                    <option value="">Semua Kriteria</option>
                    @foreach($criteriaOptions as $criteria)
                        <option value="{{ $criteria->id }}" {{ (string) request('criteria_id') === (string) $criteria->id ? 'selected' : '' }}>
                            {{ $criteria->code }} - {{ $criteria->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-pln-700 hover:bg-pln-800 text-white text-sm font-bold py-2.5">Terapkan</button>
                <a href="{{ route('admin.verifikasi') }}" class="flex-1 rounded-xl bg-stone-200 hover:bg-stone-300 text-stone-700 text-sm font-bold py-2.5 text-center">Reset</a>
            </div>
        </form>
    </section>

    <!-- Documents Table -->
    <section class="bg-white border border-stone-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-100 text-stone-600 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Upload</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Kriteria / Level</th>
                        <th class="px-4 py-3 text-left">Berkas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($pendingUploads as $upload)
                        <tr class="align-top hover:bg-stone-50">
                            <td class="px-4 py-3 text-xs text-stone-500 whitespace-nowrap">{{ $upload->uploaded_at ? $upload->uploaded_at->timezone(config('app.timezone'))->format('d M Y H:i') . ' WITA' : '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-stone-800">{{ $upload->user->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-stone-600">
                                <div class="font-semibold text-stone-800">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                                <div>{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                                @if($upload->evidenceRequirement)
                                    <div class="mt-2 text-[11px] font-bold uppercase tracking-wide text-amber-800">Bukti: {{ $upload->evidenceRequirement->name ?? '-' }}</div>
                                    @if($upload->evidenceRequirement->description)
                                        <div class="mt-1 max-w-md text-[11px] leading-5 text-stone-600 whitespace-pre-line">{{ Str::limit(strip_tags($upload->evidenceRequirement->description), 180) }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-pln-700 hover:text-pln-900 underline font-semibold">{{ $upload->original_filename }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-stone-400">Tidak ada dokumen pending sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pendingUploads->hasPages())
        <div class="px-4 py-4 border-t border-stone-200">
            <div class="flex items-center justify-between">
                <div class="text-xs text-stone-600">
                    Menampilkan <span class="font-semibold">{{ $pendingUploads->from() }}</span> hingga <span class="font-semibold">{{ $pendingUploads->to() }}</span> dari <span class="font-semibold">{{ $pendingUploads->total() }}</span> dokumen
                </div>
                <div class="flex gap-2">
                    @if($pendingUploads->onFirstPage())
                        <button disabled class="px-3 py-1 rounded border border-stone-300 text-stone-300 text-xs font-semibold">← Sebelumnya</button>
                    @else
                        <a href="{{ $pendingUploads->previousPageUrl() }}" class="px-3 py-1 rounded border border-stone-300 text-stone-600 hover:bg-stone-50 text-xs font-semibold">← Sebelumnya</a>
                    @endif

                    @if($pendingUploads->hasMorePages())
                        <a href="{{ $pendingUploads->nextPageUrl() }}" class="px-3 py-1 rounded bg-pln-700 text-white text-xs font-semibold hover:bg-pln-800">Berikutnya →</a>
                    @else
                        <button disabled class="px-3 py-1 rounded border border-stone-300 text-stone-300 text-xs font-semibold">Berikutnya →</button>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </section>
</div>
@endsection
