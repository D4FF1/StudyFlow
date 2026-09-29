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
        $user = auth()->user();
        $activeSession = $user->studySessions()->with('subject')->where('status', 'active')->latest()->first();
        $subjects = $user->subjects()->get();
        $tasks = $user->tasks()->where('status', '!=', 'completed')->with('subject')->get();
        $recentSessions = $user->studySessions()->with('subject')->where('status', 'completed')->latest()->take(5)->get();
        $defaultDuration = $user->settings()->first()?->default_study_duration ?? 45;

        return view('focus.index', compact('activeSession', 'subjects', 'tasks', 'recentSessions', 'defaultDuration'));
    }

    public function start(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $duration = max(1, (int) $request->input('duration_minutes', 45));
        $subjectId = $request->input('subject_id');
        $taskId = $request->input('task_id');
        $topic = $request->input('topic');

        $subjectName = null;
        if ($subjectId) {
            $subject = $user->subjects()->find($subjectId);
            $subjectName = $subject?->name;
        }

        if ($taskId && ! $topic) {
            $task = $user->tasks()->find($taskId);
            if ($task) {
                $topic = $task->title;
                if (! $subjectId && $task->subject_id) {
                    $subjectId = $task->subject_id;
                    $subjectName = $task->subject?->name;
                }
            }
        }

        $user->studySessions()->create([
            'subject_id' => $subjectId,
            'subject_name' => $subjectName,
            'topic' => $topic ?: 'Focused study',
            'notes' => $taskId ? "Task #{$taskId}" : $request->input('notes'),
            'status' => 'active',
            'started_at' => now(),
            'duration_minutes' => $duration,
            'duration' => $duration,
            'start_time' => now()->format('H:i'),
            'color' => '#4f8cff',
        ]);

        return redirect()->route('focus.index')->with('success', 'Focus session started. Stay locked in!');
    }

    public function complete(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $session = $user->studySessions()->where('status', 'active')->latest()->first();

        if ($session) {
            $minutes = max(1, (int) $request->input('duration_minutes', $session->duration_minutes ?: 45));
            $session->update([
                'status' => 'completed',
                'ended_at' => now(),
                'duration_minutes' => $minutes,
                'duration' => $minutes,
            ]);

            if ($session->subject_id) {
                $subject = $user->subjects()->find($session->subject_id);
                if ($subject) {
                    $subject->increment('study_minutes', $minutes);
                }
            }

            // Sync achievements
            app(\App\Services\AchievementService::class)->syncForUser($user);

            // Create notification for completed session
            $user->notifications()->create([
                'type' => 'focus',
                'message' => "Focus session completed. +{$minutes} minutes study time. Keep the momentum going!",
            ]);

            return redirect()->route('focus.index')->with('success', "Focus session completed. +{$minutes} minutes study time! Keep the momentum going.");
        }

        return redirect()->route('focus.index')->with('error', 'No active session found.');
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
