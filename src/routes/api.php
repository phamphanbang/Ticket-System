<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TicketParticipantController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum','api'])->group(function () {
  Route::apiResource('users', UserController::class);

  Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index']);
    Route::post('/', [TicketController::class, 'store']);
    Route::get('/{id}', [TicketController::class, 'show']);
    Route::put('/{id}', [TicketController::class, 'update']);
    Route::delete('/{id}', [TicketController::class, 'destroy']);
    
    Route::post('/{id}/execute', [TicketController::class, 'executeTicket']);
    Route::post('/{id}/close', [TicketController::class, 'closeTicket']);

    Route::prefix('/{id}/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
    });
});

  Route::get('/tickets/{id}/participants', [TicketParticipantController::class, 'index']);
  Route::post('/tickets/{id}/participants', [TicketParticipantController::class, 'store']);
  Route::delete('/participants/{id}', [TicketParticipantController::class, 'destroy']);
  Route::post('/tickets/{id}/participants/multiple', [TicketParticipantController::class, 'destroyMultiple']);

// Task routes
Route::prefix('tasks')->group(function () {
    Route::get('/{id}', [TaskController::class, 'show']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    
    // Task status transitions
    Route::post('/{id}/assign', [TaskController::class, 'assignStaff']);
    Route::post('/{id}/ready-for-review', [TaskController::class, 'readyToReview']);
    Route::post('/{id}/needs-revision', [TaskController::class, 'needsRevision']);
    Route::post('/{id}/estimate-approved', [TaskController::class, 'estimateApproved']);
    Route::post('/{id}/start-execution', [TaskController::class, 'startExecution']);
    Route::post('/{id}/block', [TaskController::class, 'blockTask']);
    Route::post('/{id}/change-request', [TaskController::class, 'changeRequest']);
    Route::post('/{id}/execution-ready', [TaskController::class, 'executionReadyToReview']);
    Route::post('/{id}/execution-complete', [TaskController::class, 'completeExecution']);
});
});

