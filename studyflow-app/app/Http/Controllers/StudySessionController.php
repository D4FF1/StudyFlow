<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudySessionController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->studySessions()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'string', 'max:255'],
            'subject_name' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'string', 'max:50'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'day' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $session = Auth::user()->studySessions()->create($data);

        return response()->json($session, 201);
    }

    public function show(StudySession $studySession)
    {
        abort_unless($studySession->user_id === Auth::id(), 403);

        return response()->json($studySession);
    }

    public function update(Request $request, StudySession $studySession)
    {
        abort_unless($studySession->user_id === Auth::id(), 403);

        $studySession->update($request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'string', 'max:255'],
            'subject_name' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'string', 'max:50'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'day' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ]));

        return response()->json($studySession);
    }

    public function destroy(StudySession $studySession)
    {
        abort_unless($studySession->user_id === Auth::id(), 403);

        $studySession->delete();

        return response()->json(['deleted' => true]);
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? $item['external_id'] ?? null;
            $session = Auth::user()->studySessions()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'external_id' => $externalId,
                    'subject_id' => $item['subjectId'] ?? $item['subject_id'] ?? null,
                    'subject_name' => $item['subjectName'] ?? $item['subject_name'] ?? null,
                    'topic' => $item['topic'] ?? null,
                    'start_time' => $item['startTime'] ?? $item['start_time'] ?? null,
                    'duration' => $item['duration'] ?? 0,
                    'day' => $item['day'] ?? null,
                    'color' => $item['color'] ?? '#4f8cff',
                ]
            );

            $saved[] = $session->fresh();
        }

        return response()->json(['items' => $saved]);
    }
}
