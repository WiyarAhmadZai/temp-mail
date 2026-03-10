<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Models\Message;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch {--limit=50 : Max emails to process per run}';

    protected $description = 'Fetch incoming emails from IMAP and store them for matching temp addresses';

    public function handle(): int
    {
        $this->info('Connecting to IMAP server...');

        try {
            $client = Client::account('default');
            $client->connect();
        } catch (\Exception $e) {
            $this->error('IMAP connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $folder = $client->getFolder('INBOX');

        if (!$folder) {
            $this->error('Could not open INBOX folder.');
            $client->disconnect();
            return self::FAILURE;
        }

        $this->info('Fetching unseen messages...');

        $messages = $folder->messages()
            ->unseen()
            ->limit((int) $this->option('limit'))
            ->get();

        $stored = 0;
        $skipped = 0;
        $unmatched = 0;

        // Preload all active temp email addresses for fast lookup
        $activeEmails = Email::where('expires_at', '>', now())
            ->pluck('id', 'email')
            ->toArray();

        foreach ($messages as $imapMessage) {
            $messageId = $imapMessage->getMessageId()?->toString();

            // Skip duplicates by IMAP Message-ID
            if ($messageId && Message::where('message_id', $messageId)->exists()) {
                $skipped++;
                continue;
            }

            // Extract recipients (To + CC)
            $recipients = $this->extractRecipients($imapMessage);

            // Match against our temp emails
            $matched = false;
            foreach ($recipients as $recipient) {
                $recipient = strtolower(trim($recipient));

                if (isset($activeEmails[$recipient])) {
                    $emailId = $activeEmails[$recipient];

                    Message::create([
                        'email_id' => $emailId,
                        'message_id' => $messageId,
                        'sender' => $this->extractSender($imapMessage),
                        'subject' => (string) $imapMessage->getSubject(),
                        'body' => $this->extractBody($imapMessage),
                    ]);

                    $stored++;
                    $matched = true;
                    break; // One match is enough
                }
            }

            if (!$matched) {
                $unmatched++;
            }

            // Mark as seen so we don't re-process
            $imapMessage->setFlag('Seen');
        }

        $client->disconnect();

        $this->info("Done. Stored: {$stored} | Skipped (duplicate): {$skipped} | Unmatched: {$unmatched}");

        return self::SUCCESS;
    }

    /**
     * Extract all recipient addresses from To and CC headers.
     */
    private function extractRecipients($message): array
    {
        $recipients = [];

        $to = $message->getTo();
        if ($to) {
            foreach ($to->toArray() as $address) {
                if (isset($address->mail)) {
                    $recipients[] = $address->mail;
                }
            }
        }

        $cc = $message->getCc();
        if ($cc) {
            foreach ($cc->toArray() as $address) {
                if (isset($address->mail)) {
                    $recipients[] = $address->mail;
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
