<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SlackWebhookController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);

Route::post('slack/user', [SlackWebhookController::class, 'handle']);

Route::middleware(['auth'])->group(function () {
  Route::get('auth/me', [AuthController::class, 'me']);
  Route::apiResource('users', UserController::class);
  Route::apiResource('tickets', TicketController::class);
  Route::get('tickets/{id}/attachments', [AttachmentController::class, 'getTicketAttachments']);
  Route::prefix('tickets')->group(function () {
    Route::get('/{id}/logs', [TicketController::class, 'getLogs']);
    Route::get('/{id}/comments', [CommentController::class, 'index']);
    Route::post('/{id}/comments', [CommentController::class, 'store']);
    Route::get('/{id}/comments/{commentId}', [CommentController::class, 'show']);
  });
  Route::delete('/logs/{id}', [TicketController::class, 'deleteLog']);
  Route::put('/comments/{commentId}', [CommentController::class, 'update']);
  Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);
  Route::get('attachments/{id}', [AttachmentController::class, 'download']);
  Route::delete('attachments/{id}', [AttachmentController::class, 'deleteAttachment']);
  Route::get('test-notification/{id}', [SlackWebhookController::class, 'sendTestNotification']);
});
