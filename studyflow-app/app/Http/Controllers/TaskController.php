<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->tasks()->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
            'difficulty' => ['nullable', 'string', 'max:50'],
            'external_subject_id' => ['nullable', 'string', 'max:255'],
            'subject_name' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'completed_at' => ['nullable', 'date'],
            'is_overdue' => ['nullable', 'boolean'],
        ]);

        $task = Auth::user()->tasks()->create($data);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);

        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);

        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'max:50'],
            'priority' => ['sometimes', 'string', 'max:50'],
            'difficulty' => ['sometimes', 'string', 'max:50'],
            'external_subject_id' => ['nullable', 'string', 'max:255'],
            'subject_name' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'estimated_minutes' => ['sometimes', 'integer', 'min:1'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'completed_at' => ['nullable', 'date'],
            'is_overdue' => ['nullable', 'boolean'],
        ]);

        $task->update($data);

        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);

        $task->delete();

        return response()->json(['deleted' => true]);
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? $item['external_id'] ?? null;
            $task = Auth::user()->tasks()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'external_id' => $externalId,
                    'title' => $item['title'] ?? 'Untitled task',
                    'description' => $item['description'] ?? null,
                    'status' => $item['status'] ?? 'Todo',
                    'priority' => $item['priority'] ?? 'Medium',
                    'difficulty' => $item['difficulty'] ?? 'Medium',
                    'external_subject_id' => $item['subjectId'] ?? $item['external_subject_id'] ?? null,
                    'subject_name' => $item['subjectName'] ?? $item['subject_name'] ?? null,
                    'deadline' => $item['deadline'] ?? null,
                    'estimated_minutes' => $item['estimatedMinutes'] ?? $item['estimated_minutes'] ?? 45,
                    'progress' => $item['progress'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                    'completed_at' => $item['completedAt'] ?? $item['completed_at'] ?? null,
                    'is_overdue' => (bool) ($item['isOverdue'] ?? false),
                ]
            );

            $saved[] = $task->fresh();
        }

        return response()->json(['items' => $saved]);
    }
}
