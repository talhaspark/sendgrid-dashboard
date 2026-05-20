@extends('layouts.app')

@section('title', 'Logs & Audit Trails')
@section('header-title', 'Telemetry Logs & Audits')
@section('header-subtitle', 'Real-time server log parser and entity audit trail tracking')

@section('styles')
<style>
    .logs-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .logs-layout {
            grid-template-columns: 1fr;
        }
    }

    .log-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .log-table th, .log-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255,255,255,0.03);
    }

    .log-table th {
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .log-badge {
        display: inline-flex;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .log-badge.info { background: rgba(59, 130, 246, 0.1); color: var(--status-info); }
    .log-badge.warning { background: rgba(245, 158, 11, 0.1); color: var(--status-warning); }
    .log-badge.error { background: rgba(239, 68, 68, 0.1); color: var(--status-danger); }
    .log-badge.debug { background: rgba(156, 163, 175, 0.15); color: var(--text-muted); }

    .audit-item {
        border-left: 3px solid var(--accent-primary);
        background: rgba(255, 255, 255, 0.01);
        border-radius: 0 10px 10px 0;
        padding: 12px 16px;
        margin-bottom: 12px;
        border-bottom: 1px solid var(--glass-border);
    }

    .audit-action {
        font-weight: 600;
        color: #fff;
        font-size: 0.9rem;
    }

    .audit-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 4px;
    }
</style>
@section('content')

<div class="logs-layout">
    
    <!-- Left Column: Database Application Logs -->
    <div class="card" style="padding: 20px;">
        <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            <i class="fa-solid fa-server" style="color: var(--accent-primary);"></i>
            App Diagnostic Logs
        </div>
        
        <div style="overflow-x: auto;">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Level</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="color: var(--text-muted); white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                            </td>
                            <td>
                                <span class="log-badge {{ $log->level }}">
                                    {{ $log->level }}
                                </span>
                            </td>
                            <td style="color: #e6edf3; font-family: monospace;">
                                {{ $log->message }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                                No server logs captured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Audit Logs -->
    <div class="card" style="padding: 20px;">
        <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
            <i class="fa-solid fa-user-shield" style="color: var(--accent-secondary);"></i>
            Activity Audit Trail
        </div>
        
        <div>
            @forelse($auditLogs as $audit)
                <div class="audit-item" style="border-left-color: {{
                    match($audit->action) {
                        'email.received' => '#10b981',
                        'email.read' => '#3b82f6',
                        'email.deleted' => '#ef4444',
                        default => 'var(--accent-primary)'
                    }
                }}">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="audit-action">{{ ucfirst(str_replace('.', ' ', $audit->action)) }}</span>
                        <span class="tag" style="font-size: 0.65rem;">ID: #{{ $audit->entity_id }}</span>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">{{ $audit->description ?: 'Action successfully verified.' }}</p>
                    <div class="audit-time">
                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i>
                        {{ \Carbon\Carbon::parse($audit->created_at)->diffForHumans() }}
                        &bull; IP: <span style="font-family: monospace;">{{ $audit->ip_address }}</span>
                    </div>
                </div>
            @empty
                <div style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                    No audit trail items recorded.
                </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
