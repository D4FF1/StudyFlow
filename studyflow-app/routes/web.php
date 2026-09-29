<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FocusController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

    Route::resource('tasks', TaskController::class);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{task}/reopen', [TaskController::class, 'reopen'])->name('tasks.reopen');

    Route::resource('subjects', SubjectController::class);
    Route::resource('goals', GoalController::class);
    Route::post('/goals/{goal}/milestones', [GoalController::class, 'storeMilestone'])->name('goals.milestones.store');
    Route::post('/goals/{goal}/milestones/{milestone}/toggle', [GoalController::class, 'toggleMilestone'])->name('goals.milestones.toggle');
    Route::patch('/goals/{goal}/milestones/{milestone}', [GoalController::class, 'updateMilestone'])->name('goals.milestones.update');
    Route::delete('/goals/{goal}/milestones/{milestone}', [GoalController::class, 'destroyMilestone'])->name('goals.milestones.destroy');

    Route::get('/planner', [PlannerController::class, 'index'])->name('planner.index');
    Route::get('/planner/create', [PlannerController::class, 'create'])->name('planner.create');
    Route::post('/planner', [PlannerController::class, 'store'])->name('planner.store');
    Route::get('/planner/{plannerSession}/edit', [PlannerController::class, 'edit'])->name('planner.edit');
    Route::put('/planner/{plannerSession}', [PlannerController::class, 'update'])->name('planner.update');
    Route::delete('/planner/{plannerSession}', [PlannerController::class, 'destroy'])->name('planner.destroy');

    Route::get('/focus', [FocusController::class, 'index'])->name('focus.index');
    Route::post('/focus/start', [FocusController::class, 'start'])->name('focus.start');
    Route::post('/focus/complete', [FocusController::class, 'complete'])->name('focus.complete');
    Route::post('/focus/cancel', [FocusController::class, 'cancel'])->name('focus.cancel');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';
