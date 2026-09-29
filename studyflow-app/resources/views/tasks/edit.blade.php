@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Tasks</a>
                <span class="text-slate-400">/</span>
                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">{{ Str::limit($task->title, 20) }}</a>
                <span class="text-slate-400">/</span>
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Edit</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Edit Task</h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.show', $task) }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                Cancel
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="POST" action="{{ route('tasks.update', $task) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Title <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title', $task->title) }}" required
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
                          class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Subject & Deadline -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="subject_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Subject
                    </label>
                    <select name="subject_id" id="subject_id"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="">No specific subject</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" {{ old('subject_id', $task->subject_id) == $subj->id ? 'selected' : '' }}>
                                {{ $subj->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="deadline" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Deadline
                    </label>
                    <input type="datetime-local" name="deadline" id="deadline" 
                           value="{{ old('deadline', $task->deadline ? $task->deadline->format('Y-m-d\TH:i') : '') }}"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('deadline')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Priority, Importance, Difficulty -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="priority" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Priority
                    </label>
                    <select name="priority" id="priority"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="importance" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Importance
                    </label>
                    <select name="importance" id="importance"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="low" {{ old('importance', $task->importance) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('importance', $task->importance) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('importance', $task->importance) === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                    @error('importance')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="difficulty" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Difficulty
                    </label>
                    <select name="difficulty" id="difficulty"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="easy" {{ old('difficulty', $task->difficulty) === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty', $task->difficulty) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty', $task->difficulty) === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                    @error('difficulty')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Estimated Minutes, Status, Progress -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="estimated_minutes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Estimated Minutes
                    </label>
                    <input type="number" name="estimated_minutes" id="estimated_minutes" min="1" max="1440" 
                           value="{{ old('estimated_minutes', $task->estimated_minutes) }}"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('estimated_minutes')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Status
                    </label>
                    <select name="status" id="status"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                        <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>Todo</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="progress" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Progress (%)
                    </label>
                    <input type="number" name="progress" id="progress" min="0" max="100" 
                           value="{{ old('progress', $task->progress) }}"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('progress')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Notes
                </label>
                <textarea name="notes" id="notes" rows="2"
                          class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ old('notes', $task->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('tasks.show', $task) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                    Update Task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
