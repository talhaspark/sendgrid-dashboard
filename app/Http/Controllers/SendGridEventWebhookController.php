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
        Log::channel('events')->info('SendGrid event webhook received', [
            'event_count' => is_array($request->all()) ? count($request->all()) : 1,
        ]);

        try {
            $events = $request->all();

            // SendGrid sends events as an array
            if (!is_array($events) || empty($events)) {
                return response()->json(['success' => false, 'error' => 'No events provided.'], 400);
            }

            // Handle both single event and batch of events
            // If the first key is numeric, it's an array of events
            if (isset($events[0])) {
                foreach ($events as $eventData) {
                    if (is_array($eventData)) {
                        ProcessEmailEvent::dispatch($eventData);
                    }
                }
            } else {
                // Single event
                ProcessEmailEvent::dispatch($events);
            }

            return response()->json(['success' => true, 'message' => 'Events queued for processing.']);

        } catch (\Exception $e) {
            Log::channel('events')->error('Failed to process SendGrid event webhook', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'error' => 'Internal server error.'], 500);
        }
    }
}
