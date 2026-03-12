<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('generate-email', function (Request $request) {
            $maxAttempts = config('tempmail.rate_limit_per_minute');

            return Limit::perMinute($maxAttempts)
                ->by($request->ip())
                ->response(function () {
                    return back()->with('error', 'Too many emails generated. Please wait a moment.');
                });
        });
    }
}
