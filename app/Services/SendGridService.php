<?php

namespace App\Services;

use App\Models\Email;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    /**
     * Parse incoming email data from SendGrid Inbound Parse webhook.
     *
     * @param array $payload The raw POST data from SendGrid
     * @return Email The created email record
     */
    public function parseIncomingEmail(array $payload): Email
    {
        $toAddresses = $this->parseAddresses($payload['to'] ?? '');
        $ccAddresses = $this->parseAddresses($payload['cc'] ?? '');

        // Parse envelope for additional routing info
        $envelope = null;
        if (!empty($payload['envelope'])) {
            $envelope = is_string($payload['envelope'])
                ? json_decode($payload['envelope'], true)
                : $payload['envelope'];
        }

        // Parse headers
        $headers = null;
        if (!empty($payload['headers'])) {
            $headers = $this->parseHeaders($payload['headers']);
        }

        $email = Email::create([
            'message_id' => $headers['Message-ID'] ?? null,
            'from_address' => $this->extractEmail($payload['from'] ?? ''),
            'from_name' => $this->extractName($payload['from'] ?? ''),
            'to_addresses' => $toAddresses,
            'cc_addresses' => !empty($ccAddresses) ? $ccAddresses : null,
            'subject' => $payload['subject'] ?? '(No Subject)',
            'text_body' => $payload['text'] ?? null,
            'html_body' => $payload['html'] ?? null,
            'headers' => $headers,
            'sender_ip' => $payload['sender_ip'] ?? null,
            'spam_score' => floatval($payload['spam_score'] ?? 0),
            'spam_report' => $payload['spam_report'] ?? null,
            'attachment_count' => intval($payload['attachments'] ?? 0),
            'is_spam' => floatval($payload['spam_score'] ?? 0) > 5.0,
            'status' => 'received',
            'raw_payload' => $payload,
            'envelope' => !empty($envelope) ? json_encode($envelope) : null,
            'received_at' => now(),
        ]);

        Log::channel('email')->info('Email parsed from SendGrid webhook', [
            'email_id' => $email->id,
            'from' => $email->from_address,
            'subject' => $email->subject,
        ]);

        return $email;
    }

    /**
     * Verify SendGrid webhook signature.
     */
    public function verifyWebhookSignature(string $signature, string $timestamp, string $token): bool
    {
        $webhookKey = config('services.sendgrid.webhook_key');

        if (empty($webhookKey)) {
            // If no key configured, skip verification in development
            return config('app.env') === 'local';
        }

        $payload = $timestamp . $token;
        $expectedSignature = hash_hmac('sha256', $payload, $webhookKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Parse a comma-separated address string into an array of email addresses.
     */
    protected function parseAddresses(string $addressString): array
    {
        if (empty($addressString)) {
            return [];
        }

        $addresses = [];
        // Handle format: "Name <email@example.com>, Name2 <email2@example.com>"
        preg_match_all('/[\w\.\-\+]+@[\w\.\-]+\.\w+/', $addressString, $matches);

        if (!empty($matches[0])) {
            $addresses = array_unique($matches[0]);
        }

        return array_values($addresses);
    }

    /**
     * Extract email address from a "Name <email>" string.
     */
    protected function extractEmail(string $from): string
    {
        if (preg_match('/<(.+?)>/', $from, $matches)) {
            return $matches[1];
        }

        // Could be just an email address
        if (filter_var(trim($from), FILTER_VALIDATE_EMAIL)) {
            return trim($from);
        }

        return $from;
    }

    /**
     * Extract display name from a "Name <email>" string.
     */
    protected function extractName(string $from): ?string
    {
        if (preg_match('/^(.+?)\s*</', $from, $matches)) {
            return trim($matches[1], ' "\'');
        }

        return null;
    }

    /**
     * Parse raw email headers into an associative array.
     */
    protected function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = explode("\n", $rawHeaders);
        $currentKey = null;

        foreach ($lines as $line) {
            if (preg_match('/^([\w\-]+):\s*(.*)$/', $line, $matches)) {
                $currentKey = $matches[1];
                $headers[$currentKey] = trim($matches[2]);
            } elseif ($currentKey && preg_match('/^\s+(.*)$/', $line, $matches)) {
                // Continuation of previous header
                $headers[$currentKey] .= ' ' . trim($matches[1]);
            }
        }

        return $headers;
    }
}
