<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\EmailLogger;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct(
        protected EmailLogger $logger
    ) {}

    /**
     * Display list of emails with filtering.
     */
    public function index(Request $request)
    {
        $emails = Email::query()
            ->with('attachments')
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->from, fn($q) => $q->where('from_address', $request->from))
            ->when($request->date_from, fn($q) => $q->where('received_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('received_at', '<=', $request->date_to))
            ->when($request->status === 'unread', fn($q) => $q->unread())
            ->when($request->status === 'starred', fn($q) => $q->starred())
            ->when($request->status === 'spam', fn($q) => $q->spam())
            ->when($request->has_attachments, fn($q) => $q->where('attachment_count', '>', 0))
            ->orderBy('received_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $unreadCount = Email::unread()->count();

        return view('emails.index', compact('emails', 'unreadCount'));
    }

    /**
     * Display a single email.
     */
    public function show(Email $email)
    {
        $email->load(['attachments', 'events' => function ($q) {
            $q->orderBy('event_timestamp', 'desc');
        }]);

        // Mark as read
        if (!$email->is_read) {
            $email->update(['is_read' => true]);
            $this->logger->audit('email.read', 'Email', $email->id);
        }

        return view('emails.show', compact('email'));
    }

    /**
     * Toggle star on an email.
     */
    public function toggleStar(Email $email)
    {
        $email->update(['is_starred' => !$email->is_starred]);

        return response()->json([
            'success' => true,
            'is_starred' => $email->is_starred,
        ]);
    }

    /**
     * Mark email as read/unread.
     */
    public function markRead(Email $email, Request $request)
    {
        $isRead = $request->boolean('is_read', true);
        $email->update(['is_read' => $isRead]);

        $this->logger->audit(
            $isRead ? 'email.read' : 'email.unread',
            'Email',
            $email->id
        );

        return back()->with('success', $isRead ? 'Email marked as read.' : 'Email marked as unread.');
    }

    /**
     * Mark email as spam / not spam.
     */
    public function toggleSpam(Email $email)
    {
        $email->update(['is_spam' => !$email->is_spam]);

        $this->logger->audit(
            $email->is_spam ? 'email.spam' : 'email.not_spam',
            'Email',
            $email->id
        );

        return back()->with('success', $email->is_spam ? 'Marked as spam.' : 'Removed from spam.');
    }

    /**
     * Soft delete an email.
     */
    public function destroy(Email $email)
    {
        $this->logger->audit('email.deleted', 'Email', $email->id, null, null, "Deleted email from {$email->from_address}");

        $email->delete();

        return redirect()->route('emails.index')->with('success', 'Email deleted.');
    }
}
