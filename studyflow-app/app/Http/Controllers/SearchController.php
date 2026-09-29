<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', $request->input('query', '')));

        if ($query === '') {
            $tasks = collect();
            $subjects = collect();
            $goals = collect();
        } else {
            $user = auth()->user();

            $tasks = $user->tasks()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('notes', 'like', "%{$query}%");
                })
                ->with('subject')
                ->latest()
                ->get();

            $subjects = $user->subjects()
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->withCount('tasks')
                ->get();

            $goals = $user->goals()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%");
                })
                ->with('milestones')
                ->latest()
                ->get();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'query' => $query,
                'tasks' => $tasks,
                'subjects' => $subjects,
                'goals' => $goals,
            ]);
        }

        return view('search.index', compact('query', 'tasks', 'subjects', 'goals'));
    }
}
