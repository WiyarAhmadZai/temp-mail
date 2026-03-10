<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch {--limit= : Max emails to process per run}';

    protected $description = 'Fetch incoming emails from IMAP and store them for matching temp addresses';

    private function log(): \Psr\Log\LoggerInterface
    {
        return Log::channel('tempmail');
    }

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?: config('tempmail.fetch_limit'));
        $domain = config('tempmail.domain');

        $this->log()->info('Starting email fetch', ['limit' => $limit, 'domain' => $domain]);

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

        $stats = ['stored' => 0, 'skipped' => 0, 'unmatched' => 0, 'wrong_domain' => 0];

        // Preload all active temp email addresses for O(1) lookup
        $activeEmails = Email::where('expires_at', '>', now())
            ->pluck('id', 'email')
            ->toArray();

        foreach ($messages as $imapMessage) {
            $messageId = $imapMessage->getMessageId()?->toString();

            // Skip duplicates by IMAP Message-ID
            if ($messageId && Message::where('message_id', $messageId)->exists()) {
                $stats['skipped']++;
                continue;
            }

            // Extract and filter recipients
            $recipients = $this->extractRecipients($imapMessage);
            $ourRecipients = array_filter($recipients, fn ($addr) => Email::belongsToDomain($addr));

            if (empty($ourRecipients)) {
                $stats['wrong_domain']++;
                $this->log()->debug('Ignored email for foreign domain', [
                    'recipients' => $recipients,
                    'sender' => $this->extractSender($imapMessage),
                ]);
                $imapMessage->setFlag('Seen');
                continue;
            }

            // Match against our active temp emails
            $matched = false;
            foreach ($ourRecipients as $recipient) {
                $normalized = Email::normalizeEmail($recipient);

                if (isset($activeEmails[$normalized])) {
                    Message::create([
                        'email_id' => $activeEmails[$normalized],
                        'message_id' => $messageId,
                        'sender' => $this->extractSender($imapMessage),
                        'subject' => (string) $imapMessage->getSubject(),
                        'body' => $this->extractBody($imapMessage),
                    ]);

                    $stats['stored']++;
                    $matched = true;

                    $this->log()->info('Stored message', [
                        'to' => $normalized,
                        'from' => $this->extractSender($imapMessage),
                        'subject' => (string) $imapMessage->getSubject(),
                    ]);

                    break;
                }
            }

            if (!$matched) {
                $stats['unmatched']++;
                $this->log()->debug('No active temp email matched', [
                    'recipients' => $ourRecipients,
                ]);
            }

            $imapMessage->setFlag('Seen');
        }

        $client->disconnect();

        $summary = "Stored: {$stats['stored']} | Duplicates: {$stats['skipped']} | Unmatched: {$stats['unmatched']} | Wrong domain: {$stats['wrong_domain']}";
        $this->log()->info('Fetch complete', $stats);
        $this->info($summary);

        return self::SUCCESS;
    }

    /**
     * Extract all recipient addresses from To and CC headers.
     */
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

    /**
     * Extract the sender's email address.
     */
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

    /**
     * Extract the message body, preferring text over HTML.
     */
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
