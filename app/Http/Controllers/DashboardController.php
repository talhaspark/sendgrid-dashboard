<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\EmailEvent;
use App\Models\SentEmail;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ── Period toggle (week / month) ──────────────────────────────
        $period      = $request->get('period', 'week');
        $days        = $period === 'month' ? 30 : 7;
        $periodStart = now()->subDays($days)->startOfDay();

        // OUTBOUND Email Stats
        $sentStatsRaw = SentEmail::selectRaw("
                COUNT(*) as requests,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN opens > 0 THEN 1 ELSE 0 END) as opens,
                COALESCE(SUM(opens),0) as total_opens,
                SUM(CASE WHEN clicks > 0 THEN 1 ELSE 0 END) as clicks,
                COALESCE(SUM(clicks),0) as total_clicks,
                SUM(CASE WHEN bounces > 0 THEN 1 ELSE 0 END) as bounces,
                SUM(CASE WHEN spam_reports > 0 THEN 1 ELSE 0 END) as spam_reports
            ")
            ->where('sent_at', '>=', $periodStart)
            ->first();

        $requests    = (int) $sentStatsRaw->requests;
        $delivered   = (int) $sentStatsRaw->delivered;
        $opens       = (int) $sentStatsRaw->opens;
        $totalOpens  = (int) $sentStatsRaw->total_opens;
        $clicks      = (int) $sentStatsRaw->clicks;
        $totalClicks = (int) $sentStatsRaw->total_clicks;
        $bounces     = (int) $sentStatsRaw->bounces;
        $spamReports = (int) $sentStatsRaw->spam_reports;

        $sentStats = [
            'requests'      => $requests,
            'delivered'     => $delivered,
            'delivered_pct' => $requests  > 0 ? round(($delivered   / $requests)  * 100, 2) : 0,
            'opens'         => $opens,
            'total_opens'   => $totalOpens,
            'opened_pct'    => $delivered > 0 ? round(($opens       / $delivered) * 100, 2) : 0,
            'clicks'        => $clicks,
            'total_clicks'  => $totalClicks,
            'clicked_pct'   => $delivered > 0 ? round(($clicks      / $delivered) * 100, 2) : 0,
            'bounces'       => $bounces,
            'bounces_pct'   => $requests  > 0 ? round(($bounces     / $requests)  * 100, 2) : 0,
            'spam_reports'  => $spamReports,
            'spam_pct'      => $requests  > 0 ? round(($spamReports / $requests)  * 100, 2) : 0,
        ];

        // Outbound chart data
        $sentChartRows = SentEmail::selectRaw("
                DATE(sent_at) AS day,
                COUNT(*) AS requests,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered,
                SUM(opens)  AS total_opens,
                SUM(clicks) AS total_clicks,
                SUM(CASE WHEN bounces > 0 THEN 1 ELSE 0 END) AS bounces
            ")
            ->where('sent_at', '>=', $periodStart)
            ->whereNotNull('sent_at')
            ->groupByRaw('DATE(sent_at)')
            ->orderByRaw('DATE(sent_at)')
            ->get();

        $sentChartJson = $this->buildChartJson($days, $sentChartRows, [
            'requests'  => ['Requests',  '#6366f1', 'requests'],
            'delivered' => ['Delivered', '#10b981', 'delivered'],
            'opens'     => ['Opens',     '#f59e0b', 'total_opens'],
            'clicks'    => ['Clicks',    '#8b5cf6', 'total_clicks'],
            'bounces'   => ['Bounces',   '#ef4444', 'bounces'],
        ]);

        // INBOUND Email Stats
        $inboundStatsRaw = Email::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count,
                SUM(CASE WHEN is_starred = 1 THEN 1 ELSE 0 END) as starred,
                SUM(CASE WHEN attachment_count > 0 THEN 1 ELSE 0 END) as with_attachments,
                SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) as spam
            ")
            ->where('received_at', '>=', $periodStart)
            ->first();

        $totalInbound  = (int) $inboundStatsRaw->total;
        $readCount     = (int) $inboundStatsRaw->read_count;
        $starredCount  = (int) $inboundStatsRaw->starred;
        $attachCount   = (int) $inboundStatsRaw->with_attachments;
        $spamCount     = (int) $inboundStatsRaw->spam;

        $inboundStats = [
            'total'             => $totalInbound,
            'read'              => $readCount,
            'read_pct'          => $totalInbound > 0 ? round(($readCount    / $totalInbound) * 100, 2) : 0,
            'starred'           => $starredCount,
            'starred_pct'       => $totalInbound > 0 ? round(($starredCount / $totalInbound) * 100, 2) : 0,
            'with_attachments'  => $attachCount,
            'attachments_pct'   => $totalInbound > 0 ? round(($attachCount  / $totalInbound) * 100, 2) : 0,
            'spam'              => $spamCount,
            'spam_pct'          => $totalInbound > 0 ? round(($spamCount    / $totalInbound) * 100, 2) : 0,
        ];

        // Inbound chart data
        $inboundChartRows = Email::selectRaw("
                DATE(received_at) AS day,
                COUNT(*) AS total,
                SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) AS read_count,
                SUM(CASE WHEN is_starred = 1 THEN 1 ELSE 0 END) AS starred,
                SUM(CASE WHEN attachment_count > 0 THEN 1 ELSE 0 END) AS with_attachments,
                SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) AS spam
            ")
            ->where('received_at', '>=', $periodStart)
            ->whereNotNull('received_at')
            ->groupByRaw('DATE(received_at)')
            ->orderByRaw('DATE(received_at)')
            ->get();

        $inboundChartJson = $this->buildChartJson($days, $inboundChartRows, [
            'total'            => ['Received',    '#6366f1', 'total'],
            'read'             => ['Read',         '#10b981', 'read_count'],
            'starred'          => ['Starred',      '#f59e0b', 'starred'],
            'with_attachments' => ['Attachments',  '#8b5cf6', 'with_attachments'],
            'spam'             => ['Spam',         '#b91c1c', 'spam'],
        ]);

        // Webhook event 
        $eventStats = EmailEvent::typeStatistics();

        return view('dashboard.index', compact(
            'sentStats',
            'sentChartJson',
            'inboundStats',
            'inboundChartJson',
            'eventStats',
            'period',
            'days',
        ));
    }

    /**
     * @param int        
     * @param Collection 
     * @param array      
     */
    private function buildChartJson(int $days, $rows, array $series): string
    {
        $labels   = [];
        $datasets = [];

        foreach ($series as $key => [$label, $color, $field]) {
            $datasets[$key] = [];
        }

        $cursor = now()->subDays($days - 1)->startOfDay();

        while ($cursor->lte(now())) {
            $dateStr  = $cursor->toDateString();
            $labels[] = $cursor->format($days <= 7 ? 'D' : 'M d');

            $row = $rows->firstWhere('day', $dateStr);

            foreach ($series as $key => [$label, $color, $field]) {
                $datasets[$key][] = $row ? (int) $row->{$field} : 0;
            }

            $cursor->addDay();
        }

        $chartDatasets = [];
        foreach ($series as $key => [$label, $color, $field]) {
            $chartDatasets[] = [
                'label'           => $label,
                'data'            => $datasets[$key],
                'borderColor'     => $color,
                'backgroundColor' => $this->hexToRgba($color, 0.08),
                'tension'         => 0.4,
                'fill'            => false,
                'borderWidth'     => 2,
                'pointRadius'     => 3,
            ];
        }

        return json_encode([
            'labels'   => $labels,
            'datasets' => $chartDatasets,
        ]);
    }

    private function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');
        $r   = hexdec(substr($hex, 0, 2));
        $g   = hexdec(substr($hex, 2, 2));
        $b   = hexdec(substr($hex, 4, 2));
        return "rgba({$r},{$g},{$b},{$alpha})";
    }
}
