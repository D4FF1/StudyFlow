@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Next Best Task (Priority Engine Recommendation) -->
    @if(isset($nextBestTask) && $nextBestTask)
        <div class="relative overflow-hidden rounded-2xl border border-indigo-200 bg-gradient-to-r from-indigo-50/90 via-white to-violet-50/90 p-5 shadow-sm dark:border-indigo-900/60 dark:from-indigo-950/40 dark:via-slate-900 dark:to-violet-950/40">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                            ⚡ Your Next Best Task
                        </span>
                        <span class="text-xs font-medium text-slate-500">Priority Score: <strong class="text-indigo-600 dark:text-indigo-400">{{ $nextBestTask->priorityMeta['score'] ?? 85 }}/100</strong></span>
                    </div>
                    <h3 class="mt-1.5 text-lg font-bold text-slate-900 dark:text-white">{{ $nextBestTask->title }}</h3>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $nextBestTask->subject?->name ?? 'General' }}</span>
                        @foreach($nextBestTask->priorityMeta['reasons'] ?? [] as $reason)
                            <span>• {{ $reason }}</span>
                        @endforeach
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('tasks.show', $nextBestTask) }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        View Details
                    </a>
                    <a href="{{ route('focus.index') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        Start Focus Now
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Top 4 Metric Cards -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500">Tasks Today</p>
            <p class="mt-2 text-3xl font-bold">{{ $tasksToday->count() }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500">Completed Today</p>
            <p class="mt-2 text-3xl font-bold text-emerald-500">{{ $completedToday }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500">Study Time</p>
            <p class="mt-2 text-3xl font-bold text-violet-500">{{ $studyTime }}m</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-500">Current Streak</p>
            <p class="mt-2 text-3xl font-bold text-amber-500">{{ $currentStreak }} days</p>
        </div>
    </div>

    <!-- Today's Focus & Upcoming Deadlines -->
    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Today's Focus</h2>
                    <p class="text-xs text-slate-400">High priority tasks calculated by StudyFlow Engine</p>
                </div>
                <a href="{{ route('focus.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Open focus mode &rarr;</a>
            </div>

            @if($priorityTasks->isNotEmpty())
                <div class="space-y-3">
                    @foreach($priorityTasks as $task)
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/60">
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-slate-900 hover:text-indigo-600 dark:text-white">{{ $task->title }}</a>
                                    <p class="text-xs text-slate-500">{{ $task->subject?->name ?? 'General' }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ ($task->priorityMeta['level'] ?? '') === 'High' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' }}">
                                    {{ $task->priorityMeta['level'] ?? 'Normal' }} Priority ({{ $task->priorityMeta['score'] ?? 0 }})
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-x-2 text-xs text-slate-500">
                                @foreach($task->priorityMeta['reasons'] ?? [] as $reason)
                                    <span>• {{ $reason }}</span>
                                @endforeach
                            </div>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $task->progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $task->progress }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                    <p class="text-sm font-medium text-slate-500">No active tasks need attention.</p>
                    <a href="{{ route('tasks.create') }}" class="mt-2 inline-block text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Create a task to build momentum</a>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Upcoming Deadlines</h2>
            <div class="mt-4 space-y-3">
                @forelse($upcomingDeadlines as $task)
                    <div class="rounded-xl border border-slate-100 p-3 hover:border-slate-200 transition dark:border-slate-800 dark:hover:border-slate-700">
                        <div class="flex items-center justify-between gap-2">
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-sm text-slate-800 hover:text-indigo-600 truncate dark:text-slate-200">{{ $task->title }}</a>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $task->priority === 'high' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                {{ strtoupper($task->priority) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 flex items-center justify-between">
                            <span>{{ $task->subject?->name ?? 'General' }}</span>
                            <span class="font-medium text-slate-600 dark:text-slate-400">{{ $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline' }}</span>
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                        <p class="text-sm text-slate-500">No upcoming deadlines.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Today's Tasks & Weekly Progress -->
    <div class="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Today's Tasks</h2>
                <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">View all</a>
            </div>
            <div class="space-y-3">
                @forelse($todayTasks as $task)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50/80 p-3 dark:bg-slate-800/60">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-sm hover:text-indigo-600">{{ $task->title }}</a>
                            <p class="text-xs text-slate-500">{{ $task->subject?->name ?? 'General' }} • {{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
                        </div>
                        <div>
                            @if($task->status === 'completed')
                                <form method="POST" action="{{ route('tasks.reopen', $task) }}">
                                    @csrf
                                    <button class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Reopen</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                    @csrf
                                    <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500 transition">Complete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                        <p class="text-sm text-slate-500">You have no tasks yet.</p>
                        <a href="{{ route('tasks.create') }}" class="mt-2 inline-block text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Add a new task</a>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Weekly Study Progress</h2>
            <p class="text-xs text-slate-400 mt-1">Tasks completed across the past 7 days</p>
            <div class="mt-6 flex items-end gap-2 h-36">
                @foreach($weeklyProgress as $day)
                    <div class="flex flex-1 flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400">{{ $day['completed'] }}</span>
                        <div class="w-full rounded-t-xl bg-indigo-100 hover:bg-indigo-200 transition dark:bg-indigo-950/60" style="height: {{ max(12, min(90, $day['completed'] * 24)) }}px"></div>
                        <span class="text-[10px] text-slate-500 truncate w-full text-center">{{ $day['date'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-lg font-semibold">Recent Activity</h2>
        <div class="mt-4 space-y-3">
            @forelse($recentActivity as $task)
                <div class="flex items-center justify-between rounded-xl bg-slate-50/70 px-4 py-3 dark:bg-slate-800/50">
                    <div>
                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-sm hover:text-indigo-600">{{ $task->title }}</a>
                        <p class="text-xs text-slate-500">{{ $task->subject?->name ?? 'General' }} • Updated {{ $task->updated_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $task->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                    <p class="text-sm text-slate-500">No recent activity.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
