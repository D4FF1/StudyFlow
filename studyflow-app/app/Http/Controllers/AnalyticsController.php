<?php

namespace App\Http\Controllers;

use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $totalStudyTime = (int) $user->studySessions()->whereNotNull('ended_at')->sum('duration_minutes');
        $tasksCompleted = $user->tasks()->whereNotNull('completed_at')->count();
        $totalTasks = $user->tasks()->count();
        $completionRate = $totalTasks > 0 ? round(($tasksCompleted / $totalTasks) * 100, 1) : 0;
        
        $streakService = app(StreakService::class);
        $currentStreak = $streakService->current($user);
        $longestStreak = $streakService->longest($user);

        $completedSessions = $user->studySessions()->whereNotNull('ended_at');
        $averageSessionLength = (int) round($completedSessions->avg('duration_minutes') ?? 0);

        // Chart 1: Weekly Study Time (Last 7 Days)
        $weeklyStudyTime = [];
        // Chart 2: Tasks Completed (Last 7 Days)
        $weeklyTasksCompleted = [];
        // Chart 4: Study Activity (Daily session counts)
        $studyActivity = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();
            $label = $date->format('D, M j');
            $shortLabel = $date->format('D');

            $dayMinutes = (int) $user->studySessions()
                ->whereNotNull('ended_at')
                ->whereDate('started_at', $dateString)
                ->sum('duration_minutes');

            $dayTasks = $user->tasks()
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', $dateString)
                ->count();

            $daySessionsCount = $user->studySessions()
                ->whereDate('started_at', $dateString)
                ->count();

            $weeklyStudyTime[] = [
                'label' => $shortLabel,
                'full_date' => $label,
                'minutes' => $dayMinutes,
            ];

            $weeklyTasksCompleted[] = [
                'label' => $shortLabel,
                'full_date' => $label,
                'count' => $dayTasks,
            ];

            $studyActivity[] = [
                'label' => $shortLabel,
                'full_date' => $label,
                'sessions' => $daySessionsCount,
                'minutes' => $dayMinutes,
            ];
        }

        // Chart 3: Subject Distribution
        $subjects = $user->subjects()->with(['tasks', 'studySessions'])->get();
        $subjectDistribution = [];
        $palette = ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6'];

        foreach ($subjects as $index => $subject) {
            $mins = (int) ($subject->studySessions->whereNotNull('ended_at')->sum('duration_minutes') ?: ($subject->study_minutes ?? 0));
            $subjectDistribution[] = [
                'name' => $subject->name,
                'color' => $subject->color ?: $palette[$index % count($palette)],
                'minutes' => $mins,
                'task_count' => $subject->tasks->count(),
            ];
        }

        $hasData = ($totalStudyTime > 0) || ($tasksCompleted > 0) || ($totalTasks > 0);

        return view('analytics.index', compact(
            'totalStudyTime',
            'tasksCompleted',
            'completionRate',
            'currentStreak',
            'longestStreak',
            'averageSessionLength',
            'weeklyStudyTime',
            'weeklyTasksCompleted',
            'subjectDistribution',
            'studyActivity',
            'hasData'
        ));
    }
}
