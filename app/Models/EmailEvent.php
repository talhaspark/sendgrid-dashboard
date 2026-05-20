<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'sg_message_id',
        'event_type',
        'email_address',
        'event_timestamp',
        'smtp_id',
        'category',
        'sg_event_id',
        'reason',
        'status',
        'response',
        'attempt',
        'url',
        'useragent',
        'ip',
        'raw_payload',
    ];

    protected $casts = [
        'event_timestamp' => 'datetime',
        'raw_payload' => 'array',
        'attempt' => 'integer',
    ];

    /**
     * Get the email this event belongs to.
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Get a badge color based on event type.
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->event_type) {
            'processed' => '#3b82f6',
            'delivered' => '#10b981',
            'open' => '#8b5cf6',
            'click' => '#6366f1',
            'bounce' => '#ef4444',
            'dropped' => '#f97316',
            'deferred' => '#f59e0b',
            'spam_report' => '#dc2626',
            'unsubscribe' => '#6b7280',
            default => '#9ca3af',
        };
    }

    /**
     * Get event icon.
     */
    public function getIconAttribute(): string
    {
        return match($this->event_type) {
            'processed' => 'fa-cog',
            'delivered' => 'fa-check-circle',
            'open' => 'fa-envelope-open',
            'click' => 'fa-mouse-pointer',
            'bounce' => 'fa-exclamation-triangle',
            'dropped' => 'fa-times-circle',
            'deferred' => 'fa-clock',
            'spam_report' => 'fa-shield-alt',
            'unsubscribe' => 'fa-user-minus',
            default => 'fa-info-circle',
        };
    }

    /**
     * Scope: filter by event type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Get event type statistics.
     */
    public static function typeStatistics(): array
    {
        return static::selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();
    }
}
