<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $subjects = auth()->user()->subjects()->with(['tasks', 'studySessions'])->get();

        $subjects->each(function ($subject) {
            $total = $subject->tasks->count();
            $completed = $subject->tasks->where('status', 'completed')->count();
            $subject->total_tasks_count = $total;
            $subject->completed_tasks_count = $completed;
            $subject->completion_rate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
            $subject->total_study_minutes = (int) ($subject->studySessions->whereNotNull('ended_at')->sum('duration_minutes') ?: ($subject->study_minutes ?? 0));
        });

        if ($request->expectsJson()) {
            return response()->json($subjects);
        }

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('subjects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $subject = auth()->user()->subjects()->create($validated);

        if ($request->expectsJson()) {
            return response()->json($subject, 201);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function show(Request $request, Subject $subject)
    {
        abort_unless($subject->user_id === auth()->id(), 403);

        $subject->load(['tasks', 'studySessions']);
        $totalTasks = $subject->tasks()->count();
        $completedTasksCount = $subject->tasks()->where('status', 'completed')->count();
        $progress = $totalTasks ? (int) round(($completedTasksCount / $totalTasks) * 100) : 0;

        $upcomingTasks = $subject->tasks()->where('status', '!=', 'completed')->orderBy('deadline')->get();
        $completedTasks = $subject->tasks()->where('status', 'completed')->latest('completed_at')->take(10)->get();
        $recentSessions = $subject->studySessions()->latest('started_at')->take(5)->get();
        $totalStudyMinutes = (int) ($subject->studySessions()->whereNotNull('ended_at')->sum('duration_minutes') ?: ($subject->study_minutes ?? 0));

        $stats = [
            'total' => $totalTasks,
            'completed' => $completedTasksCount,
            'in_progress' => $subject->tasks()->where('status', 'in_progress')->count(),
            'todo' => $subject->tasks()->where('status', 'todo')->count(),
            'progress' => $progress,
            'study_minutes' => $totalStudyMinutes,
        ];

        if ($request->expectsJson()) {
            return response()->json(['subject' => $subject, 'progress' => $progress, 'stats' => $stats]);
        }

        return view('subjects.show', compact('subject', 'progress', 'upcomingTasks', 'completedTasks', 'recentSessions', 'stats'));
    }

    public function edit(Subject $subject): View
    {
        abort_unless($subject->user_id === auth()->id(), 403);

        return view('subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        abort_unless($subject->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $subject->update($validated);

        if ($request->expectsJson()) {
            return response()->json($subject->fresh());
        }

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Request $request, Subject $subject)
    {
        abort_unless($subject->user_id === auth()->id(), 403);

        $subject->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $subject = auth()->user()->subjects()->updateOrCreate(
                ['external_id' => $item['id'] ?? $item['external_id'] ?? null],
                [
                    'external_id' => $item['id'] ?? $item['external_id'] ?? null,
                    'name' => $item['name'] ?? 'Untitled subject',
                    'description' => $item['description'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'color' => $item['color'] ?? null,
                ]
            );

            $saved[] = $subject->fresh();
        }

        return response()->json(['items' => $saved]);
    }
}
