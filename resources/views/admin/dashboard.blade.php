@extends('layouts.admin')

@section('title', 'Dashboard Verifikator')

@section('content')
<div class="space-y-6">
    <div class="py-2 text-stone-950">
        <p class="text-xs uppercase tracking-[0.2em] text-amber-950 font-bold">Pusat Verifikasi</p>
        <h1 class="text-2xl font-extrabold font-display mt-2">Dashboard Verifikasi Dokumen</h1>
        <p class="text-sm text-amber-950/80 mt-1">Tinjau berkas pending berdasarkan tanggal upload dan kriteria K3.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <section class="bg-white border border-stone-200 rounded-3xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
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
                <a href="{{ route('admin.dashboard') }}" class="flex-1 rounded-xl bg-stone-200 hover:bg-stone-300 text-stone-700 text-sm font-bold py-2.5 text-center">Reset</a>
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
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($recentPendingUploads as $upload)
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-stone-400">Tidak ada dokumen pending sesuai filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection