<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessIncomingEmail;
use App\Services\SendGridService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailWebhookController extends Controller
{
    public function __construct(
        protected SendGridService $sendGridService
    ) {}

    /**
     * Handle incoming email from SendGrid Inbound Parse.
     * POST /api/webhooks/incoming-emails
     */
    public function handleIncoming(Request $request): JsonResponse
    {
        Log::channel('webhook')->info('Incoming email webhook received', [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'subject' => $request->input('subject'),
        ]);

        try {
            $payload = $request->except(['_token']);

            // Collect uploaded files
            $files = [];
            $attachmentCount = intval($request->input('attachments', 0));
            for ($i = 1; $i <= $attachmentCount; $i++) {
                if ($request->hasFile("attachment{$i}")) {
                    $files[] = $request->file("attachment{$i}");
                }
            }
            // Also check for 'attachment' (singular)
            if ($request->hasFile('attachment')) {
                $files[] = $request->file('attachment');
            }

            // Dispatch job for processing
            ProcessIncomingEmail::dispatch($payload, $files);

            return response()->json(['success' => true, 'message' => 'Email queued for processing.']);

        } catch (\Exception $e) {
            Log::channel('webhook')->error('Failed to process incoming email webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'error' => 'Internal server error.'], 500);
        }
    }
}
