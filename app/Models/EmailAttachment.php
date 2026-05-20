<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'storage_path',
        'disk',
        'content_id',
        'checksum',
        'is_inline',
        'is_scanned',
        'is_clean',
        'scan_result',
    ];

    protected $casts = [
        'size' => 'integer',
        'is_inline' => 'boolean',
        'is_scanned' => 'boolean',
        'is_clean' => 'boolean',
    ];

    /**
     * Get the email this attachment belongs to.
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Check if attachment is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Get icon class based on file type.
     */
    public function getIconClassAttribute(): string
    {
        return match(true) {
            str_starts_with($this->mime_type, 'image/') => 'fa-file-image',
            str_starts_with($this->mime_type, 'video/') => 'fa-file-video',
            str_starts_with($this->mime_type, 'audio/') => 'fa-file-audio',
            str_contains($this->mime_type, 'pdf') => 'fa-file-pdf',
            str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document') => 'fa-file-word',
            str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet') => 'fa-file-excel',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'archive') => 'fa-file-archive',
            str_contains($this->mime_type, 'text/') => 'fa-file-alt',
            default => 'fa-file',
        };
    }
}
