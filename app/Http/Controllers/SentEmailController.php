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
        $sortByActivity = $request->get('sort') === 'activity';

        $query = $sortByActivity
            ? SentEmail::query()->orderByDesc('last_event_at')
            : SentEmail::query()->orderByDesc('sent_at');
   
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('subject', 'like', "%{$s}%")
                  ->orWhere('to_email', 'like', "%{$s}%");
            });
        }

        $query->when($request->filled('message_id'), function ($query) use ($request) {
            $query->where('sg_message_id', 'like', '%' . $request->message_id . '%');
        });

        $query->when($request->filled('to_email'), function ($query) use ($request) {
            $query->where('to_email', 'like', '%' . $request->to_email . '%');
        });

        $query->when($request->filled('from_email'), function ($query) use ($request) {
            $query->where('from_email', 'like', '%' . $request->from_email . '%');
        });

        $query->when($request->filled('subject'), function ($query) use ($request) {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        });

        $query->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        });

        $query->when($request->filled('category'), function ($query) use ($request) {
            $query->whereJsonContains('categories', $request->category);
        });

        $query->when($request->filled('date_from'), function ($query) use ($request) {
            $query->whereDate('sent_at', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($query) use ($request) {
            $query->whereDate('sent_at', '<=', $request->date_to);
        });

        $emails = $query->paginate(20)->withQueryString();

        $categories = SentEmail::query()
            ->whereNotNull('categories')
            ->pluck('categories')
            ->flatten(1)
            ->unique()
            ->filter()
            ->sort()
            ->values();

        return view('sent-emails.index', compact('emails', 'categories', 'sortByActivity'));
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