<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['email_id', 'sender', 'subject', 'body'];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }
}
