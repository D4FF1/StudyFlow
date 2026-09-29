@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500">Tasks Today</p>
            <p class="mt-2 text-3xl font-bold">{{ $tasksToday->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500">Completed Today</p>
            <p class="mt-2 text-3xl font-bold text-emerald-500">{{ $completedToday }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500">Study Time</p>
            <p class="mt-2 text-3xl font-bold text-violet-500">{{ $studyTime }}m</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500">Current Streak</p>
            <p class="mt-2 text-3xl font-bold text-amber-500">{{ $currentStreak }} days</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Today's Focus</h2>
                <a href="{{ route('focus.index') }}" class="text-sm font-medium text-accent-600">Open focus mode</a>
            </div>

            @if($priorityTasks->isNotEmpty())
                @foreach($priorityTasks as $task)
                    <div class="mb-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold">{{ $task->title }}</h3>
                                <p class="text-sm text-slate-500">{{ $task->subject?->name ?? 'General' }}</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">
                                {{ $task->priorityMeta['level'] ?? 'Low' }} priority
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            @foreach($task->priorityMeta['reasons'] ?? [] as $reason)
                                <span class="mr-2">• {{ $reason }}</span>
                            @endforeach
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full rounded-full bg-accent-500" style="width: {{ $task->progress }}%"></div>
                            </div>
                            <span class="text-sm font-medium">{{ $task->progress }}%</span>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-slate-500">No active tasks need attention.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Upcoming Deadlines</h2>
            <div class="mt-4 space-y-3">
                @forelse($upcomingDeadlines as $task)
                    <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium">{{ $task->title }}</span>
                            <span class="rounded-full bg-rose-100 px-2 py-1 text-[11px] font-semibold text-rose-700">
                                {{ strtoupper($task->priority) }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No upcoming deadlines.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Today's Tasks</h2>
            <div class="mt-4 space-y-3">
                @forelse($todayTasks as $task)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3 dark:bg-slate-800">
                        <div>
                            <p class="font-medium">{{ $task->title }}</p>
                            <p class="text-sm text-slate-500">{{ $task->status }}</p>
                        </div>
                        <form method="POST" action="{{ route('tasks.complete', $task) }}">
                            @csrf
                            <button class="rounded-lg bg-accent-500 px-3 py-2 text-sm font-medium text-white">Complete</button>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-500">You have no tasks yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Weekly Study Progress</h2>
            <div class="mt-5 flex items-end gap-2">
                @foreach($weeklyProgress as $day)
                    <div class="flex flex-1 flex-col items-center gap-2">
                        <div class="w-full rounded-t-xl bg-accent-100 dark:bg-accent-900/40" style="height: {{ max(20, $day['completed'] * 20) }}px"></div>
                        <span class="text-[10px] text-slate-500">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">Recent Activity</h2>
        <div class="mt-4 space-y-3">
            @forelse($recentActivity as $task)
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800">
                    <div>
                        <p class="font-medium">{{ $task->title }}</p>
                        <p class="text-sm text-slate-500">{{ $task->updated_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ $task->status }}</span>
                </div>
            @empty
                <p class="text-slate-500">No recent activity.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
