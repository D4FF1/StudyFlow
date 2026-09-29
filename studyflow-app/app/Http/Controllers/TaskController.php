<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->tasks()->with('subject');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', strtolower($request->input('priority')));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->filled('deadline')) {
            $deadlineFilter = $request->input('deadline');
            if ($deadlineFilter === 'today') {
                $query->whereDate('deadline', today());
            } elseif ($deadlineFilter === 'week') {
                $query->whereBetween('deadline', [now()->startOfDay(), now()->addDays(7)->endOfDay()]);
            } elseif ($deadlineFilter === 'overdue') {
                $query->where('deadline', '<', now())->where('status', '!=', 'completed');
            } elseif ($deadlineFilter === 'upcoming') {
                $query->where('deadline', '>=', now());
            }
        }

        $tasks = $query->latest()->get();

        if ($request->expectsJson()) {
            return response()->json($tasks);
        }

        $subjects = $user->subjects()->get();
        $priorityEngine = app(\App\Services\PriorityEngine::class);
        $tasks->each(function ($task) use ($priorityEngine) {
            $task->priorityMeta = $priorityEngine->calculate($task);
        });

        $totalTasksCount = $user->tasks()->count();

        return view('tasks.index', compact('tasks', 'subjects', 'totalTasksCount'));
    }

    public function create(): View
    {
        $subjects = auth()->user()->subjects()->get();

        return view('tasks.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'importance' => ['nullable', 'in:low,medium,high'],
            'difficulty' => ['nullable', 'in:easy,medium,hard'],
            'status' => ['nullable', 'in:todo,in_progress,completed'],
            'deadline' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:1'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['subject_id']) && ! auth()->user()->subjects()->whereKey($validated['subject_id'])->exists()) {
            abort(403, 'Invalid subject selection.');
        }

        $task = auth()->user()->tasks()->create([
            ...$validated,
            'subject_name' => $validated['subject_id'] ? optional(auth()->user()->subjects()->find($validated['subject_id']))->name : null,
            'priority' => $validated['priority'] ?? 'medium',
            'importance' => $validated['importance'] ?? 'medium',
            'difficulty' => $validated['difficulty'] ?? 'medium',
            'status' => $validated['status'] ?? 'todo',
            'estimated_minutes' => $validated['estimated_minutes'] ?? 45,
            'progress' => $validated['progress'] ?? 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json($task, 201);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Request $request, Task $task)
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $task->load('subject');
        $priorityMeta = app(\App\Services\PriorityEngine::class)->calculate($task);

        if ($request->expectsJson()) {
            return response()->json([
                'task' => $task,
                'priorityMeta' => $priorityMeta,
            ]);
        }

        return view('tasks.show', compact('task', 'priorityMeta'));
    }

    public function edit(Task $task): View
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $subjects = auth()->user()->subjects()->get();

        return view('tasks.edit', compact('task', 'subjects'));
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'importance' => ['sometimes', 'in:low,medium,high'],
            'difficulty' => ['sometimes', 'in:easy,medium,hard'],
            'status' => ['sometimes', 'in:todo,in_progress,completed'],
            'deadline' => ['nullable', 'date'],
            'estimated_minutes' => ['sometimes', 'integer', 'min:1'],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['subject_id']) && ! auth()->user()->subjects()->whereKey($validated['subject_id'])->exists()) {
            abort(403, 'Invalid subject selection.');
        }

        $task->update($validated);

        if ($request->expectsJson()) {
            return response()->json($task->fresh());
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Request $request, Task $task)
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $task->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? $item['external_id'] ?? null;
            $task = auth()->user()->tasks()->updateOrCreate(
                ['external_id' => $externalId],
                [
                    'external_id' => $externalId,
                    'subject_id' => $item['subjectId'] ?? $item['subject_id'] ?? null,
                    'subject_name' => $item['subjectName'] ?? $item['subject_name'] ?? null,
                    'title' => $item['title'] ?? 'Untitled task',
                    'description' => $item['description'] ?? null,
                    'status' => $this->normalizeTaskStatus($item['status'] ?? 'todo'),
                    'priority' => strtolower($item['priority'] ?? 'medium'),
                    'importance' => strtolower($item['importance'] ?? 'medium'),
                    'difficulty' => strtolower($item['difficulty'] ?? 'medium'),
                    'deadline' => $item['deadline'] ?? null,
                    'estimated_minutes' => $item['estimatedMinutes'] ?? $item['estimated_minutes'] ?? 45,
                    'progress' => (int) ($item['progress'] ?? 0),
                    'notes' => $item['notes'] ?? null,
                    'completed_at' => ($this->normalizeTaskStatus($item['status'] ?? 'todo') === 'completed') ? now() : null,
                ]
            );

            $saved[] = $task->fresh();
        }

        return response()->json(['items' => $saved]);
    }

    protected function normalizeTaskStatus(string $status): string
    {
        $status = trim($status);

        if ($status === '') {
            return 'todo';
        }

        $normalized = strtolower(str_replace(['-', '_'], ' ', $status));

        return match ($normalized) {
            'in progress' => 'In Progress',
            'completed', 'done' => 'completed',
            'todo' => 'todo',
            default => $status,
        };
    }

    public function complete(Task $task): RedirectResponse
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $task->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Task marked complete.');
    }

    public function reopen(Task $task): RedirectResponse
    {
        abort_unless($task->user_id === auth()->id(), 403);

        $task->update([
            'status' => 'todo',
            'completed_at' => null,
        ]);

        return back()->with('success', 'Task reopened.');
    }
}
