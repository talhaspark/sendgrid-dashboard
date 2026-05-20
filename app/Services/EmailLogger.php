<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailLogger
{
    /**
     * Log to the database logs table.
     */
    public function log(string $level, string $message, array $context = [], string $channel = 'app'): void
    {
        try {
            DB::table('logs')->insert([
                'channel' => $channel,
                'level' => $level,
                'message' => $message,
                'context' => !empty($context) ? json_encode($context) : null,
                'source' => $context['source'] ?? null,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fallback to file logging if database is unavailable
            Log::error('Failed to write to database log: ' . $e->getMessage());
        }
    }

    /**
     * Log an audit entry.
     */
    public function audit(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'user_id' => auth()->id(),
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to write audit log: ' . $e->getMessage());
        }
    }

    /**
     * Shortcut methods for different log levels.
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }
}
