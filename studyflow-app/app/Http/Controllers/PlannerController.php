<?php

namespace App\Http\Controllers;

use App\Models\PlannerSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlannerController extends Controller
{
    public function index(): View
    {
        $sessions = auth()->user()->plannerSessions()->with('subject')->orderBy('start_at')->get();

        return view('planner.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('planner.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'notes' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
        ]);

        auth()->user()->plannerSessions()->create($validated);

        return redirect()->route('planner.index')->with('success', 'Planner session created.');
    }

    public function edit(PlannerSession $plannerSession): View
    {
        abort_unless($plannerSession->user_id === auth()->id(), 403);

        return view('planner.edit', compact('plannerSession'));
    }

    public function update(Request $request, PlannerSession $plannerSession): RedirectResponse
    {
        abort_unless($plannerSession->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'notes' => ['nullable', 'string'],
            'start_at' => ['sometimes', 'required', 'date'],
            'end_at' => ['sometimes', 'required', 'date', 'after_or_equal:start_at'],
        ]);

        $plannerSession->update($validated);

        return redirect()->route('planner.index')->with('success', 'Planner session updated.');
    }

    public function destroy(PlannerSession $plannerSession): RedirectResponse
    {
        abort_unless($plannerSession->user_id === auth()->id(), 403);

        $plannerSession->delete();

        return redirect()->route('planner.index')->with('success', 'Planner session deleted.');
    }
}
