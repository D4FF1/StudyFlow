<?php

namespace App\Http\Controllers;

use App\Models\PlannerSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlannerController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $startOfWeek = $request->filled('week')
            ? \Carbon\Carbon::parse($request->input('week'))->startOfWeek()
            : now()->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        $allSessions = $user->plannerSessions()
            ->with('subject')
            ->whereBetween('start_at', [$startOfWeek->copy()->startOfDay(), $endOfWeek->copy()->endOfDay()])
            ->orderBy('start_at')
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $daySessions = $allSessions->filter(function ($s) use ($dayDate) {
                return $s->start_at && $s->start_at->isSameDay($dayDate);
            })->values();

            $days[] = [
                'day_name' => $dayDate->format('l'),
                'date' => $dayDate->toDateString(),
                'display_date' => $dayDate->format('M d'),
                'is_today' => $dayDate->isToday(),
                'sessions' => $daySessions,
            ];
        }

        $subjects = $user->subjects()->get();
        $prevWeek = $startOfWeek->copy()->subWeek()->toDateString();
        $nextWeek = $startOfWeek->copy()->addWeek()->toDateString();
        $currentWeek = now()->startOfWeek()->toDateString();

        return view('planner.index', compact('days', 'allSessions', 'subjects', 'startOfWeek', 'endOfWeek', 'prevWeek', 'nextWeek', 'currentWeek'));
    }

    public function create(): View
    {
        $subjects = auth()->user()->subjects()->get();

        return view('planner.create', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->prepareSessionDates($request);

        $validated = validator($data, [
            'title' => ['required', 'string', 'max:255'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'notes' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ])->validate();

        auth()->user()->plannerSessions()->create($validated);

        return redirect()->route('planner.index')->with('success', 'Planner session created.');
    }

    public function edit(PlannerSession $plannerSession): View
    {
        abort_unless($plannerSession->user_id === auth()->id(), 403);
        $subjects = auth()->user()->subjects()->get();

        return view('planner.edit', compact('plannerSession', 'subjects'));
    }

    public function update(Request $request, PlannerSession $plannerSession): RedirectResponse
    {
        abort_unless($plannerSession->user_id === auth()->id(), 403);

        $data = $this->prepareSessionDates($request);

        $validated = validator($data, [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'notes' => ['nullable', 'string'],
            'start_at' => ['sometimes', 'required', 'date'],
            'end_at' => ['sometimes', 'required', 'date', 'after:start_at'],
        ])->validate();

        $plannerSession->update($validated);

        return redirect()->route('planner.index')->with('success', 'Planner session updated.');
    }

    protected function prepareSessionDates(Request $request): array
    {
        $all = $request->all();

        if ($request->filled('date') && $request->filled('start_time') && $request->filled('end_time')) {
            $date = $request->input('date');
            $all['start_at'] = \Carbon\Carbon::parse("{$date} {$request->input('start_time')}")->toDateTimeString();
            $all['end_at'] = \Carbon\Carbon::parse("{$date} {$request->input('end_time')}")->toDateTimeString();
        }

        return $all;
    }

    public function destroy(PlannerSession $plannerSession): RedirectResponse
    {
        abort_unless($plannerSession->user_id === auth()->id(), 403);

        $plannerSession->delete();

        return redirect()->route('planner.index')->with('success', 'Planner session deleted.');
    }
}
