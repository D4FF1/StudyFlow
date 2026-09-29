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

        $totalStudyTime = $user->studySessions()->whereNotNull('ended_at')->sum('duration_minutes');
        $tasksCompleted = $user->tasks()->whereNotNull('completed_at')->count();
        $completionRate = $user->tasks()->count() > 0 ? round(($user->tasks()->whereNotNull('completed_at')->count() / $user->tasks()->count()) * 100, 1) : 0;
        $streakService = app(StreakService::class);
        $currentStreak = $streakService->current($user);
        $longestStreak = $streakService->longest($user);

        $mostStudiedSubject = $user->studySessions()->selectRaw('subject_id, COUNT(*) as sessions')
            ->groupBy('subject_id')
            ->orderByDesc('sessions')
            ->first();

        $averageSessionLength = $user->studySessions()->whereNotNull('ended_at')->avg('duration_minutes') ?? 0;

        $weeklyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $weeklyActivity[] = [
                'label' => $day->format('D'),
                'value' => $user->studySessions()->whereDate('started_at', $day)->count(),
            ];
        }

        return view('analytics.index', compact(
            'totalStudyTime',
            'tasksCompleted',
            'completionRate',
            'currentStreak',
            'longestStreak',
            'mostStudiedSubject',
            'averageSessionLength',
            'weeklyActivity'
        ));
    }
}
