@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Back & Action Bar -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Tasks
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Complete or Reopen -->
            @if($task->status === 'completed')
                <form method="POST" action="{{ route('tasks.reopen', $task) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-amber-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-amber-400">
                        ↺ Reopen Task
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('tasks.complete', $task) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                        ✓ Mark Completed
                    </button>
                </form>
            @endif

            <!-- Edit Button -->
            <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>

            <!-- Delete Button -->
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Are you sure you want to delete this task?');">
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

    <!-- Priority Engine Explanation Banner -->
    <div class="relative overflow-hidden rounded-2xl border border-indigo-200 bg-gradient-to-r from-indigo-50/80 via-white to-violet-50/80 p-5 shadow-sm dark:border-indigo-900/60 dark:from-indigo-950/40 dark:via-slate-900 dark:to-violet-950/40">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <span class="inline-flex items-center rounded-md bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                    Priority Score: {{ $priorityMeta['score'] ?? 50 }}/100
                </span>
                <h3 class="mt-2 text-sm font-bold text-slate-800 dark:text-slate-200">Why this task is important:</h3>
                <ul class="mt-1.5 space-y-1">
                    @forelse($priorityMeta['reasons'] ?? [] as $reason)
                        <li class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <span class="text-indigo-500 font-bold">•</span> {{ $reason }}
                        </li>
                    @empty
                        <li class="text-xs text-slate-500">Standard priority rating based on current deadline and importance parameters.</li>
                    @endforelse
                </ul>
            </div>
            <div class="shrink-0 self-start sm:self-auto">
                <a href="{{ route('focus.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                    Focus on this task
                </a>
            </div>
        </div>
    </div>

    <!-- Main Detail Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
        <!-- Title & Subject Header -->
        <div class="border-b border-slate-100 pb-5 dark:border-slate-800">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($task->subject)
                    <a href="{{ route('subjects.show', $task->subject) }}" class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300">
                        <span class="h-2 w-2 rounded-full" style="background-color: {{ $task->subject->color ?? '#6366f1' }}"></span>
                        {{ $task->subject->name }}
                    </a>
                @else
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                        General / No Subject
                    </span>
                @endif

                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $task->priority === 'high' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : ($task->priority === 'medium' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400') }}">
                    {{ ucfirst($task->priority) }} Priority
                </span>

                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $task->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
            </div>

            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $task->title }}</h1>

            @if($task->description)
                <p class="mt-3 text-sm text-slate-600 leading-relaxed dark:text-slate-300">{{ $task->description }}</p>
            @endif
        </div>

        <!-- Progress Bar -->
        <div>
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-semibold text-slate-700 dark:text-slate-300">Progress</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $task->progress }}%</span>
            </div>
            <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full rounded-full bg-indigo-600 transition-all duration-300" style="width: {{ $task->progress }}%"></div>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 border-t border-b border-slate-100 py-5 dark:border-slate-800">
            <div>
                <p class="text-xs text-slate-400">Deadline</p>
                <p class="mt-1 text-sm font-semibold {{ $task->deadline && $task->deadline->isPast() && $task->status !== 'completed' ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200' }}">
                    {{ $task->deadline ? $task->deadline->format('M d, Y H:i') : 'None set' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Estimated Time</p>
                <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                    {{ $task->estimated_minutes }} minutes
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Importance</p>
                <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                    {{ ucfirst($task->importance) }}
                </p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Difficulty</p>
                <p class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                    {{ ucfirst($task->difficulty) }}
                </p>
            </div>
        </div>

        <!-- Additional Notes -->
        @if($task->notes)
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Notes</h3>
                <div class="mt-2 rounded-xl bg-slate-50 p-4 text-xs text-slate-600 whitespace-pre-wrap dark:bg-slate-800 dark:text-slate-300">
                    {{ $task->notes }}
                </div>
            </div>
        @endif

        <!-- Timestamps -->
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400 pt-2">
            <span>Created on {{ $task->created_at->format('M d, Y \a\t H:i') }}</span>
            <span>Last updated {{ $task->updated_at->diffForHumans() }}</span>
        </div>
    </div>
</div>
@endsection
