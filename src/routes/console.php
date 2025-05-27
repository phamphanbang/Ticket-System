<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');
Artisan::command('ticket:fetch-emails', function () {
  Log::info('Fetching emails for tickets at ' . now());
})->everyFiveMinutes()->withoutOverlapping();
// Schedule::command('ticket:fetch-emails')->everyFiveMinutes();
