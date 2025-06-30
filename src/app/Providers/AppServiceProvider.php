<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // DB::listen(function ($query) {
        //     $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);
    
        //     // Find first trace outside vendor or framework files
        //     $source = collect($trace)->first(function ($trace) {
        //         return isset($trace['file']) && !str_contains($trace['file'], '/vendor/');
        //     });
        //     logger()->info('------------------------------------------------------');
        //     logger()->info('SQL: ' . $query->sql . ' [' . implode(', ', $query->bindings) . ']');
        //     logger()->info('Time: ' . $query->time . 'ms');
            
        //     if ($source) {
        //         logger()->info('Called at: ' . $source['file'] . ':' . $source['line']);
        //     }
        //     logger()->info('------------------------------------------------------');
        // });
    }
}
