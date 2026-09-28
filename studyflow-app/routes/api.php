<?php

use App\Http\Controllers\GoalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudySessionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/me', function (Request $request) {
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email']),
        ]);
    });

    Route::post('/tasks/sync', [TaskController::class, 'sync']);
    Route::post('/subjects/sync', [SubjectController::class, 'sync']);
    Route::post('/goals/sync', [GoalController::class, 'sync']);
    Route::post('/study-sessions/sync', [StudySessionController::class, 'sync']);
    Route::post('/notifications/sync', [NotificationController::class, 'sync']);

    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('goals', GoalController::class);
    Route::apiResource('study-sessions', StudySessionController::class);
    Route::apiResource('notifications', NotificationController::class);
});
