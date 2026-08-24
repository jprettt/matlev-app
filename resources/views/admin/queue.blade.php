@extends('layouts.admin')

@section('title', 'Antrean Verifikasi')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm">
        <h1 class="text-2xl font-extrabold font-display text-stone-900">Antrean Verifikasi</h1>
        <p class="text-sm text-stone-500 mt-1">Fokus pada berkas berstatus pending dengan filter unit kerja, tanggal upload, dan kriteria K3.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <section class="bg-white border border-stone-200 rounded-3xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.queue') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-stone-600 uppercase">Unit Kerja</label>
                <select name="unit_kerja" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm bg-white">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit }}" {{ request('unit_kerja') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                    @endforeach
                </select>
            </div>

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
                <a href="{{ route('admin.queue') }}" class="flex-1 rounded-xl bg-stone-200 hover:bg-stone-300 text-stone-700 text-sm font-bold py-2.5 text-center">Reset</a>
            </div>
        </form>
    </section>

    <section class="bg-white border border-stone-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-100 text-stone-600 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Upload</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left">Kriteria / Level</th>
                        <th class="px-4 py-3 text-left">Berkas</th>
                        <th class="px-4 py-3 text-left">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($uploads as $upload)
                        <tr class="align-top">
                            <td class="px-4 py-3 text-xs text-stone-500 whitespace-nowrap">{{ $upload->uploaded_at ? $upload->uploaded_at->timezone(config('app.timezone'))->format('d M Y H:i') . ' WITA' : '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-stone-800">{{ $upload->user->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-stone-600">
                                <div class="font-semibold text-stone-800">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                                <div>{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-pln-700 hover:text-pln-900 underline">{{ $upload->original_filename }}</a>
                            </td>
                            <td class="px-4 py-3 w-80">
                                <form action="{{ route('admin.verify', $upload->id) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <select name="status" class="w-full rounded-lg border border-stone-300 px-2.5 py-2 text-sm bg-white">
                                        <option value="pending" {{ $upload->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved">Setujui</option>
                                        <option value="rejected">Tolak</option>
                                    </select>
                                    <textarea name="rejection_note" rows="2" placeholder="Catatan alasan bila ditolak" class="w-full rounded-lg border border-stone-300 px-2.5 py-2 text-sm"></textarea>
                                    <button type="submit" class="w-full rounded-lg bg-pln-700 hover:bg-pln-800 text-white text-sm font-bold py-2">Simpan Evaluasi</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-stone-400">Tidak ada dokumen pending sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($uploads->hasPages())
            <div class="px-4 py-3 border-t border-stone-100">{{ $uploads->links() }}</div>
        @endif
    </section>
</div>
@endsection
