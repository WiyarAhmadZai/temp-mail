<?php

namespace App\Console\Commands;

use App\Jobs\ProcessIncomingEmail;
use App\Models\Email;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch {--limit= : Max emails to process per run} {--sync : Process synchronously instead of queuing}';

    protected $description = 'Fetch incoming emails from IMAP and store them for matching temp addresses';

    private function log(): \Psr\Log\LoggerInterface
    {
        return Log::channel('tempmail');
    }

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?: config('tempmail.fetch_limit'));
        $sync = $this->option('sync');
        $domain = config('tempmail.domain');
        $maxBodySize = config('tempmail.max_body_size');

        $this->log()->info('Starting email fetch', [
            'limit' => $limit,
            'domain' => $domain,
            'mode' => $sync ? 'sync' : 'queue',
        ]);

        try {
            $client = Client::account('default');
            $client->connect();
        } catch (\Exception $e) {
            $this->log()->error('IMAP connection failed', ['error' => $e->getMessage()]);
            $this->error('IMAP connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $folder = $client->getFolder('INBOX');

        if (!$folder) {
            $this->log()->error('Could not open INBOX folder');
            $this->error('Could not open INBOX folder.');
            $client->disconnect();
            return self::FAILURE;
        }

        $messages = $folder->messages()
            ->unseen()
            ->limit($limit)
            ->get();

        $stats = [
            'stored' => 0,
            'queued' => 0,
            'skipped_duplicate' => 0,
            'skipped_no_id' => 0,
            'skipped_too_large' => 0,
            'wrong_domain' => 0,
            'unmatched' => 0,
        ];

        // Preload active temp emails for O(1) lookup (sync mode only)
        $activeEmails = $sync
            ? Email::where('expires_at', '>', now())->pluck('id', 'email')->toArray()
            : [];

        foreach ($messages as $imapMessage) {
            $messageId = $imapMessage->getMessageId()?->toString();

            // Spam filter: reject emails without a Message-ID
            if (empty($messageId)) {
                $stats['skipped_no_id']++;
                $this->log()->debug('Skipped: no Message-ID header', [
                    'sender' => $this->extractSender($imapMessage),
                ]);
                $imapMessage->setFlag('Seen');
                continue;
            }

            // Dedup check
            if (Message::where('message_id', $messageId)->exists()) {
                $stats['skipped_duplicate']++;
                $imapMessage->setFlag('Seen');
                continue;
            }

            // Extract and filter recipients by domain
            $recipients = $this->extractRecipients($imapMessage);
            $ourRecipients = array_filter($recipients, fn ($addr) => Email::belongsToDomain($addr));

            if (empty($ourRecipients)) {
                $stats['wrong_domain']++;
                $this->log()->debug('Ignored: foreign domain', [
                    'recipients' => $recipients,
                    'sender' => $this->extractSender($imapMessage),
                ]);
                $imapMessage->setFlag('Seen');
                continue;
            }

            // Extract body and check size
            $body = $this->extractBody($imapMessage);
            if (strlen($body) > $maxBodySize) {
                $stats['skipped_too_large']++;
                $this->log()->warning('Skipped: body too large', [
                    'size' => strlen($body),
                    'max' => $maxBodySize,
                    'sender' => $this->extractSender($imapMessage),
                ]);
                $imapMessage->setFlag('Seen');
                continue;
            }

            $sender = $this->extractSender($imapMessage);
            $subject = (string) $imapMessage->getSubject();
            $recipient = reset($ourRecipients);

            if ($sync) {
                // Synchronous mode: insert directly
                $normalized = Email::normalizeEmail($recipient);

                if (isset($activeEmails[$normalized])) {
                    Message::create([
                        'email_id' => $activeEmails[$normalized],
                        'message_id' => $messageId,
                        'sender' => $sender,
                        'subject' => $subject,
                        'body' => $body,
                    ]);
                    $stats['stored']++;
                } else {
                    $stats['unmatched']++;
                    $this->log()->debug('No active temp email matched', ['recipient' => $normalized]);
                }
            } else {
                // Queue mode: dispatch job for async processing
                ProcessIncomingEmail::dispatch($recipient, $sender, $subject, $body, $messageId);
                $stats['queued']++;
            }

            $imapMessage->setFlag('Seen');
        }

        $client->disconnect();

        $this->log()->info('Fetch complete', $stats);
        $this->info(
            "Stored: {$stats['stored']} | Queued: {$stats['queued']} | "
            . "Duplicates: {$stats['skipped_duplicate']} | No ID: {$stats['skipped_no_id']} | "
            . "Too large: {$stats['skipped_too_large']} | Wrong domain: {$stats['wrong_domain']} | "
            . "Unmatched: {$stats['unmatched']}"
        );

        return self::SUCCESS;
    }

    private function extractRecipients($message): array
    {
        $recipients = [];

        foreach (['getTo', 'getCc'] as $method) {
            $header = $message->$method();
            if ($header) {
                foreach ($header->toArray() as $address) {
                    if (isset($address->mail)) {
                        $recipients[] = Email::normalizeEmail($address->mail);
                    }
                }
            }
        }

        return $recipients;
    }

    private function extractSender($message): string
    {
        $from = $message->getFrom();
        if ($from) {
            $addresses = $from->toArray();
            if (!empty($addresses)) {
                return $addresses[0]->mail ?? 'unknown@unknown';
            }
        }

        return 'unknown@unknown';
    }

    private function extractBody($message): string
    {
        $textBody = $message->getTextBody();
        if (!empty($textBody)) {
            return $textBody;
        }

        $htmlBody = $message->getHTMLBody();
        if (!empty($htmlBody)) {
            return $htmlBody;
        }

        return '(no content)';
    }
}
