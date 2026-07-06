@extends('layouts.app')

@section('title', 'Email Activity Dashboard')
@section('header-title', 'Email Activity Dashboard')
@section('header-subtitle', 'Outbound delivery metrics and inbound message activity')

@section('styles')
<style>
    /* Period Toggle*/
    .period-toggle {
        display: inline-flex;
        background: rgba(255,255,255,0.04);
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        padding: 3px;
        gap: 2px;
    }

    .period-btn {
        padding: 6px 18px;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .period-btn.active {
        background: linear-gradient(135deg, rgba(99,102,241,0.3), rgba(236,72,153,0.2));
        border-color: rgba(99,102,241,0.4);
        color: #fff;
    }

    .period-btn:hover:not(.active) {
        color: #fff;
        background: rgba(255,255,255,0.05);
    }

    /* Section header*/
    .section-label {
        font-family: var(--font-display);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-block {
        margin-bottom: 40px;
    }

    .section-block + .section-block {
        padding-top: 32px;
        border-top: 1px solid var(--glass-border);
    }

    /* Stat Cards */
    .stat-cards-row {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .stat-cards-row.cols-6 { grid-template-columns: repeat(6, 1fr); }

    @media (max-width: 1100px) {
        .stat-cards-row,
        .stat-cards-row.cols-6 { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 700px) {
        .stat-cards-row,
        .stat-cards-row.cols-6 { grid-template-columns: repeat(2, 1fr); }
    }

    .stat-card {
        padding: 22px 18px;
        border-right: 1px solid var(--glass-border);
        text-align: center;
        position: relative;
        transition: background 0.2s ease;
    }

    .stat-card:last-child { border-right: none; }

    .stat-card:hover {
        background: rgba(255,255,255,0.02);
    }

    .stat-card-label {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        color: var(--text-main);
        margin-bottom: 10px;
    }

    .stat-card-main {
        font-family: var(--font-display);
        font-size: 1.8rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
    }   
    .stat-card-sub {
        font-size: 0.76rem;
        color: var(--text-main);
        margin-top: 4px;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
    }

    /* Outbound colour set */
    .stat-card.requests  .stat-card-main { color: #fff; }
    .stat-card.requests::after  { background: #0ea5e9; }

    .stat-card.delivered .stat-card-main { color: #4ade80; }
    .stat-card.delivered::after { background: #22C55E; }

    .stat-card.opened    .stat-card-main { color: #fbbf24; }
    .stat-card.opened::after    { background: #f59e0b; }

    .stat-card.clicked   .stat-card-main { color: #a78bfa; }
    .stat-card.clicked::after   { background: #8b5cf6; }

    .stat-card.bounces   .stat-card-main { color: #fb7185; }
    .stat-card.bounces::after   { background: #f43f5e; }

    .stat-card.spam      .stat-card-main { color: #b91c1c; }
    .stat-card.spam::after      { background: #b91c1c; }

    /* Inbound colour set */
    .stat-card.received  .stat-card-main { color: #fff; }
    .stat-card.received::after  { background: #0ea5e9; }

    .stat-card.read       .stat-card-main { color: #4ade80; }
    .stat-card.read::after       { background: #22C55E; }

    .stat-card.starred    .stat-card-main { color: #fbbf24; }
    .stat-card.starred::after    { background: #f59e0b; }

    .stat-card.attach     .stat-card-main { color: #a78bfa; }
    .stat-card.attach::after     { background: #8b5cf6; }

    .stat-card.spamflag   .stat-card-main { color: #b91c1c; }
    .stat-card.spamflag::after   { background: #b91c1c; }

    /* Chart section */
    .chart-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 24px;
        position: relative;
    }

    .chart-title {
        font-family: var(--font-display);
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        margin-bottom: 6px;
    }

    .chart-subtitle {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 20px;
    }

    .chart-legend {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: var(--text-muted);
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Webhook event tags */
    .event-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--glass-border);
        margin: 4px 4px 0 0;
    }

    .event-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')

{{-- ── Top bar: title + period toggle (shared across both sections) ─── --}}
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-family: var(--font-display); font-size: 1.3rem; color: #fff; margin: 0 0 4px;">
            Here's your recent email activity.
        </h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
            {{ $period === 'week' ? 'Last 7 days' : 'Last 30 days' }} ·
            Outbound from sent_emails · Inbound from emails
        </p>
    </div>
    <div class="period-toggle">
        <a href="{{ request()->fullUrlWithQuery(['period' => 'week']) }}"
           class="period-btn {{ $period === 'week' ? 'active' : '' }}">Wk</a>
        <a href="{{ request()->fullUrlWithQuery(['period' => 'month']) }}"
           class="period-btn {{ $period === 'month' ? 'active' : '' }}">Mo</a>
    </div>
</div>

 <!-- OUTBOUND — Sent email activity -->
<div class="section-block">

    <div class="section-label">
        <i class="fa-solid fa-paper-plane" style="color: #818cf8;"></i>
        Outbound — Sent Email Activity
    </div>

    {{-- Stat cards --}}
    <div class="stat-cards-row cols-6">

        <div class="stat-card requests">
            <div class="stat-card-label">Requests</div>
            <div class="stat-card-main">{{ number_format($sentStats['requests']) }}</div>
            <div class="stat-card-sub">Total sent</div>
        </div>

        <div class="stat-card delivered">
            <div class="stat-card-label">Delivered</div>
            <div class="stat-card-main">{{ $sentStats['delivered_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($sentStats['delivered']) }}</div>
        </div>

        <div class="stat-card opened">
            <div class="stat-card-label">Opened</div>
            <div class="stat-card-main">{{ $sentStats['opened_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($sentStats['opens']) }} unique</div>
        </div>

        <div class="stat-card clicked">
            <div class="stat-card-label">Clicked</div>
            <div class="stat-card-main">{{ $sentStats['clicked_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($sentStats['clicks']) }} unique</div>
        </div>

        <div class="stat-card bounces">
            <div class="stat-card-label">Bounces</div>
            <div class="stat-card-main">{{ $sentStats['bounces_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($sentStats['bounces']) }}</div>
        </div>

        <div class="stat-card spam">
            <div class="stat-card-label">Spam Reports</div>
            <div class="stat-card-main">{{ $sentStats['spam_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($sentStats['spam_reports']) }}</div>
        </div>

    </div>

    {{-- Chart --}}
    <div class="chart-card">
        <div class="chart-title">Outbound Activity Over Time</div>
        <div class="chart-subtitle">
            Daily breakdown · {{ $period === 'week' ? 'Last 7 days' : 'Last 30 days' }}
        </div>

        <div class="chart-legend">
            @foreach([
                ['Requests', '#0ea5e9'], ['Delivered', '#22C55E'],
                ['Opens', '#f59e0b'], ['Clicks', '#8b5cf6'], ['Bounces', '#ef4444'],
            ] as [$label, $color])
            <span class="legend-item">
                <span class="legend-dot" style="background: {{ $color }};"></span>
                {{ $label }}
            </span>
            @endforeach
        </div>

        <div style="position: relative; height: 300px;">
            <canvas id="outboundChart"></canvas>
        </div>
    </div>

</div>

<!-- INBOUND — Received email activity  -->
<div class="section-block">

    <div class="section-label">
        <i class="fa-solid fa-inbox" style="color: #4ade80;"></i>
        Inbound — Received Email Activity
    </div>

    {{-- Stat cards --}}
    <div class="stat-cards-row">

        <div class="stat-card received">
            <div class="stat-card-label">Requests</div>
            <div class="stat-card-main">{{ number_format($inboundStats['total']) }}</div>
            <div class="stat-card-sub">Total received</div>
        </div>

        <div class="stat-card read">
            <div class="stat-card-label">Read</div>
            <div class="stat-card-main">{{ $inboundStats['read_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($inboundStats['read']) }}</div>
        </div>

        <div class="stat-card starred">
            <div class="stat-card-label">Starred</div>
            <div class="stat-card-main">{{ $inboundStats['starred_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($inboundStats['starred']) }}</div>
        </div>

        <div class="stat-card attach">
            <div class="stat-card-label">Attachments</div>
            <div class="stat-card-main">{{ $inboundStats['attachments_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($inboundStats['with_attachments']) }}</div>
        </div>

        <div class="stat-card spamflag">
            <div class="stat-card-label">Spam Flagged</div>
            <div class="stat-card-main">{{ $inboundStats['spam_pct'] }}%</div>
            <div class="stat-card-sub">{{ number_format($inboundStats['spam']) }}</div>
        </div>

    </div>

    {{-- Chart --}}
    <div class="chart-card">
        <div class="chart-title">Inbound Activity Over Time</div>
        <div class="chart-subtitle">
            Daily breakdown · {{ $period === 'week' ? 'Last 7 days' : 'Last 30 days' }}
        </div>

        <div class="chart-legend">
            @foreach([
                ['Received', '#0ea5e9'], ['Read', '#22C55E'],
                ['Starred', '#f59e0b'], ['Attachments', '#8b5cf6'], ['Spam', '#b91c1c'],
            ] as [$label, $color])
            <span class="legend-item">
                <span class="legend-dot" style="background: {{ $color }};"></span>
                {{ $label }}
            </span>
            @endforeach
        </div>

        <div style="position: relative; height: 300px;">
            <canvas id="inboundChart"></canvas>
        </div>
    </div>

</div>

 <!-- Webhook event totals — small reference strip, all-time   
<div class="card" style="padding: 20px;">
    <div class="section-label" style="margin-bottom: 10px;">
        <i class="fa-solid fa-bolt" style="color: #fbbf24;"></i>
        Webhook Event Totals (All Time)
    </div>
    <div>
        @php
            $eventColors = [
                'processed'   => '#0ea5e9',
                'delivered'   => '#10b981',
                'open'        => '#8b5cf6',
                'click'       => '#6366f1',
                'bounce'      => '#ef4444',
                'dropped'     => '#f97316',
                'deferred'    => '#f59e0b',
                'spamreport'  => '#dc2626',
                'unsubscribe' => '#6b7280',
            ];
        @endphp
        @forelse($eventStats as $type => $count)
        <span class="event-tag">
            <span class="event-dot" style="background:{{ $eventColors[$type] ?? '#9ca3af' }};"></span>
            <span style="color:#fff;">{{ ucfirst($type) }}:</span>
            <span style="color:var(--text-muted);">{{ number_format($count) }}</span>
        </span>
        @empty
        <span style="color: var(--text-muted); font-size: 0.82rem;">No webhook events recorded yet.</span>
        @endforelse
    </div>
</div> -->

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
(function () {
    const gridColor = 'rgba(255,255,255,0.05)';
    const textColor = 'rgba(255,255,255,0.35)';

    const tooltipStyle = {
        backgroundColor: 'rgba(10,10,20,0.92)',
        borderColor: 'rgba(99,102,241,0.3)',
        borderWidth: 1,
        titleColor: '#fff',
        bodyColor: 'rgba(255,255,255,0.7)',
        padding: 12,
        callbacks: {
            label: function(ctx) {
                return '  ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString();
            }
        }
    };

    const sharedScales = {
        x: {
            grid: { color: gridColor, drawBorder: false },
            ticks: { color: textColor, font: { size: 11 } },
        },
        y: {
            beginAtZero: true,
            grid: { color: gridColor, drawBorder: false },
            ticks: {
                color: textColor,
                font: { size: 11 },
                callback: function(val) {
                    return val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val;
                },
            },
        },
    };

    function renderChart(canvasId, dataJson) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: dataJson,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipStyle,
                },
                scales: sharedScales,
            },
        });
    }

    renderChart('outboundChart', @json(json_decode($sentChartJson)));
    renderChart('inboundChart',  @json(json_decode($inboundChartJson)));
})();
</script>
@endsection
