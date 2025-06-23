<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;


Schedule::command('ticket:fetch-emails')->everyFiveMinutes();
Artisan::command('slack:sync-users', function () {
  Log::info('Syncing Slack users at ' . now());
})->everySixHours()->withoutOverlapping();

