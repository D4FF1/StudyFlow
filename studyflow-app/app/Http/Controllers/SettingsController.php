<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = auth()->user()->settings()->firstOrCreate([], [
            'theme' => 'light',
            'default_study_duration' => 45,
            'notifications_enabled' => true,
            'sound_enabled' => true,
        ]);

        return view('settings.index', compact('settings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['nullable', 'in:light,dark'],
            'default_study_duration' => ['nullable', 'integer', 'min:15', 'max:180'],
            'notifications_enabled' => ['nullable', 'boolean'],
            'sound_enabled' => ['nullable', 'boolean'],
        ]);

        $settings = auth()->user()->settings()->firstOrCreate([], [
            'theme' => 'light',
            'default_study_duration' => 45,
            'notifications_enabled' => true,
            'sound_enabled' => true,
        ]);

        $settings->update($validated);

        return redirect()->route('settings.index')->with('success', 'Settings saved.');
    }
}
