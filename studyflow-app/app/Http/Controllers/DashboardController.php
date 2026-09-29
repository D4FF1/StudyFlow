<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\PriorityEngine;
use App\Services\StreakService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $tasksToday = $user->tasks()->whereDate('deadline', today())->orderBy('deadline')->get();
        $completedToday = $user->tasks()->whereNotNull('completed_at')->whereDate('completed_at', today())->count();
        $studyTime = $user->studySessions()->whereNotNull('ended_at')->sum('duration_minutes');
        $currentStreak = app(StreakService::class)->current($user);

        $upcomingDeadlines = $user->tasks()
            ->whereNotNull('deadline')
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->take(5)
            ->get();

        $todayTasks = $user->tasks()->orderBy('deadline')->take(6)->get();
        $priorityEngine = app(PriorityEngine::class);
        $priorityTasks = $user->tasks()->where('status', '!=', 'completed')->orderBy('deadline')->get()->map(function ($task) use ($priorityEngine) {
            $task->priorityMeta = $priorityEngine->calculate($task);
            return $task;
        })->take(3);

        $weeklyProgress = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = $user->tasks()->whereDate('completed_at', $date)->count();
            $weeklyProgress[] = [
                'date' => now()->subDays($i)->format('M d'),
                'completed' => $count,
            ];
        }

        $recentActivity = $user->tasks()->latest()->take(5)->get();

        return view('dashboard', compact(
            'tasksToday',
            'completedToday',
            'studyTime',
            'currentStreak',
            'upcomingDeadlines',
            'todayTasks',
            'priorityTasks',
            'weeklyProgress',
            'recentActivity'
        ));
    }
}
