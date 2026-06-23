<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\SentEmailController;
use App\Models\Email;
use App\Models\EmailEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ── Auth routes ───────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// ── Protected routes ──────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Emails
    Route::get('/emails', [EmailController::class, 'index'])->name('emails.index');
    Route::get('/emails/{email}', [EmailController::class, 'show'])->name('emails.show');
    Route::post('/emails/{email}/star', [EmailController::class, 'toggleStar'])->name('emails.star');
    Route::post('/emails/{email}/mark-read', [EmailController::class, 'markRead'])->name('emails.mark-read');
    Route::post('/emails/{email}/spam', [EmailController::class, 'toggleSpam'])->name('emails.spam');
    Route::delete('/emails/{email}', [EmailController::class, 'destroy'])->name('emails.destroy');
    // Sent Emails
    Route::get('/sent-emails', [SentEmailController::class, 'index'])
        ->name('sent-emails.index');

    Route::get('/sent-emails/{sentEmail}', [SentEmailController::class, 'show'])
        ->name('sent-emails.show');
    // Attachments
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{attachment}/preview', [AttachmentController::class, 'preview'])->name('attachments.preview');

    // Analytics
    Route::get('/analytics', function () {
        $eventStats = EmailEvent::typeStatistics();
        $totalEmails = Email::count();
        $totalEvents = EmailEvent::count();

        $eventsOverTime = EmailEvent::selectRaw('DATE(event_timestamp) as date, event_type, COUNT(*) as count')
            ->where('event_timestamp', '>=', now()->subDays(30))
            ->groupBy('date', 'event_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $hourlyDistribution = EmailEvent::selectRaw('HOUR(event_timestamp) as hour, COUNT(*) as count')
            ->whereNotNull('event_timestamp')
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        return view('analytics.index', compact('eventStats', 'totalEmails', 'totalEvents', 'eventsOverTime', 'hourlyDistribution'));
    })->name('analytics');

    // Logs & Audits
    Route::get('/logs', function (Request $request) {
        $logs = DB::table('logs')
            ->when($request->level, fn ($q) => $q->where('level', $request->level))
            ->when($request->channel, fn ($q) => $q->where('channel', $request->channel))
            ->when($request->search, fn ($q) => $q->where('message', 'like', "%{$request->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        $auditLogs = DB::table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('logs.index', compact('logs', 'auditLogs'));
    })->name('logs');

});
