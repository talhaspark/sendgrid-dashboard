<?php

namespace Database\Seeders;

use App\Models\Email;
use App\Models\EmailAttachment;
use App\Models\EmailEvent;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Seeding emails...');

        // Create 80 normal emails
        $emails = Email::factory()->count(80)->create();

        // Create 10 unread emails
        $unreadEmails = Email::factory()->unread()->count(10)->create();

        // Create 5 starred emails
        Email::factory()->starred()->count(5)->create();

        // Create 5 spam emails
        Email::factory()->spam()->count(5)->create();

        $this->command->info('Seeding attachments...');

        // Add attachments to ~30% of emails
        $allEmails = $emails->merge($unreadEmails);
        $emailsWithAttachments = $allEmails->random(min(25, $allEmails->count()));

        foreach ($emailsWithAttachments as $email) {
            $count = rand(1, 3);
            EmailAttachment::factory()
                ->count($count)
                ->create(['email_id' => $email->id]);

            $email->update(['attachment_count' => $count]);
        }

        $this->command->info('Seeding events...');

        // Add events to emails
        foreach ($allEmails->take(50) as $email) {
            // Each email gets 1-4 events
            $eventCount = rand(1, 4);
            EmailEvent::factory()
                ->count($eventCount)
                ->create(['email_id' => $email->id]);
        }

        // Create some orphan events (events without associated emails)
        EmailEvent::factory()->count(20)->create(['email_id' => null]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('  - Emails: ' . Email::count());
        $this->command->info('  - Attachments: ' . EmailAttachment::count());
        $this->command->info('  - Events: ' . EmailEvent::count());
    }
}
