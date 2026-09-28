<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->goals()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'target_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $goal = Auth::user()->goals()->create($data);

        return response()->json($goal, 201);
    }

    public function show(Goal $goal)
    {
        abort_unless($goal->user_id === Auth::id(), 403);

        return response()->json($goal);
    }

    public function update(Request $request, Goal $goal)
    {
        abort_unless($goal->user_id === Auth::id(), 403);

        $goal->update($request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'target_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]));

        return response()->json($goal);
    }

    public function destroy(Goal $goal)
    {
        abort_unless($goal->user_id === Auth::id(), 403);

        $goal->delete();

        return response()->json(['deleted' => true]);
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? $item['external_id'] ?? null;
            $goal = Auth::user()->goals()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'external_id' => $externalId,
                    'title' => $item['title'] ?? 'New goal',
                    'description' => $item['description'] ?? null,
                    'progress' => $item['progress'] ?? 0,
                    'target_date' => $item['targetDate'] ?? $item['target_date'] ?? null,
                    'status' => $item['status'] ?? 'On Track',
                ]
            );

            $saved[] = $goal->fresh();
        }

        return response()->json(['items' => $saved]);
    }
}
