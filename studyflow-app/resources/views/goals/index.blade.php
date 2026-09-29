@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Goals & Milestones</h1>
                <span class="rounded-full bg-indigo-100 px-3 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                    {{ $goals->count() }} Goals
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Define long-term academic targets and track milestone progress.</p>
        </div>
        <div>
            <a href="{{ route('goals.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Goal
            </a>
        </div>
    </div>

    @php
        $activeGoals = $goals->filter(fn($g) => ($g->progress ?? 0) < 100 && $g->status !== 'completed');
        $completedGoals = $goals->filter(fn($g) => ($g->progress ?? 0) >= 100 || $g->status === 'completed');
    @endphp

    <!-- Active Goals Section -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
            <span>Active Goals</span>
            <span class="text-xs font-medium text-slate-400">({{ $activeGoals->count() }})</span>
        </h2>

        @if($activeGoals->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($activeGoals as $goal)
                    <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300">
                                    {{ ucfirst($goal->category ?: 'Study') }}
                                </span>
                                @if($goal->deadline)
                                    <span class="text-xs text-slate-500 {{ $goal->deadline->isPast() ? 'text-rose-500 font-semibold' : '' }}">
                                        Due {{ $goal->deadline->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('goals.show', $goal) }}" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white line-clamp-1">
                                    {{ $goal->title }}
                                </a>
                                @if($goal->description)
                                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $goal->description }}</p>
                                @endif
                            </div>

                            <!-- Progress & Target -->
                            <div class="mt-5 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">Progress</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $goal->current_progress }} / {{ $goal->target }} ({{ $goal->progress }}%)
                                    </span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div class="h-full rounded-full bg-indigo-600 transition-all duration-300" style="width: {{ min(100, $goal->progress) }}%"></div>
                                </div>
                            </div>

                            <!-- Milestones summary -->
                            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>{{ $goal->milestones->where('completed', true)->count() }} of {{ $goal->milestones->count() }} milestones completed</span>
                            </div>
                        </div>

                        <!-- Actions Footer -->
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                            <a href="{{ route('goals.show', $goal) }}" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                                View Milestones &rarr;
                            </a>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('goals.edit', $goal) }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" title="Edit goal">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Are you sure you want to delete this goal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Delete goal">
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
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500">No active goals right now.</p>
                <a href="{{ route('goals.create') }}" class="mt-2 inline-block text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Set a new goal</a>
            </div>
        @endif
    </div>

    <!-- Completed Goals Section -->
    @if($completedGoals->isNotEmpty())
        <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span>Completed Goals</span>
                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">{{ $completedGoals->count() }}</span>
            </h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($completedGoals as $goal)
                    <div class="flex flex-col justify-between rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                                    ✓ Accomplished
                                </span>
                                <span class="text-xs text-slate-400">{{ ucfirst($goal->category) }}</span>
                            </div>
                            <h3 class="mt-3 font-bold text-slate-900 dark:text-white">{{ $goal->title }}</h3>
                            <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $goal->description }}</p>
                        </div>
                        <div class="mt-4 flex items-center justify-between pt-3 border-t border-emerald-200/60 text-xs">
                            <a href="{{ route('goals.show', $goal) }}" class="font-semibold text-emerald-700 hover:underline dark:text-emerald-400">View Details &rarr;</a>
                            <form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Delete goal?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-500">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
