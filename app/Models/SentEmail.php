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
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'categories'   => 'array',
        'custom_args'  => 'array',
        'raw_payload'  => 'array',
        'opens'        => 'integer',
        'clicks'       => 'integer',
        'bounces'      => 'integer',
        'blocks'       => 'integer',
        'deferred'     => 'integer',
        'spam_reports' => 'integer',
        'unsubscribes' => 'integer',
    ];

    // Relationship

    public function events()
    {
        return $this->hasMany(EmailEvent::class, 'sg_message_id', 'sg_message_id');
    }

    // Scopes

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('subject',  'like', "%{$term}%")
              ->orWhere('to_email', 'like', "%{$term}%");
        });
    }

    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Accessors — used in blade views

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'processed'   => 'Queued',
            'delivered'   => 'Delivered',
            'open'        => 'Opened',
            'click'       => 'Clicked',
            'bounce'      => 'Bounced',
            'dropped'     => 'Dropped',
            'deferred'    => 'Deferred',
            'spamreport'  => 'Spam',
            'unsubscribe' => 'Unsubscribed',
            default       => ucfirst($this->status ?? 'Unknown'),
        };
    }

    /**
     * CSS class for status badge color.
     * Used in blade: class="status-badge {{ $email->status }}"
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->status) {
            'delivered'   => 'badge-success',
            'open'        => 'badge-info',
            'click'       => 'badge-primary',
            'bounce'      => 'badge-danger',
            'dropped'     => 'badge-warning',
            'deferred'    => 'badge-warning',
            'spamreport'  => 'badge-danger',
            default       => 'badge-secondary',
        };
    }

   
    public function getCategoriesStringAttribute(): string
    {
        return implode(', ', $this->categories ?? []);
    }

    // Statistics — used on dashboard/analytics pages

    public static function statistics(): array
    {
        return [
            'total'       => static::count(),
            'delivered'   => static::where('status', 'delivered')->count(),
            'opened'      => static::where('opens', '>', 0)->count(),
            'clicked'     => static::where('clicks', '>', 0)->count(),
            'bounced'     => static::where('status', 'bounce')->count(),
            'spam'        => static::where('status', 'spamreport')->count(),
            'today'       => static::whereDate('sent_at', today())->count(),
            'this_week'   => static::whereBetween('sent_at', [
                                now()->startOfWeek(),
                                now()->endOfWeek()
                            ])->count(),
        ];
    }
}
