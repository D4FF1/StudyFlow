@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header with Action Buttons -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('subjects.index') }}" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-500 hover:text-slate-800 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-bold text-white shadow-sm" style="background-color: {{ $subject->color ?: '#6366f1' }}">
                    @if($subject->icon && mb_strlen($subject->icon) <= 3)
                        <span>{{ $subject->icon }}</span>
                    @else
                        <span>{{ strtoupper(substr($subject->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $subject->name }}</h1>
                    <p class="text-xs text-slate-500">{{ $subject->description ?: 'No description provided.' }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('tasks.create') }}?subject_id={{ $subject->id }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Task
            </a>
            <a href="{{ route('subjects.edit', $subject) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject and its associated tasks?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-400">Total Tasks</p>
            <p class="mt-2 text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-400">Completed Tasks</p>
            <p class="mt-2 text-2xl font-bold text-emerald-500">{{ $stats['completed'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-400">Completion Rate</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['progress'] }}%</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-xs font-medium text-slate-400">Total Study Time</p>
            <p class="mt-2 text-2xl font-bold text-violet-500">{{ $stats['study_minutes'] }}m</p>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between text-sm mb-2">
            <span class="font-semibold text-slate-700 dark:text-slate-300">Curriculum Progress</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $stats['completed'] }} of {{ $stats['total'] }} tasks completed ({{ $stats['progress'] }}%)</span>
        </div>
        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['progress'] }}%; background-color: {{ $subject->color ?: '#6366f1' }};"></div>
        </div>
    </div>

    <!-- Upcoming Tasks & Completed Tasks -->
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Upcoming Tasks -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Upcoming Tasks ({{ $upcomingTasks->count() }})</h2>
                <a href="{{ route('tasks.create') }}?subject_id={{ $subject->id }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">+ Add Task</a>
            </div>

            <div class="space-y-3">
                @forelse($upcomingTasks as $task)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 p-3 hover:border-slate-200 transition dark:border-slate-800 dark:hover:border-slate-700">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-sm hover:text-indigo-600">{{ $task->title }}</a>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                <span>{{ $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline' }}</span>
                                <span>•</span>
                                <span>⏱ {{ $task->estimated_minutes }}m</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $task->priority === 'high' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                {{ strtoupper($task->priority) }}
                            </span>
                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-emerald-500">
                                    ✓
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                        <p class="text-xs text-slate-500">No pending tasks for this subject.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Completed Tasks ({{ $completedTasks->count() }})</h2>
            <div class="space-y-3">
                @forelse($completedTasks as $task)
                    <div class="flex items-center justify-between rounded-xl bg-slate-50/70 p-3 dark:bg-slate-800/60">
                        <div>
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-sm line-through text-slate-500">{{ $task->title }}</a>
                            <p class="text-xs text-slate-400">Completed {{ $task->completed_at ? $task->completed_at->diffForHumans() : 'recently' }}</p>
                        </div>
                        <form method="POST" action="{{ route('tasks.reopen', $task) }}">
                            @csrf
                            <button type="submit" class="text-xs text-amber-600 hover:underline">Reopen</button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                        <p class="text-xs text-slate-500">No completed tasks yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Study Sessions -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Recent Study Sessions</h2>
        <div class="space-y-3">
            @forelse($recentSessions as $session)
                <div class="flex items-center justify-between rounded-xl bg-slate-50/70 px-4 py-3 dark:bg-slate-800/60">
                    <div>
                        <p class="font-medium text-sm text-slate-900 dark:text-white">{{ $session->topic ?: 'Focused Study' }}</p>
                        <p class="text-xs text-slate-500">{{ $session->started_at ? $session->started_at->format('M d, Y H:i') : 'Recorded Session' }}</p>
                    </div>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                        +{{ $session->duration_minutes }}m
                    </span>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                    <p class="text-xs text-slate-500">No recorded study sessions for this subject yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
