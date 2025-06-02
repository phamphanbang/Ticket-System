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

  Route::apiResource('tickets', TicketController::class);

  Route::put('tickets/{id}/awaiting-client-approval', [TicketController::class, 'awaitingForClientApproval']);
  Route::put('tickets/{id}/client-approve', [TicketController::class, 'clientApprove']);

  // Route::apiResource('tasks', TaskController::class);
  Route::get('tickets/{id}/tasks', [TaskController::class, 'index']);
  Route::get('tasks/{id}', [TaskController::class, 'show']);
  Route::post('tickets/{id}/tasks', [TaskController::class, 'store']);
  Route::put('tasks/{id}', [TaskController::class, 'update']);
  Route::delete('tasks/{id}', [TaskController::class, 'destroy']);
  Route::post('tasks/{id}/assign-staff', [TaskController::class, 'assignStaff']);
  Route::post('tasks/{id}/ready-to-review', [TaskController::class, 'readyToReview']);
  Route::post('tasks/{id}/notify-leader-for-review', [TaskController::class, 'notifyLeaderForReview']);
  Route::post('tasks/{id}/mark-estimate-needs-revision', [TaskController::class, 'needsRevision']);
  Route::post('tasks/{id}/mark-estimate-approved', [TaskController::class, 'estimateApproved']);
  Route::post('tasks/{id}/start-execution', [TaskController::class, 'startExecution']);

  Route::get('/tickets/{id}/participants', [TicketParticipantController::class, 'index']);
  Route::post('/tickets/{id}/participants', [TicketParticipantController::class, 'store']);
  Route::delete('/participants/{id}', [TicketParticipantController::class, 'destroy']);

});

