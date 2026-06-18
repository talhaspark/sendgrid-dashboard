<?php

namespace App\Console\Commands;

use App\Services\SendGridActivityService;
use Illuminate\Console\Command;

class BackfillSendGridEmails extends Command
{
    protected $signature   = 'sendgrid:backfill {--days=90}';
    protected $description = 'Backfill ALL SendGrid email history in chunks';

    public function handle(SendGridActivityService $service): void
    {
        $days = (int) $this->option('days');

        $this->info("Starting backfill for last {$days} days in 7-day chunks...");

        [$fetched, $saved, $updated] = $service->backfill($days);

        $this->newLine();
        $this->info("COMPLETE — Fetched: {$fetched} | Saved: {$saved} | Updated: {$updated}");
    }
}