<?php

namespace App\Jobs;

use App\Models\Email;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessIncomingEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private string $recipient,
        private string $sender,
        private string $subject,
        private string $body,
        private ?string $messageId,
    ) {}

    public function handle(): void
    {
        $log = Log::channel('tempmail');

        // Dedup check
        if ($this->messageId && Message::where('message_id', $this->messageId)->exists()) {
            $log->debug('Queue: skipped duplicate', ['message_id' => $this->messageId]);
            return;
        }

        $normalized = Email::normalizeEmail($this->recipient);
        $email = Email::where('email', $normalized)
            ->where('expires_at', '>', now())
            ->first();

        if (!$email) {
            $log->debug('Queue: no active email found', ['recipient' => $normalized]);
            return;
        }

        Message::create([
            'email_id' => $email->id,
            'message_id' => $this->messageId,
            'sender' => $this->sender,
            'subject' => $this->subject,
            'body' => $this->body,
        ]);

        // Invalidate poll cache so new message shows immediately
        Cache::forget("inbox:{$email->id}");

        $log->info('Queue: stored message', [
            'to' => $normalized,
            'from' => $this->sender,
            'subject' => $this->subject,
        ]);
    }
}
