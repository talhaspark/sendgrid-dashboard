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
 
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('subject',  'like', "%{$s}%")
                  ->orWhere('to_email', 'like', "%{$s}%");
            });
        }
 
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
 
        $emails = $query->paginate(20)->withQueryString();
 
        return view('sent-emails.index', compact('emails'));
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
