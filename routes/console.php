<?php

use App\Models\Email;
use Illuminate\Support\Facades\Schedule;

// Fetch new emails from IMAP every minute
Schedule::command('emails:fetch')->everyMinute()
    ->withoutOverlapping()
    ->name('fetch-emails');

// Clean up expired emails and their messages every hour
Schedule::call(function () {
    Email::where('expires_at', '<', now())->delete();
})->hourly()->name('cleanup-expired-emails');
