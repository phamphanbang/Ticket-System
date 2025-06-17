<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('ticket:fetch-emails', function () {
  Log::info('Fetching emails for tickets at ' . now());
})->everyFiveMinutes()->withoutOverlapping();

Artisan::command('slack:sync-users', function () {
  Log::info('Syncing Slack users at ' . now());
})->everySixHours()->withoutOverlapping();

