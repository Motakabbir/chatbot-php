<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedbackController;

// Auth routes
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Chat routes
    Route::prefix('chat')->group(function () {
        Route::post('start', [ChatController::class, 'startSession']);
        Route::post('message', [ChatController::class, 'sendMessage']);
        Route::get('messages/{session_id}', [ChatController::class, 'getMessages']);
        Route::post('end', [ChatController::class, 'endSession']);
    });

    // Agent routes
    Route::prefix('agent')->middleware(['auth:sanctum', 'role:agent'])->group(function () {
        Route::get('sessions/waiting', [AgentController::class, 'getWaitingSessions']);
        Route::post('sessions/accept', [AgentController::class, 'acceptSession']);
        Route::post('message', [AgentController::class, 'sendMessage']);
    });

    // Feedback routes
    Route::prefix('feedback')->group(function () {
        Route::post('submit', [FeedbackController::class, 'store']);
        Route::get('session/{session_id}', [FeedbackController::class, 'getSessionFeedback']);
    });
});
