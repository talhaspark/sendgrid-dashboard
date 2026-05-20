@extends('layouts.app')

@section('title', 'Dashboard Overview')
@section('header-title', 'System Overview')
@section('header-subtitle', 'Real-time telemetry and inbound email statistics')

@section('styles')
<style>
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .metric-card {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        padding: 20px;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .metric-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
        opacity: 0.8;
    }

    .metric-info h3 {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 6px;
    }

    .metric-info .value {
        font-family: var(--font-display);
        font-size: 1.8rem;
        font-weight: 700;
        color: #fff;
    }

    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
    }

    .metric-card.unread .metric-icon { color: var(--status-info); border-color: rgba(59, 130, 246, 0.3); }
    .metric-card.starred .metric-icon { color: var(--status-warning); border-color: rgba(245, 158, 11, 0.3); }
    .metric-card.attachments .metric-icon { color: var(--accent-secondary); border-color: rgba(236, 72, 153, 0.3); }
    .metric-card.spam .metric-icon { color: var(--status-danger); border-color: rgba(239, 68, 68, 0.3); }

    .dashboard-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }

    @media (max-width: 1024px) {
        .dashboard-layout {
            grid-template-columns: 1fr;
        }
    }

    .section-title {
        font-family: var(--font-display);
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
    }

    .recent-emails-table {
        width: 100%;
        border-collapse: collapse;
    }

    .recent-emails-table th, .recent-emails-table td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .recent-emails-table th {
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
    }

    .recent-emails-table tr {
        transition: background-color 0.2s ease;
    }

    .recent-emails-table tr:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }

    .sender-cell {
        display: flex;
        flex-direction: column;
    }

    .sender-name {
        font-weight: 600;
        color: #fff;
    }

    .sender-address {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .subject-link {
        color: var(--text-main);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .subject-link:hover {
        color: var(--accent-primary);
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-badge.processed { background: rgba(16, 185, 129, 0.1); color: var(--status-success); }
    .status-badge.received { background: rgba(59, 130, 246, 0.1); color: var(--status-info); }
    .status-badge.failed { background: rgba(239, 68, 68, 0.1); color: var(--status-danger); }

    .tag {
        display: inline-flex;
        padding: 2px 6px;
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.2);
        color: #a5b4fc;
        border-radius: 4px;
        font-size: 0.7rem;
        font-weight: 500;
    }

    /* Top Senders List */
    .top-sender-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .top-sender-item:last-child {
        border-bottom: none;
    }

    .top-sender-details {
        display: flex;
        flex-direction: column;
    }

    .top-sender-count {
        background: rgba(255, 255, 255, 0.05);
        padding: 4px 8px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Event flow visualization */
    .event-dots {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .event-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@section('content')

<!-- Metrics grid -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-info">
            <h3>Total Inbound</h3>
            <div class="value">{{ $stats['total'] }}</div>
        </div>
        <div class="metric-icon" style="color: var(--accent-primary); border-color: rgba(99, 102, 241, 0.3);">
            <i class="fa-solid fa-cloud-arrow-down"></i>
        </div>
    </div>
    <div class="metric-card unread">
        <div class="metric-info">
            <h3>Unread Emails</h3>
            <div class="value">{{ $stats['unread'] }}</div>
        </div>
        <div class="metric-icon">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
    </div>
    <div class="metric-card starred">
        <div class="metric-info">
            <h3>Starred</h3>
            <div class="value">{{ $stats['starred'] }}</div>
        </div>
        <div class="metric-icon">
            <i class="fa-solid fa-star"></i>
        </div>
    </div>
    <div class="metric-card attachments">
        <div class="metric-info">
            <h3>Attachments</h3>
            <div class="value">{{ $stats['with_attachments'] }}</div>
        </div>
        <div class="metric-icon">
            <i class="fa-solid fa-paperclip"></i>
        </div>
    </div>
    <div class="metric-card spam">
        <div class="metric-info">
            <h3>Spam Flagged</h3>
            <div class="value">{{ $stats['spam'] }}</div>
        </div>
        <div class="metric-icon">
            <i class="fa-solid fa-shield-virus"></i>
        </div>
    </div>
</div>

<!-- Dashboard layout -->
<div class="dashboard-layout">
    
    <!-- Left Column: Recent Emails -->
    <div class="card" style="padding: 20px;">
        <div class="section-title">
            <i class="fa-solid fa-inbox" style="color: var(--accent-primary);"></i>
            Recent Incoming Emails
        </div>
        <div style="overflow-x: auto;">
            <table class="recent-emails-table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Received</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEmails as $email)
                        <tr>
                            <td>
                                <div class="sender-cell">
                                    <span class="sender-name">{{ $email->from_name ?? 'Unknown' }}</span>
                                    <span class="sender-address">{{ $email->from_address }}</span>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('emails.show', $email) }}" class="subject-link">
                                    {{ $email->subject }}
                                </a>
                                @if($email->attachment_count > 0)
                                    <i class="fa-solid fa-paperclip" style="font-size: 0.8rem; color: var(--text-muted); margin-left: 6px;" title="{{ $email->attachment_count }} Attachments"></i>
                                @endif
                                @if($email->labels)
                                    <div style="display: flex; gap: 4px; margin-top: 4px;">
                                        @foreach($email->labels as $lbl)
                                            <span class="tag">{{ $lbl }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $email->status }}">
                                    {{ $email->status }}
                                </span>
                            </td>
                            <td style="font-size: 0.8rem; color: var(--text-muted);">
                                {{ $email->received_at ? $email->received_at->diffForHumans() : 'N/A' }}
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('emails.show', $email) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 8px;">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                                <i class="fa-solid fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 12px; opacity: 0.5;"></i>
                                No emails received yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <a href="{{ route('emails.index') }}" class="btn btn-primary" style="font-size: 0.85rem;">
                <i class="fa-solid fa-list"></i> View All Inbox Emails
            </a>
        </div>
    </div>

    <!-- Right Column: Senders & Webhook Feed -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Top Senders -->
        <div class="card" style="padding: 20px;">
            <div class="section-title">
                <i class="fa-solid fa-users" style="color: var(--accent-secondary);"></i>
                Top Sender Domains
            </div>
            <div>
                @forelse($topSenders as $sender)
                    <div class="top-sender-item">
                        <div class="top-sender-details">
                            <span style="font-weight: 600; font-size: 0.9rem; color: #fff;">{{ $sender->from_name ?? $sender->from_address }}</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $sender->from_address }}</span>
                        </div>
                        <span class="top-sender-count">{{ $sender->count }} emails</span>
                    </div>
                @empty
                    <p style="color: var(--text-muted); font-size: 0.85rem; padding: 20px 0; text-align: center;">No sender metrics yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Webhook Event Stream Overview -->
        <div class="card" style="padding: 20px;">
            <div class="section-title">
                <i class="fa-solid fa-bolt" style="color: var(--status-warning);"></i>
                SendGrid Events Real-time
            </div>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">Visual feed of SendGrid webhook delivery & interaction status tags</p>
            <div class="event-dots">
                @foreach($eventStats as $type => $count)
                    <span class="status-badge" style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 8px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{
                            match($type) {
                                'processed' => '#3b82f6',
                                'delivered' => '#10b981',
                                'open' => '#8b5cf6',
                                'click' => '#6366f1',
                                'bounce' => '#ef4444',
                                'dropped' => '#f97316',
                                'deferred' => '#f59e0b',
                                'spam_report' => '#dc2626',
                                default => '#6b7280'
                            }
                        }}"></span>
                        <span style="color: #fff; font-weight: 500;">{{ ucfirst($type) }}:</span> {{ $count }}
                    </span>
                @endforeach
            </div>
        </div>

    </div>

</div>

@endsection
