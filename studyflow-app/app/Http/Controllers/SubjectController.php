<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->subjects()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'study_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $subject = Auth::user()->subjects()->create($data);

        return response()->json($subject, 201);
    }

    public function show(Subject $subject)
    {
        abort_unless($subject->user_id === Auth::id(), 403);

        return response()->json($subject);
    }

    public function update(Request $request, Subject $subject)
    {
        abort_unless($subject->user_id === Auth::id(), 403);

        $subject->update($request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'study_minutes' => ['nullable', 'integer', 'min:0'],
        ]));

        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        abort_unless($subject->user_id === Auth::id(), 403);

        $subject->delete();

        return response()->json(['deleted' => true]);
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? $item['external_id'] ?? null;
            $subject = Auth::user()->subjects()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'external_id' => $externalId,
                    'name' => $item['name'] ?? 'New subject',
                    'color' => $item['color'] ?? '#4f8cff',
                    'description' => $item['description'] ?? null,
                    'study_minutes' => $item['studyMinutes'] ?? $item['study_minutes'] ?? 0,
                ]
            );

            $saved[] = $subject->fresh();
        }

        return response()->json(['items' => $saved]);
    }
}
