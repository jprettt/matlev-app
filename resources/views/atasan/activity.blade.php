@extends('layouts.atasan')

@section('title', 'Riwayat Aktivitas')

@section('content')
<div class="space-y-6">
    <header class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-ocean">Audit Sistem Penilaian</p><h1 class="mt-2 font-display text-3xl font-bold text-ink">Riwayat Aktivitas</h1><p class="mt-2 text-sm text-slate-600">Urutan aktivitas User dan Verifikator Admin berdasarkan waktu.</p></div>
    </header>

    <section class="mb-6 rounded-2xl border border-slate-200 border-t-4 border-t-ocean bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('atasan.activity') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:grid-cols-6 lg:items-end">
            <div class="lg:col-span-2"><label class="text-xs font-bold uppercase text-slate-600">Cari pengguna / dokumen</label><input name="search" value="{{ request('search') }}" placeholder="Nama, file, atau catatan" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-slate-600">Pengguna</label><select name="user_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Semua</option>@foreach($users as $user)<option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold uppercase text-slate-600">Peran</label><select name="role" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Semua</option><option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option><option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Verifikator Admin</option><option value="atasan" {{ request('role') === 'atasan' ? 'selected' : '' }}>Atasan</option></select></div>
            <div><label class="text-xs font-bold uppercase text-slate-600">Jenis Aktivitas</label><select name="activity_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Semua</option>@foreach($activityTypes as $type)<option value="{{ $type }}" {{ request('activity_type') === $type ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($type)) }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold uppercase text-slate-600">Dari</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-slate-600">Sampai</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></div>
            <div class="flex gap-2 md:col-span-3 lg:col-span-6 lg:justify-end"><button class="rounded-lg bg-blue-700 px-5 py-2 text-sm font-bold text-white">Terapkan</button><a href="{{ route('atasan.activity') }}" class="rounded-lg bg-slate-200 px-5 py-2 text-sm font-bold text-slate-700">Reset</a></div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-100 text-left text-[11px] uppercase tracking-wider text-slate-600"><tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Nama / Peran</th><th class="px-4 py-3">Jenis Aktivitas</th><th class="px-4 py-3">Dokumen / Data</th><th class="px-4 py-3">Perubahan Status</th><th class="px-4 py-3">Catatan</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($activityLogs as $log)
            <tr><td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">{{ $log->occurred_at?->timezone(config('app.timezone'))->format('d-m-Y H:i') }} WITA</td><td class="px-4 py-3"><div class="font-semibold">{{ $log->actor->name ?? 'Sistem' }}</div><div class="text-xs text-slate-500">{{ ucfirst($log->actor->role ?? '-') }}</div></td><td class="px-4 py-3 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($log->activity_type)) }}</td><td class="px-4 py-3 text-xs"><div class="font-semibold">{{ $log->filename ?: 'Aktivitas akun' }}</div><div class="text-slate-500">{{ $log->maturityLevel->subkriteria->title ?? '-' }}{{ $log->maturityLevel ? ' • Level ' . $log->maturityLevel->level : '' }}</div></td><td class="px-4 py-3 text-xs">{{ $log->status_before ? ucfirst($log->status_before) . ' → ' : '' }}{{ ucfirst($log->status ?? '-') }}</td><td class="max-w-xs px-4 py-3 text-xs text-slate-600">{{ $log->note ?: '-' }}</td></tr>
        @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">Belum ada aktivitas yang tercatat.</td></tr>
        @endforelse
    </tbody></table></div>@if($activityLogs->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $activityLogs->links() }}</div>@endif</section>
</div>
@endsection
