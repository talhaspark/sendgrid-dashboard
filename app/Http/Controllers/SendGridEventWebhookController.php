<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEmailEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridEventWebhookController extends Controller
{
    /**
     * Handle SendGrid Event Webhook (opens, clicks, bounces, etc.)
     * POST /api/webhooks/sendgrid-events
     */
    public function handle(Request $request): JsonResponse
    {
        // Respond 200 FIRST — before any processing
        // This tells SendGrid "received, all good" so it never retries
        $response = response()->json([
            'success' => true,
            'message' => 'Events received.',
        ]);

        try {
            $events = $request->all();

            if (empty($events) || !is_array($events)) {
                Log::channel('events')->warning('SendGrid webhook received empty or invalid payload');
                return $response;
            }

            // SendGrid always sends an array of events
            // even if only one event occurred
            $eventArray = isset($events[0]) ? $events : [$events];
            $count      = 0;

            foreach ($eventArray as $eventData) {
                if (!is_array($eventData)) {
                    continue;
                }

                ProcessEmailEvent::dispatch($eventData);
                $count++;
            }

            Log::channel('events')->info('SendGrid webhook events queued', [
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            // Log the error but still return 200
            // If we return 500, SendGrid retries and we get duplicates
            Log::channel('events')->error('Failed to queue SendGrid webhook events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $response;
    }
}
