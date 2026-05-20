<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    /**
     * Process and store attachments from a SendGrid inbound parse webhook.
     *
     * @param Email $email
     * @param array $files Array of UploadedFile objects
     * @return array Array of created EmailAttachment models
     */
    public function processAttachments(Email $email, array $files): array
    {
        $attachments = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            try {
                $attachment = $this->storeAttachment($email, $file);
                $attachments[] = $attachment;
            } catch (\Exception $e) {
                Log::channel('email')->error('Failed to store attachment', [
                    'email_id' => $email->id,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Update attachment count on the email
        $email->update(['attachment_count' => count($attachments)]);

        return $attachments;
    }

    /**
     * Store a single attachment file.
     */
    protected function storeAttachment(Email $email, UploadedFile $file): EmailAttachment
    {
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        // Organize by date and email ID
        $directory = 'attachments/' . now()->format('Y/m') . '/' . $email->id;

        // Store the file
        $path = $file->storeAs($directory, $filename, 'local');

        // Calculate checksum
        $checksum = md5_file($file->getRealPath());

        return EmailAttachment::create([
            'email_id' => $email->id,
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'storage_path' => $path,
            'disk' => 'local',
            'checksum' => $checksum,
            'is_inline' => false,
            'is_scanned' => false,
            'is_clean' => true,
        ]);
    }

    /**
     * Get the download path for an attachment.
     */
    public function getDownloadPath(EmailAttachment $attachment): ?string
    {
        if (Storage::disk($attachment->disk)->exists($attachment->storage_path)) {
            return Storage::disk($attachment->disk)->path($attachment->storage_path);
        }

        return null;
    }

    /**
     * Delete attachment file from storage.
     */
    public function deleteAttachment(EmailAttachment $attachment): bool
    {
        $deleted = Storage::disk($attachment->disk)->delete($attachment->storage_path);

        if ($deleted) {
            $attachment->delete();
        }

        return $deleted;
    }
}
