<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     */
    public function index(Request $request)
    {
        $stats = Email::statistics();
        $eventStats = EmailEvent::typeStatistics();

        // Recent emails
        $recentEmails = Email::with('attachments')
            ->orderBy('received_at', 'desc')
            ->limit(10)
            ->get();

        // Emails per day (last 14 days)
        $dailyEmails = Email::selectRaw('DATE(received_at) as date, COUNT(*) as count')
            ->where('received_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Top senders
        $topSenders = Email::select('from_address', 'from_name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('from_address', 'from_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'eventStats',
            'recentEmails',
            'dailyEmails',
            'topSenders'
        ));
    }
}
