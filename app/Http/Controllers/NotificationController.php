<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function history(Request $request)
    {
        $filter = $request->string('filter')->value() === 'unread' ? 'unread' : 'all';
        $notifications = Auth::user()->appNotifications()
            ->when($filter === 'unread', fn ($query) => $query->where('is_read', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('user.notifications', compact('notifications', 'filter'));
    }

    public function read(Request $request, int $notification)
    {
        $item = Auth::user()->appNotifications()->findOrFail($notification);
        $item->update(['is_read' => true]);

        return redirect($item->target_url ?: route('user.dashboard'));
    }

    public function markAllRead()
    {
        Auth::user()->appNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back();
    }
}