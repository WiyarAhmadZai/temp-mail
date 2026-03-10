<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Email extends Model
{
    public $timestamps = false;

    protected $fillable = ['email', 'token', 'created_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function generateUniqueEmail(string $domain = 'tempmail.local'): self
    {
        do {
            $local = Str::lower(Str::random(8));
            $address = "{$local}@{$domain}";
        } while (self::where('email', $address)->exists());

        return self::create([
            'email' => $address,
            'token' => Str::random(64),
            'created_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);
    }
}
