<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\GoalMilestone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = auth()->user()->goals()->with('milestones')->get();

        if ($request->expectsJson()) {
            return response()->json($goals);
        }

        return view('goals.index', compact('goals'));
    }

    public function create(): View
    {
        return view('goals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'target' => ['nullable', 'integer', 'min:1'],
            'current_progress' => ['nullable', 'integer', 'min:0'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $goal = auth()->user()->goals()->create([
            ...$validated,
            'target' => $validated['target'] ?? 100,
            'current_progress' => $validated['current_progress'] ?? 0,
            'progress' => $validated['current_progress'] ?? 0,
            'category' => $validated['category'] ?? 'study',
            'status' => $validated['status'] ?? 'active',
        ]);

        if ($request->expectsJson()) {
            return response()->json($goal, 201);
        }

        return redirect()->route('goals.index')->with('success', 'Goal created successfully.');
    }

    public function show(Request $request, Goal $goal)
    {
        abort_unless($goal->user_id === auth()->id(), 403);

        if ($request->expectsJson()) {
            return response()->json($goal->load('milestones'));
        }

        return view('goals.show', ['goal' => $goal->load('milestones')]);
    }

    public function edit(Goal $goal): View
    {
        abort_unless($goal->user_id === auth()->id(), 403);

        return view('goals.edit', compact('goal'));
    }

    public function update(Request $request, Goal $goal)
    {
        abort_unless($goal->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'target' => ['sometimes', 'integer', 'min:1'],
            'current_progress' => ['sometimes', 'integer', 'min:0'],
            'category' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'string', 'max:50'],
        ]);

        $goal->update([
            ...$validated,
            'progress' => $validated['current_progress'] ?? $goal->current_progress,
        ]);

        if ($request->expectsJson()) {
            return response()->json($goal->fresh());
        }

        return redirect()->route('goals.index')->with('success', 'Goal updated successfully.');
    }

    public function destroy(Request $request, Goal $goal)
    {
        abort_unless($goal->user_id === auth()->id(), 403);

        $goal->delete();

        if ($request->expectsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('goals.index')->with('success', 'Goal deleted successfully.');
    }

    public function sync(Request $request)
    {
        $items = $request->input('items', []);
        $saved = [];

        foreach ($items as $item) {
            $goal = auth()->user()->goals()->updateOrCreate(
                ['external_id' => $item['id'] ?? $item['external_id'] ?? null],
                [
                    'external_id' => $item['id'] ?? $item['external_id'] ?? null,
                    'title' => $item['title'] ?? 'Untitled goal',
                    'description' => $item['description'] ?? null,
                    'deadline' => $item['deadline'] ?? null,
                    'target' => $item['target'] ?? 100,
                    'current_progress' => $item['current_progress'] ?? $item['progress'] ?? 0,
                    'progress' => $item['current_progress'] ?? $item['progress'] ?? 0,
                    'category' => $item['category'] ?? 'study',
                    'status' => $item['status'] ?? 'active',
                ]
            );

            $saved[] = $goal->fresh();
        }

        return response()->json(['items' => $saved]);
    }

    public function storeMilestone(Request $request, Goal $goal): RedirectResponse
    {
        abort_unless($goal->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $goal->milestones()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'completed' => $validated['completed'] ?? false,
            'order_index' => $goal->milestones()->count(),
        ]);

        return back()->with('success', 'Milestone added.');
    }
}
