@extends('layouts.app')

@section('title', 'Analytics')
@section('header-title', 'Telemetry Analytics')
@section('header-subtitle', 'SendGrid event delivery statistics and recipient engagement')

@section('styles')
<style>
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }

    .stat-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 16px;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .stat-bar-container {
        flex: 1;
        height: 8px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
        overflow: hidden;
        margin: 0 16px;
    }

    .stat-bar {
        height: 100%;
        border-radius: 4px;
        transition: width 1s ease-in-out;
    }

    .chart-container {
        height: 250px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        padding-top: 20px;
        border-bottom: 1px solid var(--glass-border);
        margin-bottom: 16px;
    }

    .chart-bar-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        height: 100%;
        justify-content: flex-end;
    }

    .chart-bar {
        width: 60%;
        max-width: 40px;
        background: linear-gradient(180deg, var(--accent-primary) 0%, rgba(99, 102, 241, 0.2) 100%);
        border-radius: 6px 6px 0 0;
        transition: height 1s ease-in-out;
        position: relative;
    }

    .chart-bar::after {
        content: attr(data-value);
        position: absolute;
        top: -24px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
    }

    .chart-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        margin-top: 8px;
        text-align: center;
        white-space: nowrap;
    }
</style>
@section('content')

<div class="analytics-grid">
    
    <!-- SendGrid Interaction Engagement Stats -->
    <div class="card">
        <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-gauge-high" style="color: var(--accent-primary);"></i>
            Engagement Telemetry
        </div>
        
        <div class="stat-list">
            @php
                $delivered = $eventStats['delivered'] ?? 0;
                $processed = $eventStats['processed'] ?? 0;
                $opens = $eventStats['open'] ?? 0;
                $clicks = $eventStats['click'] ?? 0;
                $bounces = $eventStats['bounce'] ?? 0;
                $spams = $eventStats['spam_report'] ?? 0;
                
                $totalBase = max($processed, 1);
                $openRate = $delivered > 0 ? round(($opens / $delivered) * 100, 1) : 0;
                $clickRate = $opens > 0 ? round(($clicks / $opens) * 100, 1) : 0;
                $bounceRate = $totalBase > 0 ? round(($bounces / $totalBase) * 100, 1) : 0;
            @endphp
            
            <div class="stat-row">
                <span style="font-size: 0.85rem; width: 100px; color: var(--text-muted);">Open Rate</span>
                <div class="stat-bar-container">
                    <div class="stat-bar" style="width: {{ min($openRate, 100) }}%; background: #8b5cf6;"></div>
                </div>
                <span style="font-weight: 600; color: #fff; width: 50px; text-align: right;">{{ $openRate }}%</span>
            </div>

            <div class="stat-row">
                <span style="font-size: 0.85rem; width: 100px; color: var(--text-muted);">Click-to-Open</span>
                <div class="stat-bar-container">
                    <div class="stat-bar" style="width: {{ min($clickRate, 100) }}%; background: #6366f1;"></div>
                </div>
                <span style="font-weight: 600; color: #fff; width: 50px; text-align: right;">{{ $clickRate }}%</span>
            </div>

            <div class="stat-row">
                <span style="font-size: 0.85rem; width: 100px; color: var(--text-muted);">Bounce Rate</span>
                <div class="stat-bar-container">
                    <div class="stat-bar" style="width: {{ min($bounceRate, 100) }}%; background: #ef4444;"></div>
                </div>
                <span style="font-weight: 600; color: #fff; width: 50px; text-align: right;">{{ $bounceRate }}%</span>
            </div>
        </div>
    </div>

    <!-- Event Count Summary Table -->
    <div class="card">
        <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            <i class="fa-solid fa-server" style="color: var(--accent-secondary);"></i>
            SendGrid Action Breakdown
        </div>
        
        <table class="w-full" style="border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); text-align: left; color: var(--text-muted);">
                    <th style="padding: 8px 0; font-weight: 600;">Event Type</th>
                    <th style="padding: 8px 0; font-weight: 600; text-align: right;">Registered Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($eventStats as $type => $count)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                        <td style="padding: 10px 0; display: flex; align-items: center; gap: 8px; font-weight: 500;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{
                                match($type) {
                                    'processed' => '#3b82f6',
                                    'delivered' => '#10b981',
                                    'open' => '#8b5cf6',
                                    'click' => '#6366f1',
                                    'bounce' => '#ef4444',
                                    default => '#9ca3af'
                                }
                            }}"></span>
                            {{ ucfirst($type) }}
                        </td>
                        <td style="padding: 10px 0; text-align: right; font-weight: 600; color: #fff;">{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- Daily Inbound Density Graph (using beautiful custom styled bars) -->
<div class="card" style="margin-bottom: 24px;">
    <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
        <i class="fa-solid fa-chart-simple" style="color: var(--status-info);"></i>
        Hourly Density Distribution (24-Hour Spread)
    </div>
    
    <div class="chart-container">
        @php
            $maxHour = count($hourlyDistribution) > 0 ? max($hourlyDistribution) : 1;
        @endphp
        @for($h = 0; $h < 24; $h++)
            @php
                $count = $hourlyDistribution[$h] ?? 0;
                $pct = round(($count / $maxHour) * 100);
            @endphp
            <div class="chart-bar-wrapper">
                <div class="chart-bar" style="height: {{ max($pct, 5) }}%;" data-value="{{ $count }}"></div>
                <div class="chart-label">{{ sprintf("%02d:00", $h) }}</div>
            </div>
        @endfor
    </div>
</div>

@endsection
