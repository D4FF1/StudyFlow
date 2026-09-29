<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()->latest()->get();
        $unreadNotifications = $notifications->whereNull('read_at')->values();
        $readNotifications = $notifications->whereNotNull('read_at')->values();

        if ($request->expectsJson()) {
            return response()->json($notifications);
        }

        return view('notifications.index', compact('notifications', 'unreadNotifications', 'readNotifications'));
    }

    public function show(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if ($request->expectsJson()) {
            return response()->json($notification);
        }

        return response()->view('notifications.show', ['notification' => $notification]);
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $notification = auth()->user()->notifications()->updateOrCreate(
                ['external_id' => $item['id'] ?? $item['external_id'] ?? null],
                [
                    'external_id' => $item['id'] ?? $item['external_id'] ?? null,
                    'type' => $item['type'] ?? 'info',
                    'message' => $item['message'] ?? 'Notification',
                    'read_at' => $item['readAt'] ?? $item['read_at'] ?? null,
                ]
            );

            $saved[] = $notification->fresh();
        }

        return response()->json(['items' => $saved]);
    }

    public function markRead(Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
