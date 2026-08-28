@extends('layouts.admin')

@section('title', 'Riwayat Notifikasi')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs uppercase tracking-[0.2em] text-amber-950 font-bold">Pusat Informasi</p>
        <h1 class="mt-2 text-2xl font-extrabold font-display text-stone-950">Riwayat Notifikasi</h1>
    </div>
    <div class="flex gap-2 text-sm">
        <a href="{{ route('notifications.history', ['filter' => 'all']) }}" class="rounded-xl px-4 py-2 font-bold {{ $filter === 'all' ? 'bg-pln-700 text-white' : 'bg-stone-100 text-stone-600' }}">Semua</a>
        <a href="{{ route('notifications.history', ['filter' => 'unread']) }}" class="rounded-xl px-4 py-2 font-bold {{ $filter === 'unread' ? 'bg-pln-700 text-white' : 'bg-stone-100 text-stone-600' }}">Belum Dibaca</a>
    </div>
    <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        @forelse($notifications as $notification)
            <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="border-b border-stone-100 last:border-0">@csrf
                <button type="submit" class="w-full px-5 py-4 text-left {{ $notification->is_read ? '' : 'bg-amber-50' }}">
                    <div class="flex items-start justify-between gap-4"><strong class="text-sm text-stone-900">{{ $notification->title }}</strong><span class="shrink-0 text-[11px] text-stone-400">{{ $notification->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</span></div>
                    <p class="mt-1 text-sm text-stone-600">{{ $notification->message }}</p>
                </button>
            </form>
        @empty
            <p class="px-5 py-12 text-center text-sm text-stone-400">Belum ada notifikasi.</p>
        @endforelse
        @if($notifications->hasPages())<div class="border-t border-stone-100 px-5 py-3">{{ $notifications->links() }}</div>@endif
    </section>
</div>
@endsection
