<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentEmail extends Model
{
    protected $fillable = [
        'sg_message_id',
        'from_email',
        'to_email',
        'subject',
        'status',
        'sent_at',
        'last_event',
        'last_event_at',
        'opens',
        'clicks',
        'bounces',
        'blocks',
        'deferred',
        'spam_reports',
        'unsubscribes',
        'categories',
        'custom_args',
        'raw_payload',
        'failure_reason',
        'failure_response',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'last_event_at' => 'datetime',
        'categories' => 'array',
        'custom_args' => 'array',
        'raw_payload' => 'array',
        'opens' => 'integer',
        'clicks' => 'integer',
        'bounces' => 'integer',
        'blocks' => 'integer',
        'deferred' => 'integer',
        'spam_reports' => 'integer',
        'unsubscribes' => 'integer',
    ];

    // Relationship

    public function events()
    {
        return $this->hasMany(EmailEvent::class, 'sg_message_id', 'sg_message_id')
            ->orderBy('event_timestamp');
    }

    // Scopes

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('subject', 'like', "%{$term}%")
                ->orWhere('to_email', 'like', "%{$term}%");
        });
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

  
    public function scopeSortBySentAt($query, string $direction = 'desc')
    {
        return $query->orderBy('sent_at', $direction);
    }


    public function scopeSortByActivity($query, string $direction = 'desc')
    {
        return $query->orderBy('last_event_at', $direction);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'processed' => 'Queued',
            'delivered' => 'Delivered',
            'open' => 'Opened',
            'click' => 'Clicked',
            'bounce' => 'Bounced',
            'dropped' => 'Dropped',
            'deferred' => 'Deferred',
            'spam_report' => 'Spam',
            'blocked' => 'Blocked',
            'unsubscribe' => 'Unsubscribed',
            default => ucfirst($this->status ?? 'Unknown'),
        };
    }

 
    public function getBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'delivered' => 'badge-success',
            'open' => 'badge-info',
            'click' => 'badge-primary',
            'bounce' => 'badge-danger',
            'dropped' => 'badge-warning',
            'deferred' => 'badge-warning',
            'spam_report' => 'badge-danger',
            'blocked' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function getCategoriesStringAttribute(): string
    {
        return implode(', ', $this->categories ?? []);
    }

 
    public function getHasRecentActivityAttribute(): bool
    {
        if (! $this->last_event_at || ! $this->sent_at) {
            return false;
        }

        return ! $this->last_event_at->equalTo($this->sent_at);
    }

    // Statistics — used on dashboard/analytics pages

    public static function statistics(): array
    {
        return [
            'total' => static::count(),
            'delivered' => static::where('status', 'delivered')->count(),
            'opened' => static::where('opens', '>', 0)->count(),
            'clicked' => static::where('clicks', '>', 0)->count(),
            'bounced' => static::where('status', 'bounce')->count(),
            'spam' => static::where('status', 'spam_report')->count(),
            'today' => static::whereDate('sent_at', today())->count(),
            'this_week' => static::whereBetween('sent_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
        ];
    }
}