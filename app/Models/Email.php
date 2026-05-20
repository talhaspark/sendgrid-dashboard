<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'message_id',
        'from_address',
        'from_name',
        'to_addresses',
        'cc_addresses',
        'bcc_addresses',
        'subject',
        'text_body',
        'html_body',
        'headers',
        'sender_ip',
        'spam_score',
        'spam_report',
        'attachment_count',
        'is_read',
        'is_starred',
        'is_spam',
        'labels',
        'status',
        'raw_payload',
        'envelope',
        'received_at',
    ];

    protected $casts = [
        'to_addresses' => 'array',
        'cc_addresses' => 'array',
        'bcc_addresses' => 'array',
        'headers' => 'array',
        'labels' => 'array',
        'raw_payload' => 'array',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'is_spam' => 'boolean',
        'spam_score' => 'float',
        'received_at' => 'datetime',
    ];

    /**
     * Get all attachments for this email.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    /**
     * Get all events for this email.
     */
    public function events(): HasMany
    {
        return $this->hasMany(EmailEvent::class);
    }

    /**
     * Scope: search emails by subject, from, or body.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('subject', 'like', "%{$term}%")
              ->orWhere('from_address', 'like', "%{$term}%")
              ->orWhere('from_name', 'like', "%{$term}%")
              ->orWhere('text_body', 'like', "%{$term}%");
        });
    }

    /**
     * Scope: filter unread emails.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: filter starred emails.
     */
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    /**
     * Scope: filter spam emails.
     */
    public function scopeSpam($query)
    {
        return $query->where('is_spam', true);
    }

    /**
     * Check if email has a specific label.
     */
    public function hasLabel(string $label): bool
    {
        return in_array($label, $this->labels ?? []);
    }

    /**
     * Get a sanitized HTML body (strip scripts).
     */
    public function getSanitizedHtmlAttribute(): string
    {
        if (!$this->html_body) {
            return nl2br(e($this->text_body ?? ''));
        }

        // Strip script tags and event handlers
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $this->html_body);
        $clean = preg_replace('/\bon\w+\s*=\s*"[^"]*"/i', '', $clean);
        $clean = preg_replace('/\bon\w+\s*=\s*\'[^\']*\'/i', '', $clean);

        return $clean;
    }

    /**
     * Get short preview of the email body.
     */
    public function getPreviewAttribute(): string
    {
        $text = $this->text_body ?? strip_tags($this->html_body ?? '');
        return \Illuminate\Support\Str::limit(trim($text), 120);
    }

    /**
     * Get formatted "to" addresses as a string.
     */
    public function getToStringAttribute(): string
    {
        $addresses = $this->to_addresses ?? [];
        return implode(', ', $addresses);
    }

    /**
     * Get aggregate statistics.
     */
    public static function statistics(): array
    {
        return [
            'total' => static::count(),
            'unread' => static::unread()->count(),
            'starred' => static::starred()->count(),
            'spam' => static::spam()->count(),
            'today' => static::whereDate('received_at', today())->count(),
            'this_week' => static::whereBetween('received_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'with_attachments' => static::where('attachment_count', '>', 0)->count(),
        ];
    }
}
