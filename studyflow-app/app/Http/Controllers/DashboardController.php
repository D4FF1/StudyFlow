<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('dashboard', [
            'tasks' => $user->tasks()->latest()->take(5)->get(),
            'subjects' => $user->subjects()->latest()->take(6)->get(),
            'goals' => $user->goals()->latest()->take(5)->get(),
            'sessions' => $user->studySessions()->latest()->take(5)->get(),
            'notifications' => $user->notifications()->latest()->take(5)->get(),
        ]);
    }
}
