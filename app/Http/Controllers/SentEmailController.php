<?php

namespace App\Http\Controllers;

use App\Models\SentEmail;
use Illuminate\Http\Request;
use App\Exports\SentEmailReportExport;
use Maatwebsite\Excel\Facades\Excel;

class SentEmailController extends Controller
{
    public function index(Request $request)
    {
        $query = SentEmail::latest('sent_at');

        // ── Quick sidebar search (kept for backward compatibility) ──────
        // Matches the original "search" box: subject OR recipient.
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('subject', 'like', "%{$s}%")
                  ->orWhere('to_email', 'like', "%{$s}%");
            });
        }

        // ── Advanced filters (all optional, all combinable) ─────────────

        // Message ID (sg_message_id)
        $query->when($request->filled('message_id'), function ($query) use ($request) {
            $query->where('sg_message_id', 'like', '%' . $request->message_id . '%');
        });

        // Recipient email (to_email)
        $query->when($request->filled('to_email'), function ($query) use ($request) {
            $query->where('to_email', 'like', '%' . $request->to_email . '%');
        });

        // Sender email (from_email)
        $query->when($request->filled('from_email'), function ($query) use ($request) {
            $query->where('from_email', 'like', '%' . $request->from_email . '%');
        });

        // Subject
        $query->when($request->filled('subject'), function ($query) use ($request) {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        });

        // Status / event type (delivered, bounce, deferred, spam_report, blocked, processed, ...)
        $query->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        });

        // Category (categories is a JSON array column)
        $query->when($request->filled('category'), function ($query) use ($request) {
            $query->whereJsonContains('categories', $request->category);
        });

        // Date range (sent_at)
        $query->when($request->filled('date_from'), function ($query) use ($request) {
            $query->whereDate('sent_at', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($query) use ($request) {
            $query->whereDate('sent_at', '<=', $request->date_to);
        });

        $emails = $query->paginate(20)->withQueryString();

        // Distinct categories for the filter dropdown (only real, existing values)
        $categories = SentEmail::query()
            ->whereNotNull('categories')
            ->pluck('categories')
            ->flatten(1)
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return view('sent-emails.index', compact('emails', 'categories'));
    }

    public function show(SentEmail $sentEmail)
    {
        return view('sent-emails.show', compact('sentEmail'));
    }

    public function download(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date'],
        ]);

        $from = $request->date_from;
        $to   = $request->date_to;

        // Build filename from the date range
        if ($from && $to) {
            $name = "sent-email-report-{$from}-to-{$to}.xlsx";
        } elseif ($from) {
            $name = "sent-email-report-from-{$from}.xlsx";
        } else {
            $name = 'sent-email-report-' . now()->format('Y-m-d') . '.xlsx';
        }

        return Excel::download(
            new SentEmailReportExport($from, $to),
            $name
        );
    }
}