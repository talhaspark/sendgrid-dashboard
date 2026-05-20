<?php

namespace App\Jobs;

use App\Models\Email;
use App\Services\AttachmentService;
use App\Services\EmailLogger;
use App\Services\SendGridService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessIncomingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $payload,
        protected array $files = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SendGridService $sendGridService, AttachmentService $attachmentService, EmailLogger $logger): void
    {
        try {
            // Parse and store the email
            $email = $sendGridService->parseIncomingEmail($this->payload);

            // Process attachments if any
            if (!empty($this->files)) {
                $attachmentService->processAttachments($email, $this->files);
            }

            // Update status to processed
            $email->update(['status' => 'processed']);

            $logger->info('Incoming email processed successfully', [
                'source' => 'ProcessIncomingEmail',
                'email_id' => $email->id,
                'from' => $email->from_address,
                'subject' => $email->subject,
            ]);

            $logger->audit(
                'email.received',
                'Email',
                $email->id,
                null,
                ['from' => $email->from_address, 'subject' => $email->subject],
                "Email received from {$email->from_address}"
            );

        } catch (\Exception $e) {
            Log::channel('email')->error('Failed to process incoming email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('email')->critical('ProcessIncomingEmail job failed permanently', [
            'error' => $exception->getMessage(),
            'payload_from' => $this->payload['from'] ?? 'unknown',
        ]);
    }
}
