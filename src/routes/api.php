<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum', 'api'])->group(function () {
  Route::apiResource('users', UserController::class);
  Route::apiResource('tickets', TicketController::class);
  Route::get('tickets/{id}/attachments', [TicketController::class, 'getAttachments']);
  Route::prefix('tickets')->group(function () {
    Route::get('/{id}/logs', [TicketController::class, 'getLogs']);
    Route::get('/{id}/comments', [CommentController::class, 'index']);
    Route::post('/{id}/comments', [CommentController::class, 'store']);
    Route::get('/{id}/comments/{commentId}', [CommentController::class, 'show']);
  });
  Route::put('/comments/{commentId}', [CommentController::class, 'update']);
  Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);
  Route::get('attachments/{id}', [CommentController::class, 'download']);
  Route::delete('attachments/{id}', [CommentController::class, 'deleteAttachment']);
});
