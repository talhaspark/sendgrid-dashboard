<?php

use App\Http\Controllers\EmailWebhookController;
use App\Http\Controllers\SendGridEventWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// SendGrid Webhooks (no CSRF, no auth)
Route::post('/webhooks/incoming-emails', [EmailWebhookController::class, 'handleIncoming'])
    ->name('webhooks.incoming-emails');

Route::post('/webhooks/sendgrid-events', [SendGridEventWebhookController::class, 'handle'])
    ->name('webhooks.sendgrid-events');

// API endpoints
Route::prefix('v1')->group(function () {
    Route::get('/emails', function (\Illuminate\Http\Request $request) {
        $emails = \App\Models\Email::query()
            ->when($request->search, fn($q) => $q->search($request->search))
            ->orderBy('received_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json($emails);
    });

    Route::get('/emails/{email}', function (\App\Models\Email $email) {
        $email->load(['attachments', 'events']);
        return response()->json($email);
    });

    Route::get('/stats', function () {
        return response()->json([
            'emails' => \App\Models\Email::statistics(),
            'events' => \App\Models\EmailEvent::typeStatistics(),
        ]);
    });
});
