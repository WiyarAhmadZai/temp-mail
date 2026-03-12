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

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Max email generations per user per minute.
    |
    */
    'rate_limit_per_minute' => (int) env('TEMP_MAIL_RATE_LIMIT', 5),

    /*
    |--------------------------------------------------------------------------
    | Spam Filtering
    |--------------------------------------------------------------------------
    |
    | Max email body size in bytes. Messages larger than this are discarded.
    |
    */
    'max_body_size' => (int) env('TEMP_MAIL_MAX_BODY_SIZE', 1048576), // 1MB

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Number of messages displayed per page in the inbox.
    |
    */
    'messages_per_page' => (int) env('TEMP_MAIL_MESSAGES_PER_PAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    |
    | Number of expired emails to delete per batch during cleanup.
    |
    */
    'cleanup_batch_size' => (int) env('TEMP_MAIL_CLEANUP_BATCH', 200),

];
