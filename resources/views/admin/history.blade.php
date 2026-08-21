@extends('layouts.admin')

@section('title', 'Riwayat Evaluasi')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-sm">
        <h1 class="text-2xl font-extrabold font-display text-stone-900">Riwayat Evaluasi</h1>
        <p class="text-sm text-stone-500 mt-1">Audit trail keputusan verifikator untuk dokumen approved dan rejected.</p>
    </div>

    <section class="bg-white border border-stone-200 rounded-3xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.history') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="text-xs font-bold text-stone-600 uppercase">Status</label>
                <select name="status" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm bg-white">
                    <option value="">Semua</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

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
                <label class="text-xs font-bold text-stone-600 uppercase">Dari Tanggal</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm bg-white">
            </div>

            <div>
                <label class="text-xs font-bold text-stone-600 uppercase">Sampai Tanggal</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm bg-white">
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

            <div class="md:col-span-5 flex gap-2 justify-end">
                <button type="submit" class="px-5 rounded-xl bg-pln-700 hover:bg-pln-800 text-white text-sm font-bold py-2.5">Terapkan</button>
                <a href="{{ route('admin.history') }}" class="px-5 rounded-xl bg-stone-200 hover:bg-stone-300 text-stone-700 text-sm font-bold py-2.5">Reset</a>
            </div>
        </form>
    </section>

    <section class="bg-white border border-stone-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-stone-100 text-stone-600 text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Diputuskan</th>
                        <th class="px-4 py-3 text-left">Pengunggah</th>
                        <th class="px-4 py-3 text-left">Kriteria / Level</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-left">Berkas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($uploads as $upload)
                        <tr>
                            <td class="px-4 py-3 text-xs text-stone-500 whitespace-nowrap">{{ $upload->updated_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-stone-800">{{ $upload->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-stone-600">
                                <div class="font-semibold text-stone-800">{{ $upload->maturityLevel->subkriteria->kriteria->title ?? '-' }}</div>
                                <div>{{ $upload->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $upload->maturityLevel->level ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                @if($upload->status === 'approved')
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Disetujui</span>
                                @else
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-stone-600 max-w-xs">{{ $upload->rejection_note ?: '-' }}</td>
                            <td class="px-4 py-3 text-xs"><a href="{{ asset('storage/' . $upload->file_path) }}" target="_blank" class="text-pln-700 hover:text-pln-900 underline">{{ $upload->original_filename }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-stone-400">Belum ada riwayat evaluasi sesuai filter.</td>
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
