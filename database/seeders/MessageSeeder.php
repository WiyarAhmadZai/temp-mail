<?php

namespace Database\Seeders;

use App\Models\Email;
use App\Models\Message;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first email or create one
        $email = Email::first() ?? Email::generateUniqueEmail();

        $testMessages = [
            [
                'sender' => 'welcome@example.com',
                'subject' => 'Welcome to TempMail!',
                'body' => "Hello!\n\nThank you for using TempMail. This is your temporary inbox.\n\nYour email address is active for 24 hours. Any messages sent to this address will appear here.\n\nEnjoy!",
            ],
            [
                'sender' => 'noreply@github.com',
                'subject' => 'Verify your email address',
                'body' => "Hey there,\n\nPlease verify your email address by clicking the link below:\n\nhttps://example.com/verify?token=abc123\n\nIf you did not sign up, you can ignore this email.\n\nThanks,\nThe GitHub Team",
            ],
            [
                'sender' => 'newsletter@techblog.io',
                'subject' => 'This Week in Tech - March 2026',
                'body' => "Weekly Tech Roundup\n\nHere are the top stories this week:\n\n1. Laravel 12 released with major performance improvements\n2. PHP 8.4 brings new features for developers\n3. AI-powered code assistants become mainstream\n\nRead more at techblog.io",
            ],
        ];

        foreach ($testMessages as $msg) {
            Message::create([
                'email_id' => $email->id,
                'sender' => $msg['sender'],
                'subject' => $msg['subject'],
                'body' => $msg['body'],
            ]);
        }

        $this->command->info("Seeded 3 messages for: {$email->email}");
    }
}
