<?php

namespace App\Jobs;

use App\Models\EmailEvent;
use App\Models\Email;
use App\Services\EmailLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessEmailEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $eventData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmailLogger $logger): void
    {
        try {
            // Try to find the associated email by SendGrid message ID
            $sgMessageId = $this->eventData['sg_message_id'] ?? null;
            $email = null;

            if ($sgMessageId) {
                $email = Email::where('message_id', $sgMessageId)->first();
            }

            EmailEvent::create([
                'email_id' => $email?->id,
                'sg_message_id' => $sgMessageId,
                'event_type' => $this->eventData['event'] ?? 'unknown',
                'email_address' => $this->eventData['email'] ?? null,
                'event_timestamp' => isset($this->eventData['timestamp'])
                    ? \Carbon\Carbon::createFromTimestamp($this->eventData['timestamp'])
                    : now(),
                'smtp_id' => $this->eventData['smtp-id'] ?? null,
                'category' => is_array($this->eventData['category'] ?? null)
                    ? implode(',', $this->eventData['category'])
                    : ($this->eventData['category'] ?? null),
                'sg_event_id' => $this->eventData['sg_event_id'] ?? null,
                'reason' => $this->eventData['reason'] ?? null,
                'status' => $this->eventData['status'] ?? null,
                'response' => $this->eventData['response'] ?? null,
                'attempt' => $this->eventData['attempt'] ?? null,
                'url' => $this->eventData['url'] ?? null,
                'useragent' => $this->eventData['useragent'] ?? null,
                'ip' => $this->eventData['ip'] ?? null,
                'raw_payload' => $this->eventData,
            ]);

            $logger->info('Email event processed', [
                'source' => 'ProcessEmailEvent',
                'event_type' => $this->eventData['event'] ?? 'unknown',
                'email' => $this->eventData['email'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process email event', [
                'error' => $e->getMessage(),
                'event_data' => $this->eventData,
            ]);

            throw $e;
        }
    }
}
