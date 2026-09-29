@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('goals.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Goals
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('goals.edit', $goal) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Goal
            </a>
            <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Delete this goal and all its milestones?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete Goal
                </button>
            </form>
        </div>
    </div>

    <!-- Goal Header & Progress Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                    {{ ucfirst($goal->category ?: 'Study') }}
                </span>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $goal->status === 'completed' || $goal->progress >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $goal->status === 'completed' || $goal->progress >= 100 ? 'Completed' : 'Active' }}
                </span>
            </div>
            @if($goal->deadline)
                <span class="text-xs font-medium text-slate-500 {{ $goal->deadline->isPast() ? 'text-rose-500 font-bold' : '' }}">
                    Target Deadline: {{ $goal->deadline->format('M d, Y') }}
                </span>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $goal->title }}</h1>
            @if($goal->description)
                <p class="mt-2 text-sm text-slate-600 leading-relaxed dark:text-slate-300">{{ $goal->description }}</p>
            @endif
        </div>

        <!-- Progress Metrics -->
        <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60 space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="font-semibold text-slate-700 dark:text-slate-300">Current Progress</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-base">
                    {{ $goal->current_progress }} / {{ $goal->target }} ({{ $goal->progress }}%)
                </span>
            </div>
            <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                <div class="h-full rounded-full bg-indigo-600 transition-all duration-300" style="width: {{ min(100, $goal->progress) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Milestones Section -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Milestones</h2>
                <p class="text-xs text-slate-500">Checking milestones automatically recalibrates goal progress.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                {{ $goal->milestones->where('completed', true)->count() }} / {{ $goal->milestones->count() }} Done
            </span>
        </div>

        <!-- Milestones List -->
        <div class="space-y-3">
            @forelse($goal->milestones->sortBy('order_index') as $milestone)
                <div class="flex items-start justify-between gap-3 rounded-xl border p-4 transition {{ $milestone->completed ? 'border-emerald-100 bg-emerald-50/30 dark:border-emerald-950/60 dark:bg-emerald-950/20' : 'border-slate-200 bg-slate-50/60 dark:border-slate-800 dark:bg-slate-800/40' }}">
                    <div class="flex items-start gap-3">
                        <!-- Toggle Form -->
                        <form method="POST" action="{{ route('goals.milestones.toggle', [$goal, $milestone]) }}" class="mt-0.5">
                            @csrf
                            <button type="submit" class="flex h-5 w-5 items-center justify-center rounded-lg border transition {{ $milestone->completed ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 bg-white hover:border-indigo-500 dark:border-slate-600 dark:bg-slate-800' }}">
                                @if($milestone->completed)
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @endif
                            </button>
                        </form>

                        <div>
                            <p class="text-sm font-semibold {{ $milestone->completed ? 'line-through text-slate-500 dark:text-slate-400' : 'text-slate-900 dark:text-white' }}">
                                @if($milestone->completed)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold mr-1">✓</span>
                                @else
                                    <span class="text-slate-400 font-normal mr-1">○</span>
                                @endif
                                {{ $milestone->title }}
                            </p>
                            @if($milestone->description)
                                <p class="text-xs text-slate-500 mt-1 {{ $milestone->completed ? 'line-through' : '' }}">{{ $milestone->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Milestone Actions: Delete -->
                    <div class="flex items-center gap-1 shrink-0">
                        <form method="POST" action="{{ route('goals.milestones.destroy', [$goal, $milestone]) }}" onsubmit="return confirm('Delete this milestone?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg p-1 text-slate-400 hover:text-rose-600" title="Delete milestone">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center dark:border-slate-800">
                    <p class="text-xs text-slate-500">No milestones yet for this goal. Add your first milestone below!</p>
                </div>
            @endforelse
        </div>

        <!-- Add Milestone Form -->
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 dark:border-slate-800 dark:bg-slate-800/40">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">+ Add New Milestone</h3>
            <form method="POST" action="{{ route('goals.milestones.store', $goal) }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <input type="text" name="title" required placeholder="Milestone title (e.g. Read Chapters 1-4)" 
                               class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                    <div>
                        <input type="text" name="description" placeholder="Optional notes or sub-tasks" 
                               class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        Add Milestone
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
