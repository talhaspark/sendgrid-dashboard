<?php

namespace App\Jobs;
use App\Models\Email;
use App\Models\EmailEvent;
use App\Models\SentEmail;
use App\Services\EmailLogger;
use Carbon\Carbon;
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

    public int $backoff = 10;

    public function __construct(
        protected array $eventData
    ) {}

    public function handle(EmailLogger $logger): void
    {
        try {
            $sgMessageId = $this->eventData['sg_message_id'] ?? null;
            $eventType = $this->eventData['event'] ?? 'unknown';
            $timestamp = isset($this->eventData['timestamp'])
                            ? Carbon::createFromTimestamp($this->eventData['timestamp'])
                            : now();
            $email = null;
            if ($sgMessageId) {
                $email = Email::where('message_id', $sgMessageId)->first();
            }
            EmailEvent::create([
                'email_id' => $email?->id,
                'sg_message_id' => $sgMessageId,
                'event_type' => $eventType,
                'email_address' => $this->eventData['email'] ?? null,
                'event_timestamp' => $timestamp,
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

            // Update sent_emails row with latest event data

            if ($sgMessageId) {

                $sentEmail = SentEmail::where('sg_message_id', $sgMessageId)->first();

                if ($sentEmail) {

                    $updates = [
                        'status' => $eventType,
                    ];

                    switch ($eventType) {

                        case 'open':

                            $updates['opens'] =
                                ($sentEmail->opens ?? 0) + 1;

                            break;

                        case 'click':

                            $updates['clicks'] =
                                ($sentEmail->clicks ?? 0) + 1;

                            break;

                        case 'bounce':

                            $updates['bounces'] =
                                ($sentEmail->bounces ?? 0) + 1;

                            $updates['status'] = 'bounce';

                            break;

                        case 'spamreport':

                            $updates['spam_reports'] =
                                ($sentEmail->spam_reports ?? 0) + 1;

                            break;

                        case 'unsubscribe':

                        case 'group_unsubscribe':

                            $updates['unsubscribes'] =
                                ($sentEmail->unsubscribes ?? 0) + 1;

                            break;

                        case 'delivered':

                            if (! $sentEmail->sent_at) {

                                $updates['sent_at'] = $timestamp;

                            }
                            break;
                    }

                    if (
                        in_array(
                            $eventType,
                            [
                                'bounce',
                                'dropped',
                                'blocked',
                                'deferred',
                            ]
                        )
                    ) {

                        $updates['failure_reason'] =
                            $this->eventData['reason'] ?? null;

                        $updates['failure_response'] =
                            $this->eventData['response'] ?? null;

                        $updates['bounces'] =
                            ($sentEmail->bounces ?? 0) + 1;

                    }

                    $sentEmail->update($updates);

                } else {

                    Log::channel('email')->warning('SentEmail not found for webhook event', [
                        'sg_message_id' => $sgMessageId,
                    ]);
                }

            }

            // ─────────────────────────────────────────────────────────────
            //  Audit log
            // ─────────────────────────────────────────────────────────────
            $logger->info('Email event processed', [
                'source' => 'ProcessEmailEvent',
                'event_type' => $eventType,
                'email' => $this->eventData['email'] ?? null,
                'msg_id' => $sgMessageId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process email event', [
                'error' => $e->getMessage(),
                'event_data' => $this->eventData,
            ]);

            throw $e; // re-throw so queue retries (tries=3)
        }
    }

    /**
     * Handle permanent job failure after all retries exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('email')->critical('ProcessEmailEvent failed permanently', [
            'error' => $exception->getMessage(),
            'event_type' => $this->eventData['event'] ?? 'unknown',
            'email' => $this->eventData['email'] ?? 'unknown',
            'msg_id' => $this->eventData['sg_message_id'] ?? null,
        ]);
    }
}
