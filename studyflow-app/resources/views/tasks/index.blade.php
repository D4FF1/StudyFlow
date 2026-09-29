@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Tasks</h1>
                <span class="rounded-full bg-indigo-100 px-3 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                    {{ $tasks->count() }} of {{ $totalTasksCount }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Organize, prioritize, and track all your academic work.</p>
        </div>
        <div>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Task
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <!-- Search -->
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title or notes..." 
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:bg-slate-900">
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All Statuses</option>
                    <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>Todo</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <!-- Priority Filter -->
            <div>
                <select name="priority" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All Priorities</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low Priority</option>
                </select>
            </div>

            <!-- Subject Filter -->
            <div>
                <select name="subject_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Deadline Filter & Submit -->
            <div class="flex gap-2">
                <select name="deadline" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    <option value="">Any Deadline</option>
                    <option value="today" {{ request('deadline') === 'today' ? 'selected' : '' }}>Due Today</option>
                    <option value="week" {{ request('deadline') === 'week' ? 'selected' : '' }}>Next 7 Days</option>
                    <option value="overdue" {{ request('deadline') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="upcoming" {{ request('deadline') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                </select>
                <button type="submit" class="rounded-xl bg-slate-800 px-3.5 py-2 text-xs font-semibold text-white hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Task List / Grid -->
    @if($tasks->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tasks as $task)
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <div>
                        <!-- Top tags: Subject & Priority -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                <span class="h-2 w-2 rounded-full" style="background-color: {{ $task->subject?->color ?? '#6366f1' }}"></span>
                                {{ $task->subject?->name ?? 'General' }}
                            </span>
                            
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $task->priority === 'high' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : ($task->priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400') }}">
                                {{ ucfirst($task->priority) }} Priority
                            </span>
                        </div>

                        <!-- Title & Description -->
                        <div class="mt-3">
                            <a href="{{ route('tasks.show', $task) }}" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white line-clamp-1">
                                {{ $task->title }}
                            </a>
                            @if($task->description)
                                <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $task->description }}</p>
                            @endif
                        </div>

                        <!-- Badges: Status, Importance, Difficulty, Estimated Time -->
                        <div class="mt-4 flex flex-wrap gap-1.5 text-[11px]">
                            <span class="rounded-md border border-slate-200 px-2 py-0.5 font-medium text-slate-600 dark:border-slate-700 dark:text-slate-400">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                            <span class="rounded-md border border-slate-200 px-2 py-0.5 text-slate-500 dark:border-slate-700">
                                Imp: <strong class="text-slate-700 dark:text-slate-300">{{ ucfirst($task->importance) }}</strong>
                            </span>
                            <span class="rounded-md border border-slate-200 px-2 py-0.5 text-slate-500 dark:border-slate-700">
                                Diff: <strong class="text-slate-700 dark:text-slate-300">{{ ucfirst($task->difficulty) }}</strong>
                            </span>
                            <span class="rounded-md border border-slate-200 px-2 py-0.5 text-slate-500 dark:border-slate-700">
                                ⏱ {{ $task->estimated_minutes }}m
                            </span>
                        </div>

                        <!-- Deadline & Progress -->
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Progress</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $task->progress }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $task->progress }}%"></div>
                            </div>

                            <div class="flex items-center justify-between pt-1 text-xs">
                                <span class="text-slate-400">Deadline:</span>
                                @if($task->deadline)
                                    <span class="font-medium {{ $task->deadline->isPast() && $task->status !== 'completed' ? 'text-rose-600 font-bold dark:text-rose-400' : 'text-slate-600 dark:text-slate-300' }}">
                                        {{ $task->deadline->format('M d, Y') }}
                                        @if($task->deadline->isPast() && $task->status !== 'completed') (Overdue) @endif
                                    </span>
                                @else
                                    <span class="text-slate-400">None</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                        <!-- Complete / Reopen Action -->
                        @if($task->status === 'completed')
                            <form method="POST" action="{{ route('tasks.reopen', $task) }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400">
                                    ↺ Reopen
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400">
                                    ✓ Complete
                                </button>
                            </form>
                        @endif

                        <div class="flex items-center gap-2">
                            <a href="{{ route('tasks.show', $task) }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" title="View details">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('tasks.edit', $task) }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" title="Edit task">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Delete task">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">No tasks yet</h3>
            <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">Create your first task and start building momentum.</p>
            <div class="mt-6">
                <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Your First Task
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
