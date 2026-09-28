<?php

namespace App\Jobs;

use App\Models\EmailEvent;
use App\Models\SentEmail;
use App\Services\EmailLogger;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProcessEmailEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;
    
    private const STATUS_ALIASES = [
        'spamreport' => 'spam_report',
        'group_unsubscribe' => 'unsubscribe',
    ];

    public function __construct(
        protected array $eventData
    ) {}

    public function handle(EmailLogger $logger): void
    {
        $sgMessageId = $this->eventData['sg_message_id'] ?? null;
        $eventType = $this->eventData['event'] ?? 'unknown';
        $sgEventId = $this->eventData['sg_event_id'] ?? null;

        $timestamp = isset($this->eventData['timestamp'])
            ? Carbon::createFromTimestamp($this->eventData['timestamp'])
            : now();

        if ($sgEventId && EmailEvent::where('sg_event_id', $sgEventId)->exists()) {
            Log::channel('events')->info('Duplicate SendGrid event skipped', [
                'sg_event_id' => $sgEventId,
                'event_type' => $eventType,
            ]);

            return;
        }

        try {
            DB::transaction(function () use ($sgMessageId, $eventType, $sgEventId, $timestamp, $logger) {
                try {
                    EmailEvent::create([
                        'sg_message_id' => $sgMessageId,
                        'event_type' => $eventType,
                        'email_address' => $this->eventData['email'] ?? null,
                        'event_timestamp' => $timestamp,
                        'smtp_id' => $this->eventData['smtp-id'] ?? null,
                        'category' => is_array($this->eventData['category'] ?? null)
                            ? implode(',', $this->eventData['category'])
                            : ($this->eventData['category'] ?? null),
                        'sg_event_id' => $sgEventId,
                        'reason' => $this->eventData['reason'] ?? null,
                        'status' => $this->eventData['status'] ?? null,
                        'response' => $this->eventData['response'] ?? null,
                        'attempt' => $this->eventData['attempt'] ?? null,
                        'url' => $this->eventData['url'] ?? null,
                        'useragent' => $this->eventData['useragent'] ?? null,
                        'ip' => $this->eventData['ip'] ?? null,
                        'raw_payload' => $this->eventData,
                    ]);
                } catch (QueryException $e) {
              
                    if ($this->isDuplicateKeyError($e)) {
                        Log::channel('events')->info('Duplicate SendGrid event caught at insert', [
                            'sg_event_id' => $sgEventId,
                        ]);

                        return;
                    }

                    throw $e;
                }

                if (! $sgMessageId) {
                    return;
                }

                $sentEmail = SentEmail::where('sg_message_id', $sgMessageId)
                    ->lockForUpdate()
                    ->first();

                if (! $sentEmail) {
                    Log::channel('events')->warning('SentEmail not found for webhook event', [
                        'sg_message_id' => $sgMessageId,
                    ]);

                    return;
                }

                $updates = [];


                if (is_null($sentEmail->sent_at) || $timestamp->lt($sentEmail->sent_at)) {
                    $updates['sent_at'] = $timestamp;
                }

                $isLatestKnownEvent = is_null($sentEmail->last_event_at)
                    || $timestamp->gte($sentEmail->last_event_at);

                $normalizedStatus = self::STATUS_ALIASES[$eventType] ?? $eventType;

                if ($isLatestKnownEvent) {
                    $updates['status'] = $normalizedStatus;
                    $updates['last_event'] = $normalizedStatus;
                    $updates['last_event_at'] = $timestamp;
                }
                switch ($eventType) {
                    case 'open':
                        $updates['opens'] = ($sentEmail->opens ?? 0) + 1;
                        break;

                    case 'click':
                        $updates['clicks'] = ($sentEmail->clicks ?? 0) + 1;
                        break;

                    case 'bounce':
                        $updates['bounces'] = ($sentEmail->bounces ?? 0) + 1;
                        break;

                    case 'dropped':
                    case 'blocked':
                        $updates['blocks'] = ($sentEmail->blocks ?? 0) + 1;
                        break;

                    case 'deferred':
                        $updates['deferred'] = ($sentEmail->deferred ?? 0) + 1;
                        break;

                    case 'spamreport':
                        $updates['spam_reports'] = ($sentEmail->spam_reports ?? 0) + 1;
                        break;

                    case 'unsubscribe':
                    case 'group_unsubscribe':
                        $updates['unsubscribes'] = ($sentEmail->unsubscribes ?? 0) + 1;
                        break;
                }

                if ($isLatestKnownEvent && in_array($eventType, ['bounce', 'dropped', 'blocked', 'deferred'], true)) {
                    $updates['failure_reason'] = $this->eventData['reason'] ?? null;
                    $updates['failure_response'] = $this->eventData['response'] ?? null;
                }

                if ($updates !== []) {
                    $sentEmail->update($updates);
                }

                $logger->info('Email event processed', [
                    'source' => 'ProcessEmailEvent',
                    'event_type' => $eventType,
                    'email' => $this->eventData['email'] ?? null,
                    'msg_id' => $sgMessageId,
                    'applied_as_latest' => $isLatestKnownEvent,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to process email event', [
                'error' => $e->getMessage(),
                'event_data' => $this->eventData,
            ]);

            throw $e; // re-throw so queue retries (tries=3)
        }
    }

    private function isDuplicateKeyError(QueryException $e): bool
    {

        return $e->getCode() === '23000';
    }


    public function failed(\Throwable $exception): void
    {
        Log::channel('events')->critical('ProcessEmailEvent failed permanently', [
            'error' => $exception->getMessage(),
            'event_type' => $this->eventData['event'] ?? 'unknown',
            'email' => $this->eventData['email'] ?? 'unknown',
            'msg_id' => $this->eventData['sg_message_id'] ?? null,
        ]);
    }
}