@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('goals.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Goals</a>
                <span class="text-slate-400">/</span>
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200">New</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Create Academic Goal</h1>
        </div>
        <a href="{{ route('goals.index') }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            Cancel
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="POST" action="{{ route('goals.store') }}" class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Goal Title <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       placeholder="e.g. Master Laravel & REST APIs, Score A in Calculus..."
                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                @error('title')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Why is this goal important? What are the key success criteria?..."
                          class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category & Deadline -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="category" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Category
                    </label>
                    <select name="category" id="category"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="Study" {{ old('category') === 'Study' ? 'selected' : '' }}>Study</option>
                        <option value="Project" {{ old('category') === 'Project' ? 'selected' : '' }}>Project</option>
                        <option value="Exam Prep" {{ old('category') === 'Exam Prep' ? 'selected' : '' }}>Exam Prep</option>
                        <option value="Skill Mastery" {{ old('category') === 'Skill Mastery' ? 'selected' : '' }}>Skill Mastery</option>
                        <option value="Certification" {{ old('category') === 'Certification' ? 'selected' : '' }}>Certification</option>
                    </select>
                    @error('category')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="deadline" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Target Deadline
                    </label>
                    <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('deadline')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Target & Initial Progress -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="target" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Target Metric / Units
                    </label>
                    <input type="number" name="target" id="target" min="1" value="{{ old('target', 100) }}"
                           placeholder="100 (% or units)"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('target')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="current_progress" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Current Progress
                    </label>
                    <input type="number" name="current_progress" id="current_progress" min="0" value="{{ old('current_progress', 0) }}"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('current_progress')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('goals.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                    Save Goal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
