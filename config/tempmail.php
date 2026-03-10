<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email Domain
    |--------------------------------------------------------------------------
    |
    | The domain used for generating temporary email addresses.
    | For local testing: tempmail.local
    | For production: yourdomain.com
    |
    */
    'domain' => env('TEMP_MAIL_DOMAIN', 'tempmail.local'),

    /*
    |--------------------------------------------------------------------------
    | Email Expiration
    |--------------------------------------------------------------------------
    |
    | Number of hours before a temporary email and its messages are deleted.
    |
    */
    'expiration_hours' => (int) env('TEMP_MAIL_EXPIRATION_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Inbox Refresh Interval
    |--------------------------------------------------------------------------
    |
    | How often (in seconds) the inbox page polls for new messages.
    | The homepage polls at double this interval.
    |
    */
    'inbox_refresh_seconds' => (int) env('TEMP_MAIL_REFRESH_SECONDS', 5),

    /*
    |--------------------------------------------------------------------------
    | IMAP Fetch Limit
    |--------------------------------------------------------------------------
    |
    | Maximum number of unseen emails to process per fetch run.
    |
    */
    'fetch_limit' => (int) env('TEMP_MAIL_FETCH_LIMIT', 50),

];
