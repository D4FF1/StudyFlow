@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Subjects</h1>
                <span class="rounded-full bg-indigo-100 px-3 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                    {{ $subjects->count() }} Subjects
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">Manage your courses, modules, and track dedicated study hours.</p>
        </div>
        <div>
            <a href="{{ route('subjects.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Subject
            </a>
        </div>
    </div>

    <!-- Subjects Grid -->
    @if($subjects->isNotEmpty())
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($subjects as $subject)
                @php
                    $cardColor = $subject->color ?: '#6366f1';
                @endphp
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <div>
                        <!-- Header: Icon & Subject Name -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl font-bold text-white shadow-sm" style="background-color: {{ $cardColor }}">
                                    @if($subject->icon && mb_strlen($subject->icon) <= 3)
                                        <span class="text-lg">{{ $subject->icon }}</span>
                                    @else
                                        <span>{{ strtoupper(substr($subject->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('subjects.show', $subject) }}" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white line-clamp-1">
                                        {{ $subject->name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="h-2 w-2 rounded-full" style="background-color: {{ $cardColor }}"></span>
                                        <span class="text-xs text-slate-400 font-mono">{{ $subject->color ?? 'Default color' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($subject->description)
                            <p class="mt-3 text-xs text-slate-500 line-clamp-2">{{ $subject->description }}</p>
                        @endif

                        <!-- Stats Grid -->
                        <div class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-center dark:bg-slate-800/60">
                            <div>
                                <p class="text-[11px] text-slate-400">Tasks</p>
                                <p class="mt-0.5 text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ $subject->completed_tasks_count }} / {{ $subject->total_tasks_count }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[11px] text-slate-400">Total Study Time</p>
                                <p class="mt-0.5 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $subject->total_study_minutes }}m
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-4 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Completion</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $subject->completion_rate }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full transition-all duration-300" style="width: {{ $subject->completion_rate }}%; background-color: {{ $cardColor }};"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                        <a href="{{ route('subjects.show', $subject) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            View Details &rarr;
                        </a>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('subjects.edit', $subject) }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" title="Edit subject">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400" title="Delete subject">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">No subjects yet</h3>
            <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">Create your first subject to organize your tasks, assignments, and study sessions.</p>
            <div class="mt-6">
                <a href="{{ route('subjects.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Create Subject
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
