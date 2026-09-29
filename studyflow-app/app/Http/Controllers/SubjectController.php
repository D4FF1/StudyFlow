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
        $subjects = auth()->user()->subjects()->with('tasks')->get();

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

        $subject->load('tasks');
        $progress = $subject->tasks()->count() ? (int) round(($subject->tasks()->where('status', 'completed')->count() / $subject->tasks()->count()) * 100) : 0;

        if ($request->expectsJson()) {
            return response()->json(['subject' => $subject, 'progress' => $progress]);
        }

        return view('subjects.show', compact('subject', 'progress'));
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
