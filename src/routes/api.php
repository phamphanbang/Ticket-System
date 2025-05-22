<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
  Route::apiResource('users', UserController::class);

  Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
  Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('admin.show.ticket');
  Route::put('tickets/{ticket}', [TicketController::class, 'update'])->name('admin.update.ticket');
  Route::post('tickets/create', [TicketController::class, 'adminCreateTicket'])->name('admin.create.ticket');
  Route::post('tickets/{ticket}/confirm', [TicketController::class, 'staffConfirmTicket'])->name('staff.confirm.ticket');
  Route::post('tickets/{ticket}/resolve', [TicketController::class, 'staffResolveTicket'])->name('staff.resolve.ticket');
  Route::post('tickets/{ticket}/delay', [TicketController::class, 'staffDelayTicket'])->name('staff.delay.ticket');
});
