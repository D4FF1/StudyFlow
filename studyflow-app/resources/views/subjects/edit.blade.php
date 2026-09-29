@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('subjects.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Subjects</a>
                <span class="text-slate-400">/</span>
                <a href="{{ route('subjects.show', $subject) }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">{{ $subject->name }}</a>
                <span class="text-slate-400">/</span>
                <span class="text-sm font-medium text-slate-800 dark:text-slate-200">Edit</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">Edit Subject</h1>
        </div>
        <a href="{{ route('subjects.show', $subject) }}" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
            Cancel
        </a>
    </div>

    <!-- Form Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <form method="POST" action="{{ route('subjects.update', $subject) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Subject Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $subject->name) }}" required
                       class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">{{ old('description', $subject->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Icon & Color -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="icon" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Icon (Emoji or 2-letter Code)
                    </label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon', $subject->icon) }}"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100">
                    @error('icon')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="color" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Theme Color
                    </label>
                    <div class="mt-1.5 flex items-center gap-3">
                        <input type="color" name="color" id="color" value="{{ old('color', $subject->color ?? '#6366f1') }}"
                               class="h-10 w-14 cursor-pointer rounded-lg border border-slate-200 bg-white p-1 dark:border-slate-700 dark:bg-slate-800">
                        <span class="text-xs text-slate-500 font-mono">{{ $subject->color ?? '#6366f1' }}</span>
                    </div>
                    @error('color')
                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('subjects.show', $subject) }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                    Update Subject
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
