<?php

namespace App\Services;

use App\Models\SentEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class SendGridActivityService
{
    private string $baseUrl = 'https://api.sendgrid.com/v3';

    private function apiKey(): string
    {
        return config('services.sendgrid.activity_key')
            ?? config('services.sendgrid.key');
    }

  
    //  ONE-TIME BACKFILL 
    
    public function backfill(int $days = 90, int $chunkDays = 7): array
{
    $totalFetched = 0;
    $totalSaved   = 0;
    $totalUpdated = 0;

    $end    = now();
    $start  = now()->subDays($days);
    $cursor = $start->copy();

    while ($cursor->lt($end)) {
        $chunkEnd = $cursor->copy()->addDays($chunkDays)->min($end);

        $from = $cursor->toISOString();
        $to   = $chunkEnd->toISOString();

        echo "  Chunk: {$cursor->toDateString()} → {$chunkEnd->toDateString()}" . PHP_EOL;

        [$f, $s, $u] = $this->fetchAndStore($from, $to);

        $totalFetched += $f;
        $totalSaved   += $s;
        $totalUpdated += $u;

        echo "    → Fetched: {$f} | Saved: {$s} | Updated: {$u}" . PHP_EOL;

        $cursor = $chunkEnd->addSecond();
    }

    return [$totalFetched, $totalSaved, $totalUpdated];
}


    //  HOURLY / DAILY SYNC (cron)
    
    public function sync(int $hours = 2): array
    {
        $from = now()->subHours($hours)->toISOString();
        $to   = now()->toISOString();

        return $this->fetchAndStore($from, $to);
    }

    //   CORE FETCH + STORE

    private function fetchAndStore(string $from, string $to): array
    {
        $fetched = 0;
        $saved   = 0;
        $updated = 0;
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

                $result = SentEmail::updateOrCreate(
                    ['sg_message_id' => $msg['msg_id']],
                    $this->mapFields($msg)
                );

                $result->wasRecentlyCreated ? $saved++ : $updated++;
            }

        } while ($token && count($messages) > 0);

        return [$fetched, $saved, $updated];
    }

// Mapping fields

    private function mapFields(array $msg): array
    {
        return [
            'sg_message_id' => $msg['msg_id'] ?? null,
            'from_email'    => $msg['from_email'] ?? null,
            'to_email'      => $msg['to_email'] ?? null,
            'subject'       => $msg['subject'] ?? null,
            'status'        => $msg['status'] ?? null,
            'opens'         => $msg['opens_count'] ?? 0,
            'clicks'        => $msg['clicks_count'] ?? 0,
            'bounces'       => $msg['bounces_count'] ?? 0,
            'spam_reports'  => $msg['spam_reports_count'] ?? 0,
            'categories'    => $msg['category'] ?? [],
            'custom_args'   => $msg['unique_args'] ?? [],
            'sent_at'       => isset($msg['last_event_time'])
                                ? Carbon::parse($msg['last_event_time'])
                                : null,
            'raw_payload'   => $msg,
        ];
    }

    //  SENDGRID REQUEST (with retry)
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