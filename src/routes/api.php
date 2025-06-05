<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;

Route::post('auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('auth/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum','api'])->group(function () {
  Route::apiResource('users', UserController::class);
  Route::apiResource('tickets', TicketController::class);
  Route::prefix('tickets')->group(function () {
    
    Route::get('/{id}/logs', [TicketController::class, 'getLogs']);
});

});

