<?php

use App\Models\Email;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    Email::where('expires_at', '<', now())->delete();
})->hourly()->name('cleanup-expired-emails');
