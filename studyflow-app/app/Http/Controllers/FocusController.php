<?php

namespace App\Http\Controllers;

use App\Models\StudySession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FocusController extends Controller
{
    public function index(): View
    {
        $activeSession = auth()->user()->studySessions()->where('status', 'active')->latest()->first();

        return view('focus.index', compact('activeSession'));
    }

    public function start(Request $request): RedirectResponse
    {
        $duration = $request->input('duration_minutes', 45);

        $session = auth()->user()->studySessions()->create([
            'subject_id' => $request->input('subject_id'),
            'topic' => $request->input('topic', 'Focused study'),
            'notes' => $request->input('notes'),
            'status' => 'active',
            'started_at' => now(),
            'duration_minutes' => $duration,
            'start_time' => now()->format('H:i'),
            'color' => '#4f8cff',
        ]);

        return redirect()->route('focus.index')->with('success', 'Focus session started.');
    }

    public function complete(Request $request): RedirectResponse
    {
        $session = auth()->user()->studySessions()->where('status', 'active')->latest()->first();

        if ($session) {
            $minutes = max(1, (int) $request->input('duration_minutes', 45));
            $session->update([
                'status' => 'completed',
                'ended_at' => now(),
                'duration_minutes' => $minutes,
                'duration' => $minutes,
            ]);
        }

        return redirect()->route('focus.index')->with('success', 'Focus session completed.');
    }

    public function cancel(): RedirectResponse
    {
        $session = auth()->user()->studySessions()->where('status', 'active')->latest()->first();

        if ($session) {
            $session->update([
                'status' => 'cancelled',
                'ended_at' => now(),
            ]);
        }

        return redirect()->route('focus.index')->with('success', 'Focus session cancelled.');
    }
}
