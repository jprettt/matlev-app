@extends('layouts.fore')

@section('title', 'Riwayat Notifikasi')

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-pln-600">Pusat Informasi</p>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-pln-950 font-display">Riwayat Notifikasi</h1>
        </div>
        <div class="flex gap-2 text-xs font-bold">
            <a href="{{ route('notifications.history', ['filter' => 'all']) }}" class="px-3 py-2 rounded-lg {{ $filter === 'all' ? 'bg-pln-900 text-white' : 'bg-slate-100 text-slate-600' }}">Semua</a>
            <a href="{{ route('notifications.history', ['filter' => 'unread']) }}" class="px-3 py-2 rounded-lg {{ $filter === 'unread' ? 'bg-pln-900 text-white' : 'bg-slate-100 text-slate-600' }}">Belum Dibaca</a>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
        @forelse($notifications as $notification)
            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left px-4 sm:px-5 py-4 border-b border-slate-100 hover:bg-pln-50 {{ $notification->is_read ? 'bg-white' : 'bg-pln-50/70' }}">
                    <div class="flex gap-3">
                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full shrink-0 {{ $notification->is_read ? 'bg-slate-300' : 'bg-pln-600' }}"></span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800">{{ $notification->title }}</p>
                            <p class="text-xs text-slate-600 mt-1">{{ $notification->message }}</p>
                            <p class="text-[11px] text-slate-400 mt-2">{{ $notification->created_at->translatedFormat('d F Y, H:i') }} · {{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </button>
            </form>
        @empty
            <p class="px-5 py-12 text-center text-sm text-slate-500">{{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca.' : 'Tidak ada notifikasi.' }}</p>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-5">{{ $notifications->links() }}</div>
    @endif
</section>
@endsection