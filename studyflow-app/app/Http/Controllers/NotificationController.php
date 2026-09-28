<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->notifications()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string'],
            'read_at' => ['nullable', 'date'],
        ]);

        $notification = Auth::user()->notifications()->create($data);

        return response()->json($notification, 201);
    }

    public function show(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        return response()->json($notification);
    }

    public function update(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->update($request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'message' => ['sometimes', 'string'],
            'read_at' => ['nullable', 'date'],
        ]));

        return response()->json($notification);
    }

    public function destroy(Notification $notification)
    {
        abort_unless($notification->user_id === Auth::id(), 403);

        $notification->delete();

        return response()->json(['deleted' => true]);
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? $item['external_id'] ?? null;
            $notification = Auth::user()->notifications()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'external_id' => $externalId,
                    'type' => $item['type'] ?? 'info',
                    'message' => $item['message'] ?? 'New notification',
                    'read_at' => $item['readAt'] ?? $item['read_at'] ?? null,
                ]
            );

            $saved[] = $notification->fresh();
        }

        return response()->json(['items' => $saved]);
    }
}
