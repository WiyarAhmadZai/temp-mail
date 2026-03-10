<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Generate a unique temporary email using the configured domain.
     */
    public static function generateUniqueEmail(): self
    {
        $domain = config('tempmail.domain');
        $hours = config('tempmail.expiration_hours');

        do {
            $local = Str::lower(Str::random(8));
            $address = "{$local}@{$domain}";
        } while (self::where('email', $address)->exists());

        return self::create([
            'email' => $address,
            'token' => Str::random(64),
            'created_at' => now(),
            'expires_at' => now()->addHours($hours),
        ]);
    }

    /**
     * Normalize an email address for consistent matching.
     * Lowercases, trims whitespace, and strips angle brackets.
     */
    public static function normalizeEmail(string $email): string
    {
        $email = trim($email);
        $email = trim($email, '<>');
        $email = strtolower($email);

        return $email;
    }

    /**
     * Check if an email address belongs to our configured domain.
     */
    public static function belongsToDomain(string $email): bool
    {
        $domain = strtolower(config('tempmail.domain'));
        $parts = explode('@', self::normalizeEmail($email));

        return count($parts) === 2 && $parts[1] === $domain;
    }
}
