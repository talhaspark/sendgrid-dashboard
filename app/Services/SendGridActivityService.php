<?php

namespace App\Services;

use App\Models\EmailEvent;
use App\Models\SentEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendGridActivityService
{
    private string $baseUrl = 'https://api.sendgrid.com/v3';

    private const STATUS_ALIASES = [
        'spamreport' => 'spam_report',
        'group_unsubscribe' => 'unsubscribe',
    ];

    private function apiKey(): string
    {
        return config('services.sendgrid.activity_key')
            ?? config('services.sendgrid.key');
    }


    //  ONE-TIME BACKFILL

    public function backfill(int $days = 90, int $chunkDays = 7, bool $fetchEventHistory = true): array
    {
        $totalFetched = 0;
        $totalSaved   = 0;
        $totalUpdated = 0;
        $totalEvents  = 0;

        $end    = now();
        $start  = now()->subDays($days);
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $chunkEnd = $cursor->copy()->addDays($chunkDays)->min($end);

            $from = $cursor->toISOString();
            $to   = $chunkEnd->toISOString();

            echo "  Chunk: {$cursor->toDateString()} → {$chunkEnd->toDateString()}" . PHP_EOL;

            [$f, $s, $u, $ev] = $this->fetchAndStore($from, $to, $fetchEventHistory);

            $totalFetched += $f;
            $totalSaved   += $s;
            $totalUpdated += $u;
            $totalEvents  += $ev;

            echo "    → Fetched: {$f} | Saved: {$s} | Updated: {$u} | New events: {$ev}" . PHP_EOL;

            $cursor = $chunkEnd->addSecond();
        }

        return [$totalFetched, $totalSaved, $totalUpdated, $totalEvents];
    }


    //  HOURLY / DAILY SYNC (cron)

    public function sync(int $hours = 2, bool $fetchEventHistory = false): array
    {
        $from = now()->subHours($hours)->toISOString();
        $to   = now()->toISOString();

        return $this->fetchAndStore($from, $to, $fetchEventHistory);
    }

    //   CORE FETCH + STORE

    private function fetchAndStore(string $from, string $to, bool $fetchEventHistory = true): array
    {
        $fetched = 0;
        $saved   = 0;
        $updated = 0;
        $newEvents = 0;
        $token   = null;

        do {
            $params = [
                'limit' => 1000,
                'query' => "last_event_time BETWEEN TIMESTAMP \"{$from}\" AND TIMESTAMP \"{$to}\"",
            ];

            if ($token) {
                $params['token'] = $token;
            }

            $response = $this->request('GET', '/messages', $params);

            $data     = $response->json();
            $messages = $data['messages'] ?? [];
            $token    = $data['next_page_token'] ?? null;

            foreach ($messages as $msg) {
                $fetched++;

                if (empty($msg['msg_id'])) {
                    continue;
                }

                $existing = SentEmail::where('sg_message_id', $msg['msg_id'])->first();

                $updates = $this->mapFields($msg, $existing);

                if ($existing) {
                    $existing->update($updates);
                    $updated++;
                } else {
                    SentEmail::create($updates + ['sg_message_id' => $msg['msg_id']]);
                    $saved++;
                }

 
                if ($fetchEventHistory && ! EmailEvent::where('sg_message_id', $msg['msg_id'])->exists()) {
                    $newEvents += $this->syncEventHistory($msg['msg_id']);
                }
            }

        } while ($token && count($messages) > 0);

        return [$fetched, $saved, $updated, $newEvents];
    }

    // Mapping fields

    /**
     * @param array $msg SendGrid /messages search result for one message.
     * @param SentEmail|null $existing The current row, if one exists, used
     *                                 to protect sent_at and apply
     *                                 timestamp-ordered status updates.
     */
    private function mapFields(array $msg, ?SentEmail $existing = null): array
    {
        $normalizedStatus = isset($msg['status'])
            ? (self::STATUS_ALIASES[$msg['status']] ?? $msg['status'])
            : null;

        $lastEventAt = isset($msg['last_event_time'])
            ? Carbon::parse($msg['last_event_time'])
            : null;

        $fields = [
            'from_email'    => $msg['from_email'] ?? null,
            'to_email'      => $msg['to_email'] ?? null,
            'subject'       => $msg['subject'] ?? null,
            'opens'         => $msg['opens_count'] ?? 0,
            'clicks'        => $msg['clicks_count'] ?? 0,
            'bounces'       => $msg['bounces_count'] ?? 0,
            'spam_reports'  => $msg['spam_reports_count'] ?? 0,
            'categories'    => $msg['category'] ?? [],
            'custom_args'   => $msg['unique_args'] ?? [],
            'raw_payload'   => $msg,
        ];


        $isLatestKnown = ! $existing
            || ! $existing->last_event_at
            || ! $lastEventAt
            || $lastEventAt->gte($existing->last_event_at);

        if ($isLatestKnown) {
            $fields['status'] = $normalizedStatus;
            $fields['last_event'] = $normalizedStatus;
            $fields['last_event_at'] = $lastEventAt;
        }

       
        if ($existing && $existing->sent_at) {
            $fields['sent_at'] = $existing->sent_at;
        }

        return $fields;
    }


    private function syncEventHistory(string $msgId): int
    {
        try {
            $response = $this->request('GET', '/messages/' . $msgId);
        } catch (\Exception $e) {
            Log::channel('events')->warning('Failed to fetch message event history', [
                'msg_id' => $msgId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }

        $data = $response->json();
        $events = $data['events'] ?? [];

        if (empty($events)) {
            return 0;
        }

        $earliestTimestamp = null;
        $created = 0;

        foreach ($events as $event) {
            $eventType = $event['event_name'] ?? null;
            $timestampRaw = $event['processed'] ?? null; 

            if (! $eventType || ! $timestampRaw) {
                continue;
            }

            try {
                $timestamp = Carbon::parse($timestampRaw);
            } catch (\Exception $e) {
                continue;
            }

            if (! $earliestTimestamp || $timestamp->lt($earliestTimestamp)) {
                $earliestTimestamp = $timestamp;
            }

            $alreadyStored = EmailEvent::where('sg_message_id', $msgId)
                ->where('event_type', $eventType)
                ->where('event_timestamp', $timestamp)
                ->exists();

            if ($alreadyStored) {
                continue;
            }

            EmailEvent::create([
                'sg_message_id' => $msgId,
                'event_type' => $eventType,
                'event_timestamp' => $timestamp,
                'reason' => $event['reason'] ?? null,
                'response' => $event['response'] ?? $event['mx_server'] ?? null,
                'useragent' => $event['http_user_agent'] ?? $event['useragent'] ?? null,
                'url' => $event['url'] ?? null,
                'ip' => $event['ip'] ?? null,
                'raw_payload' => $event,
            ]);

            $created++;
        }

        if ($earliestTimestamp) {
            $sentEmail = SentEmail::where('sg_message_id', $msgId)->first();

            if ($sentEmail && (! $sentEmail->sent_at || $earliestTimestamp->lt($sentEmail->sent_at))) {
                $sentEmail->update(['sent_at' => $earliestTimestamp]);
            }
        }

        return $created;
    }


    private function request(string $method, string $path, array $params = [], int $attempt = 1)
    {
        $response = Http::withToken($this->apiKey())
            ->timeout(30)
            ->acceptJson()
            ->get($this->baseUrl . $path, $params);

        if ($response->status() === 429) {
            if ($attempt >= 5) {
                throw new \Exception("SendGrid rate limit exceeded.");
            }

            sleep(2 ** $attempt);
            return $this->request($method, $path, $params, $attempt + 1);
        }

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response;
    }
}