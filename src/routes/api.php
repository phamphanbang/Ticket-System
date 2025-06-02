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
    
    // Ticket status transitions
    Route::post('/{id}/status/awaiting-client-approval', [TicketController::class, 'awaitingForClientApproval']);
    Route::post('/{id}/status/client-approve', [TicketController::class, 'clientApprove']);
    
    // Nested task routes
    Route::prefix('/{ticketId}/tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
    });
});

  Route::get('/tickets/{id}/participants', [TicketParticipantController::class, 'index']);
  Route::post('/tickets/{id}/participants', [TicketParticipantController::class, 'store']);
  Route::delete('/participants/{id}', [TicketParticipantController::class, 'destroy']);

// Task routes
Route::prefix('tasks')->group(function () {
    Route::get('/{id}', [TaskController::class, 'show']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    
    // Task status transitions
    Route::post('/{id}/status/assign', [TaskController::class, 'assignStaff']);
    Route::post('/{id}/status/ready-for-review', [TaskController::class, 'readyToReview']);
    Route::post('/{id}/status/needs-revision', [TaskController::class, 'needsRevision']);
    Route::post('/{id}/status/estimate-approved', [TaskController::class, 'estimateApproved']);
    Route::post('/{id}/status/start-execution', [TaskController::class, 'startExecution']);
    Route::post('/{id}/status/block', [TaskController::class, 'blockTask']);
    Route::post('/{id}/status/change-request', [TaskController::class, 'changeRequest']);
    Route::post('/{id}/status/execution-ready', [TaskController::class, 'executionReadyToReview']);
});
});

