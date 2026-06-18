<?php

namespace App\Console\Commands;

use App\Services\SendGridActivityService;
use Illuminate\Console\Command;

class SyncSendGridEmails extends Command
{
    protected $signature   = 'sendgrid:sync {--hours=2}'; 
    protected $description = 'Sync SendGrid email activity';

    public function handle(SendGridActivityService $service): void
    {
        $hours = (int) $this->option('hours');  

        $this->info("Syncing last {$hours} hour(s)...");

        [$fetched, $saved, $updated] = $service->sync($hours);

        $this->info("Fetched: {$fetched} | Saved: {$saved} | Updated: {$updated}");
    }
}