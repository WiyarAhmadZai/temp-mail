<?php

use App\Models\Email;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Fetch new emails from IMAP every minute
Schedule::command('emails:fetch')->everyMinute()
    ->withoutOverlapping()
    ->name('fetch-emails');

// Clean up expired emails in batches every hour
Schedule::call(function () {
    $batchSize = config('tempmail.cleanup_batch_size');
    $totalDeleted = 0;

    do {
        $deleted = Email::where('expires_at', '<', now())
            ->limit($batchSize)
            ->delete();

        $totalDeleted += $deleted;
    } while ($deleted === $batchSize);

    if ($totalDeleted > 0) {
        Log::channel('tempmail')->info('Cleanup: deleted expired emails', [
            'count' => $totalDeleted,
        ]);
    }
})->hourly()->name('cleanup-expired-emails');
