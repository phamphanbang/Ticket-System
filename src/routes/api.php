<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\SlackController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);
Route::get('attachments/{id}', [AttachmentController::class, 'show']);
Route::get('attachments/{id}/download', [AttachmentController::class, 'download']);

Route::post('slack/user', [SlackController::class, 'handle']);


Route::middleware(['auth'])->group(function () {
  Route::get('auth/me', [AuthController::class, 'me']);
  // Route::get('dashboard', [DashboardController::class, 'index']);
  Route::get('dashboard/summary', [DashboardController::class, 'summary']);
  Route::get('dashboard/user-stats', [DashboardController::class, 'statsUserGroupedByDate']);
  Route::get('dashboard/admin-stats', [DashboardController::class, 'statsAdminGroupedByDate']);

  Route::apiResource('users', UserController::class);
  Route::apiResource('tickets', TicketController::class);
  Route::get('tickets/{id}/attachments', [AttachmentController::class, 'getTicketAttachments']);
  Route::post('tickets/{id}/attachments', [AttachmentController::class, 'uploadAttachment']);

  Route::get('clients', [UserController::class, 'clientIndex']);
  Route::get('clients/{id}/tickets', [TicketController::class, 'clientTicket']);

  Route::get('tickets/{id}/mails', [MailController::class, 'index']);
  Route::post('tickets/{id}/mails', [MailController::class, 'store']);

  Route::prefix('tickets')->group(function () {
    Route::get('/{id}/logs', [TicketController::class, 'getLogs']);
    Route::get('/{id}/comments', [CommentController::class, 'index']);
    Route::post('/{id}/comments', [CommentController::class, 'store']);
    Route::get('/{id}/comments/{commentId}', [CommentController::class, 'show']);
  });
  Route::delete('/logs/{id}', [TicketController::class, 'deleteLog']);
  Route::put('/comments/{commentId}', [CommentController::class, 'update']);
  Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']);
  Route::delete('attachments/{id}', [AttachmentController::class, 'deleteAttachment']);
  Route::get('test-notification/{id}', [SlackController::class, 'sendTestNotification']);

  Route::get('/slack/connect-url', [SlackController::class, 'getOAuthUrl']);
  Route::post('/slack/callback', [SlackController::class, 'handleCallback']); // For redirect back
  Route::post('/slack/disconnect', [SlackController::class, 'disconnect']);
});
