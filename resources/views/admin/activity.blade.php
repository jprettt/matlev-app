@extends('layouts.admin')

@section('title', 'Riwayat Aktivitas')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs uppercase tracking-[0.2em] text-amber-950 font-bold">Audit Trail Sistem</p>
        <h1 class="mt-2 text-2xl font-extrabold font-display text-stone-950">Riwayat Aktivitas</h1>
        <p class="mt-1 text-sm text-stone-600">Catatan aktivitas upload, upload revisi, dan evaluasi dokumen.</p>
    </div>
    <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.activity') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4 items-end">
            <div><label class="text-xs font-bold uppercase text-stone-600">Aktivitas</label><select name="activity_type" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"><option value="">Semua Aktivitas</option>@foreach($activityTypes as $type)<option value="{{ $type }}" {{ request('activity_type') === $type ? 'selected' : '' }}>{{ $type === 'revision_upload' ? 'Upload Revisi' : ($type === 'evaluation' ? 'Evaluasi' : 'Upload') }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold uppercase text-stone-600">Status</label><select name="status" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"><option value="">Semua Status</option>@foreach(['pending', 'rejected', 'approved'] as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
            <div><label class="text-xs font-bold uppercase text-stone-600">Dari Tanggal</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"></div>
            <div><label class="text-xs font-bold uppercase text-stone-600">Sampai Tanggal</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="mt-1 w-full rounded-xl border border-stone-300 px-3 py-2 text-sm"></div>
            <div class="md:col-span-4 flex justify-end gap-2"><button class="rounded-xl bg-pln-700 px-5 py-2.5 text-sm font-bold text-white">Terapkan</button><a href="{{ route('admin.activity') }}" class="rounded-xl bg-stone-200 px-5 py-2.5 text-sm font-bold text-stone-700">Reset</a></div>
        </form>
    </section>
    <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-stone-100 text-[11px] uppercase tracking-wider text-stone-600"><tr><th class="px-4 py-3 text-left">Waktu</th><th class="px-4 py-3 text-left">Aktor</th><th class="px-4 py-3 text-left">Dokumen / Level</th><th class="px-4 py-3 text-left">Aktivitas</th><th class="px-4 py-3 text-left">Perubahan</th><th class="px-4 py-3 text-left">Catatan</th></tr></thead><tbody class="divide-y divide-stone-100">
            @forelse($activityLogs as $log)
                <tr><td class="whitespace-nowrap px-4 py-3 text-xs text-stone-500">{{ $log->occurred_at?->timezone(config('app.timezone'))->format('d M Y H:i') }} WITA</td><td class="px-4 py-3 font-semibold">{{ $log->actor->name ?? 'Sistem' }}<div class="text-[11px] font-normal text-stone-400">{{ ucfirst($log->actor->role ?? '-') }}</div></td><td class="px-4 py-3 text-xs"><div class="font-semibold">{{ $log->filename ?? '-' }}</div><div class="text-stone-500">{{ $log->maturityLevel->subkriteria->title ?? '-' }} • Level {{ $log->maturityLevel->level ?? '-' }}</div></td><td class="px-4 py-3 text-xs font-semibold">{{ str_replace('_', ' ', ucfirst($log->activity_type)) }}</td><td class="px-4 py-3 text-xs">{{ $log->status_before ? ucfirst($log->status_before) . ' → ' : '' }}{{ ucfirst($log->status ?? '-') }}</td><td class="max-w-xs px-4 py-3 text-xs text-stone-600">{{ $log->note ?: '-' }}</td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-stone-400">Belum ada aktivitas yang tercatat.</td></tr>
            @endforelse
        </tbody></table></div>
        @if($activityLogs->hasPages())<div class="border-t border-stone-100 px-4 py-3">{{ $activityLogs->links() }}</div>@endif
    </section>
</div>
@endsection
