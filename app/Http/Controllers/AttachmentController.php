<?php

namespace App\Http\Controllers;

use App\Models\EmailAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Download an attachment.
     */
    public function download(EmailAttachment $attachment)
    {
        $path = Storage::disk($attachment->disk)->path($attachment->storage_path);

        if (!Storage::disk($attachment->disk)->exists($attachment->storage_path)) {
            abort(404, 'Attachment file not found.');
        }

        return response()->download($path, $attachment->original_filename, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    /**
     * Preview an attachment (for images).
     */
    public function preview(EmailAttachment $attachment)
    {
        if (!$attachment->is_image) {
            abort(404, 'Preview not available for this file type.');
        }

        if (!Storage::disk($attachment->disk)->exists($attachment->storage_path)) {
            abort(404, 'Attachment file not found.');
        }

        $file = Storage::disk($attachment->disk)->get($attachment->storage_path);

        return response($file, 200, [
            'Content-Type' => $attachment->mime_type,
            'Content-Disposition' => 'inline; filename="' . $attachment->original_filename . '"',
        ]);
    }
}
