@extends('layouts.app')

@section('title', 'Sent Emails')
@section('header-title', 'Sent Emails')
@section('header-subtitle', 'Email activity logs synced from SendGrid')
@section('styles')
<style>
    .inbox-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .inbox-layout { grid-template-columns: 1fr; }
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 20px;
    }

    .filter-group:last-child { margin-bottom: 0; }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
    }

    .filter-btn {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        color: var(--text-muted);
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
    }

    .filter-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
    }

    .filter-btn.active {
        color: #fff;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(236, 72, 153, 0.1) 100%);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .search-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        padding: 10px 14px;
        color: #fff;
        font-family: var(--font-body);
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--accent-primary);
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
    }

    .email-item {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .email-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        border-color: rgba(99, 102, 241, 0.2);
    }

    .email-item.status-processed   { border-left: 4px solid #3b82f6; }
    .email-item.status-delivered   { border-left: 4px solid #22c55e; }
    .email-item.status-open        { border-left: 4px solid #8b5cf6; }
    .email-item.status-click       { border-left: 4px solid #6366f1; }
    .email-item.status-not_delivered { border-left: 4px solid #ff1010; }
    .email-item.status-bounce      { border-left: 4px solid #ef4444; }
    .email-item.status-deferred    { border-left: 4px solid #f59e0b; }
    .email-item.status-spam_report { border-left: 4px solid #a855f7; }
    .email-item.status-blocked     { border-left: 4px solid #6b7280; }

    .email-meta-sender {
        width: 200px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
    }

    .email-main-content {
        flex: 1;
        min-width: 0;
    }

    .email-title {
        font-weight: 600;
        font-family: var(--font-display);
        font-size: 1rem;
        color: #fff;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .email-meta-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
        flex-shrink: 0;
    }

    .email-time {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .email-activity-time {
        font-size: 0.72rem;
        color: #818cf8;
        opacity: 0.85;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border: 1px solid transparent;
    }

    .status-badge.delivered   { background: rgba(34,197,94,0.12);  border-color: rgba(34,197,94,0.25);  color: #4ade80; }
    .status-badge.processed   { background: rgba(99,102,241,0.12); border-color: rgba(99,102,241,0.25); color: #818cf8; }
    .status-badge.open        { background: rgba(139,92,246,0.12); border-color: rgba(139,92,246,0.25); color: #c4b5fd; }
    .status-badge.click       { background: rgba(99,102,241,0.14); border-color: rgba(99,102,241,0.3);  color: #a5b4fc; }
    .status-badge.bounce      { background: rgba(239,68,68,0.12);  border-color: rgba(239,68,68,0.25);  color: #f87171; }
    .status-badge.deferred    { background: rgba(245,158,11,0.12); border-color: rgba(245,158,11,0.25); color: #fbbf24; }
    .status-badge.spam_report { background: rgba(168,85,247,0.12); border-color: rgba(168,85,247,0.25); color: #c084fc; }
    .status-badge.blocked     { background: rgba(107,114,128,0.12);border-color: rgba(107,114,128,0.25);color: #9ca3af; }
    .status-badge.dropped     { background: rgba(249,115,22,0.12); border-color: rgba(249,115,22,0.25); color: #fb923c; }
    .status-badge.unsubscribe { background: rgba(107,114,128,0.12);border-color: rgba(107,114,128,0.25);color: #9ca3af; }

    .eng-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-right: 10px;
    }

    .eng-pill.active { color: #fff; }

    /* ─── Sort toggle ────────────────────────────────────────────────── */
    .sort-toggle {
        display: inline-flex;
        gap: 4px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--glass-border);
        border-radius: 8px;
        padding: 3px;
    }

    .sort-toggle a {
        padding: 5px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        border-radius: 6px;
    }

    .sort-toggle a.active {
        background: linear-gradient(135deg, rgba(99,102,241,0.35), rgba(236,72,153,0.25));
        color: #fff;
    }

    /* ─── Custom Pagination ──────────────────────────────────────────── */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .page-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.07);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .page-btn.active {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.35) 0%, rgba(236, 72, 153, 0.2) 100%);
        border-color: rgba(99, 102, 241, 0.5);
        color: #fff;
        font-weight: 700;
    }

    .page-btn.disabled {
        opacity: 0.3;
        pointer-events: none;
        cursor: default;
    }

    .page-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .report-filter-btn {
        flex: 1;
        padding: 5px 8px;
        font-size: 0.72rem;
        font-weight: 600;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--glass-border);
        border-radius: 6px;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }

    .report-filter-btn.active {
        background: linear-gradient(135deg, rgba(99,102,241,0.35), rgba(236,72,153,0.25));
        border-color: rgba(99,102,241,0.6);
        color: #fff;
    }

    /* ─── Advanced Filter Bar ────────────────────────────────────────── */
    .adv-filter-card {
        margin-bottom: 20px;
        padding: 18px 20px;
    }

    .adv-filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        cursor: pointer;
        user-select: none;
    }

    .adv-filter-header h4 {
        margin: 0;
        font-family: var(--font-display);
        font-size: 0.95rem;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .adv-filter-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(99,102,241,0.5), rgba(236,72,153,0.4));
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
    }

    .adv-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    @media (max-width: 1100px) {
        .adv-filter-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .adv-filter-grid { grid-template-columns: 1fr; }
    }

 .adv-filter-field label {
    font-size: 0.72rem;
    color: var(--text-muted);
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* Status select */
.adv-filter-field select.search-input {
    width: 100%;
    height: 40px;
    padding: 0 38px 0 12px;
    border: 1px solid #252d3d;
    border-radius: 9px;
    background-color: #171d2b;
    color: #f1f5f9;
    font-size: 0.85rem;
    outline: none;

    /* Important for native dropdown on dark UI */
    color-scheme: dark;
}

/* Select options */
.adv-filter-field select.search-input option {
    background-color: #171d2b;
    color: #f1f5f9;
}

/* Selected option */
.adv-filter-field select.search-input option:checked {
    background-color: #1e366e;
    color: #ffffff;
}

.adv-filter-field select.search-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
}

    .adv-filter-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
        align-items: center;
    }

    select.search-input {
        appearance: none;
        cursor: pointer;
    }
    /* ─────────────────────────────────────────────────────────────────── */
</style>
@endsection

@section('content')

<div class="inbox-layout">

    {{-- Sidebar --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card" style="padding: 20px;">

            <form action="{{ route('sent-emails.index') }}" method="GET" class="filter-group">
                <span class="filter-label">Quick Search</span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Subject or recipient..."
                       class="search-input">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 0.85rem;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </form>

            <div class="filter-group" style="margin-top: 20px;">
                <span class="filter-label">Status</span>

                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => null])) }}"
                   class="filter-btn {{ !request('status') ? 'active' : '' }}">
                    <span><i class="fa-solid fa-paper-plane"></i> All Sent</span>
                    <span>{{ \App\Models\SentEmail::count() }}</span>
                </a>

                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => 'delivered'])) }}"
                   class="filter-btn {{ request('status') == 'delivered' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-circle-check" style="color: #4ade80;"></i> Delivered</span>
                    <span>{{ \App\Models\SentEmail::where('status','delivered')->count() }}</span>
                </a>
                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => 'not_delivered'])) }}"
                   class="filter-btn {{ request('status') == 'not_delivered' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-circle-check" style="color: #ff1010;"></i> Not Delivered</span>
                    <span>{{ \App\Models\SentEmail::where('status','not_delivered')->count() }}</span>
                </a>

                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => 'bounce'])) }}"
                   class="filter-btn {{ request('status') == 'bounce' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-circle-exclamation" style="color: #f87171;"></i> Bounced</span>
                    <span>{{ \App\Models\SentEmail::where('status','bounce')->count() }}</span>
                </a>

                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => 'deferred'])) }}"
                   class="filter-btn {{ request('status') == 'deferred' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-clock" style="color: #fbbf24;"></i> Deferred</span>
                    <span>{{ \App\Models\SentEmail::where('status','deferred')->count() }}</span>
                </a>

                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => 'spam_report'])) }}"
                   class="filter-btn {{ request('status') == 'spam_report' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-triangle-exclamation" style="color: #c084fc;"></i> Spam</span>
                    <span>{{ \App\Models\SentEmail::where('status','spam_report')->count() }}</span>
                </a>

                <a href="{{ route('sent-emails.index', array_merge(request()->query(), ['status' => 'blocked'])) }}"
                   class="filter-btn {{ request('status') == 'blocked' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-ban" style="color: #9ca3af;"></i> Blocked</span>
                    <span>{{ \App\Models\SentEmail::where('status','blocked')->count() }}</span>
                </a>
            </div>

            @if(request()->anyFilled(['search', 'status', 'message_id', 'to_email', 'from_email', 'subject', 'category', 'date_from', 'date_to']))
                <div style="margin-top: 20px;">
                    <a href="{{ route('sent-emails.index') }}" class="btn btn-secondary" style="width: 100%; font-size: 0.8rem;">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Clear Filters
                    </a>
                </div>
            @endif
<!-- Export Report  -->
<div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--glass-border);">

    <span class="filter-label">
        <i class="fa-solid fa-file-excel" style="color: #4ade80;"></i>
        Export Report
    </span>

    <form id="reportForm" action="{{ route('sent-emails.download') }}" method="POST"
          style="margin-top: 12px; display: flex; flex-direction: column; gap: 10px;">
        @csrf

        <div>
            <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 4px;">
                From
            </label>
            <input type="date" name="date_from"
                value="{{ now()->toDateString() }}"
                max="{{ now()->toDateString() }}"
                   class="search-input" style="padding: 8px 12px; font-size: 0.82rem;">
        </div>

        <div>
            <label style="font-size: 0.72rem; color: var(--text-muted); display: block; margin-bottom: 4px;">
                To
            </label>
            <input type="date" name="date_to"
                   value="{{ now()->toDateString() }}"
                   max="{{ now()->toDateString() }}"
                   class="search-input" style="padding: 8px 12px; font-size: 0.82rem;">
        </div>

    {{-- Quick presets --}}
<div style="margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">

    <button type="button"
            id="todayBtn"
            onclick="setReportDates(1, this)"
            class="report-filter-btn active">
        Today
    </button>

    <button type="button"
            onclick="setReportDates(7, this)"
            class="report-filter-btn">
        7 days
    </button>

    <button type="button"
            onclick="setReportDates(30, this)"
            class="report-filter-btn">
        30 days
    </button>
</div>

        <button type="submit" class="btn btn-primary"
                style="width: 100%; font-size: 0.82rem; padding: 10px; margin-top: 10px;">
            <i class="fa-solid fa-download"></i> Download Excel
        </button>

    </form>
</div>
        </div>
    </div>

{{-- List --}}
<div>

    {{-- ── Advanced Multi-Filter Panel ───────────────────────────────── --}}
    @php
        $advancedFilterCount = collect([
            'message_id', 'to_email', 'from_email', 'subject', 'category', 'date_from', 'date_to',
        ])->filter(fn ($key) => request()->filled($key))->count();
    @endphp

    <div class="card adv-filter-card">

        <div class="adv-filter-header" onclick="const p = document.getElementById('advFilterBody'); p.style.display = p.style.display === 'none' ? 'block' : 'none';">
            <h4>
                <i class="fa-solid fa-sliders" style="color: var(--accent-primary, #6366f1);"></i>
                Advanced Sear Filters
                @if($advancedFilterCount > 0)
                    <span class="adv-filter-count">{{ $advancedFilterCount }}</span>
                @endif
            </h4>
            <i class="fa-solid fa-chevron-down" style="color: var(--text-muted); font-size: 0.8rem;"></i>
        </div>

        <div id="advFilterBody" style="{{ $advancedFilterCount > 0 ? '' : 'display:none;' }}">
            <form action="{{ route('sent-emails.index') }}" method="GET">

                {{-- Preserve the quick sidebar search box if it was used --}}
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <div class="adv-filter-grid">

                    <div class="adv-filter-field">
                        <label for="message_id">Message ID</label>
                        <input type="text" id="message_id" name="message_id"
                               value="{{ request('message_id') }}"
                               placeholder="e.g. abc123xyz"
                               class="search-input">
                    </div>

                    <div class="adv-filter-field">
                        <label for="from_email">Sender Email</label>
                        <input type="text" id="from_email" name="from_email"
                               value="{{ request('from_email') }}"
                               placeholder="sender@domain.com"
                               class="search-input">
                    </div>

                    <div class="adv-filter-field">
                        <label for="to_email">Recipient Email</label>
                        <input type="text" id="to_email" name="to_email"
                               value="{{ request('to_email') }}"
                               placeholder="recipient@domain.com"
                               class="search-input">
                    </div>

                    <div class="adv-filter-field">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject"
                               value="{{ request('subject') }}"
                               placeholder="Subject contains..."
                               class="search-input">
                    </div>

                    <div class="adv-filter-field">
                        <label for="status">Status / Event</label>
                        <select id="status" name="status" class="search-input">
                            <option value="">Any status</option>
                            @foreach([
                                'processed'    => 'Processed / Queued',
                                'delivered'    => 'Delivered',
                                'not_delivered'=> 'Not Delivered',
                                'open'         => 'Opened',
                                'click'        => 'Clicked',
                                'bounce'       => 'Bounced',
                                'dropped'      => 'Dropped',
                                'deferred'     => 'Deferred',
                                'spam_report'  => 'Spam Report',
                                'blocked'      => 'Blocked',
                                'unsubscribe'  => 'Unsubscribed',
                            ] as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($categories) && $categories->isNotEmpty())
                        <div class="adv-filter-field">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="search-input">
                                <option value="">Any category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="adv-filter-field">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from"
                               value="{{ request('date_from') }}"
                               max="{{ now()->toDateString() }}"
                               class="search-input">
                    </div>

                    <div class="adv-filter-field">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to"
                               value="{{ request('date_to') }}"
                               max="{{ now()->toDateString() }}"
                               class="search-input">
                    </div>

                </div>

                <div class="adv-filter-actions">
                    <button type="submit" class="btn btn-primary" style="font-size: 0.85rem; padding: 10px 20px;">
                        <i class="fa-solid fa-magnifying-glass"></i> Apply Filters
                    </button>

                    @if($advancedFilterCount > 0)
                        <a href="{{ route('sent-emails.index', request()->only('search', 'status')) }}"
                           class="btn btn-secondary" style="font-size: 0.85rem; padding: 10px 20px;">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Clear Advanced Filters
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>
    {{-- ── End Advanced Filter Panel ─────────────────────────────────── --}}

    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="color: var(--text-muted); font-size: 0.9rem;">
            Showing
            <strong style="color: #fff;">
                {{ $emails->firstItem() ?? 0 }} – {{ $emails->lastItem() ?? 0 }}
            </strong>
            of
            <strong style="color: #fff;">
                {{ $emails->total() }}
            </strong>
            emails
        </div>

  
    </div>

    @if($emails->count() > 0)

        @foreach($emails as $email)

            @php
                $statusClass = 'status-' . ($email->status ?? '');
            @endphp

            <a href="{{ route('sent-emails.show', $email) }}"
               class="email-item {{ $statusClass }}">

                {{-- Status icon --}}
                <div style="width: 28px; flex-shrink: 0; text-align: center;">

                    @if($email->status === 'delivered')

                        <i class="fa-solid fa-circle-check"
                           style="color: #29bf33;"></i>

                    @elseif($email->status === 'open')

                        <i class="fa-solid fa-envelope-open"
                           style="color: #a78bfa;"></i>

                    @elseif($email->status === 'click')

                        <i class="fa-solid fa-arrow-pointer"
                           style="color: #818cf8;"></i>

                    @elseif($email->status === 'bounce')

                        <i class="fa-solid fa-circle-exclamation"
                           style="color: #f87171;"></i>

                    @elseif($email->status === 'not_delivered')

                        <i class="fa-solid fa-circle-xmark"
                           style="color: #ff1010;"></i>

                    @elseif($email->status === 'deferred')

                        <i class="fa-solid fa-clock"
                           style="color: #fbbf24;"></i>

                    @elseif($email->status === 'spam_report')

                        <i class="fa-solid fa-triangle-exclamation"
                           style="color: #c084fc;"></i>

                    @elseif($email->status === 'blocked')

                        <i class="fa-solid fa-ban"
                           style="color: #9ca3af;"></i>

                    @else

                        <i class="fa-solid fa-paper-plane"
                           style="color: var(--text-muted);"></i>

                    @endif

                </div>


                {{-- Recipient --}}
                <div class="email-meta-sender">

                    <span style="font-weight: 600; color: #fff; font-size: 0.95rem;">
                        {{ \Illuminate\Support\Str::before($email->to_email ?? 'Unknown', '@') }}
                    </span>

                    <span style="
                        font-size: 0.75rem;
                        color: var(--text-muted);
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        max-width: 180px;
                    ">
                        {{ $email->to_email ?? '—' }}
                    </span>

                </div>


                {{-- Subject + counts --}}
                <div class="email-main-content">

                    <div class="email-title">
                        {{ $email->subject ?: '(No Subject)' }}
                    </div>

                    <div style="margin-top: 5px;">

                        <span class="eng-pill {{ $email->opens > 0 ? 'active' : '' }}"
                              title="Opens">

                            <i class="fa-solid fa-eye"></i>
                            {{ $email->opens }}

                        </span>


                        <span class="eng-pill {{ $email->clicks > 0 ? 'active' : '' }}"
                              title="Clicks">

                            <i class="fa-solid fa-arrow-pointer"></i>
                            {{ $email->clicks }}

                        </span>


                        @if($email->bounces > 0)

                            <span class="eng-pill active"
                                  style="color: #f87171;"
                                  title="Bounces">

                                <i class="fa-solid fa-circle-exclamation"></i>
                                {{ $email->bounces }}

                            </span>

                        @endif


                        @if($email->spam_reports > 0)

                            <span class="eng-pill active"
                                  style="color: #c084fc;"
                                  title="Spam Reports">

                                <i class="fa-solid fa-shield-virus"></i>
                                {{ $email->spam_reports }}

                            </span>

                        @endif


                        @if($email->categories)

                            @foreach(array_slice((array) $email->categories, 0, 2) as $cat)

                                <span class="tag"
                                      style="font-size: 0.65rem; margin-left: 4px;">

                                    {{ $cat }}

                                </span>

                            @endforeach

                        @endif

                    </div>

                </div>


                {{-- Time + status --}}
                <div class="email-meta-right">

                    <span class="email-time" title="Original sent date">

                        {{ $email->sent_at
                            ? $email->sent_at->diffForHumans()
                            : 'N/A'
                        }}

                    </span>

                    {{-- Last activity is a distinct concept from sent_at —
                         only shown when it actually differs, so an email
                         that hasn't had any activity yet doesn't show a
                         redundant duplicate line. --}}
                    @if($email->has_recent_activity)
                        <span class="email-activity-time" title="Latest webhook activity">
                            <i class="fa-solid fa-bolt"></i>
                            {{ $email->last_event_at->diffForHumans() }}
                        </span>
                    @endif

                    @if($email->sg_message_id)
                        <span class="email-time" style="font-size: 0.7rem; opacity: 0.7;" title="Message ID">
                            <i class="fa-solid fa-hashtag"></i>{{ \Illuminate\Support\Str::limit($email->sg_message_id, 14) }}
                        </span>
                    @endif


                    @if($email->status)

                        <span class="status-badge {{ $email->status }}">

                            {{ $email->status_label }}

                        </span>

                    @endif

                </div>

            </a>

        @endforeach

    @else

        <div class="card"
             style="
                text-align: center;
                padding: 60px 20px;
                color: var(--text-muted);
             ">

            <i class="fa-solid fa-paper-plane"
               style="
                    font-size: 3rem;
                    margin-bottom: 16px;
                    opacity: 0.4;
                    display: block;
               "></i>

            <h3 style="
                color: #fff;
                margin-bottom: 8px;
                font-family: var(--font-display);
            ">
                No sent emails found
            </h3>

            <p style="font-size: 0.9rem;">
                No emails matched your search.
            </p>

        </div>

    @endif


    {{-- Pagination --}}
    @if($emails->hasPages())

        <div class="pagination-wrapper">

            {{-- Previous --}}
            @if($emails->onFirstPage())

                <span class="page-btn disabled">
                    <i class="fa-solid fa-chevron-left"
                       style="font-size: 0.75rem;"></i>
                </span>

            @else

                <a class="page-btn"
                   href="{{ $emails->previousPageUrl() }}">

                    <i class="fa-solid fa-chevron-left"
                       style="font-size: 0.75rem;"></i>

                </a>

            @endif


            {{-- Page numbers (windowed, with ellipsis, so we don't render hundreds of buttons) --}}
            @php

                $currentPage = $emails->currentPage();
                $lastPage    = $emails->lastPage();
                $onEachSide  = 2; // how many page numbers to show either side of the current page

            @endphp

            {{-- Leading page 1 + ellipsis --}}
            @if($currentPage > $onEachSide + 1)

                <a class="page-btn" href="{{ $emails->url(1) }}">1</a>

                @if($currentPage > $onEachSide + 2)
                    <span class="page-ellipsis">&hellip;</span>
                @endif

            @endif

            {{-- Window around the current page --}}
            @for($page = max(1, $currentPage - $onEachSide); $page <= min($lastPage, $currentPage + $onEachSide); $page++)

                @if($page == $currentPage)

                    <span class="page-btn active">
                        {{ $page }}
                    </span>

                @else

                    <a class="page-btn"
                       href="{{ $emails->url($page) }}">

                        {{ $page }}

                    </a>

                @endif

            @endfor

            {{-- Trailing ellipsis + last page --}}
            @if($currentPage < $lastPage - $onEachSide)

                @if($currentPage < $lastPage - $onEachSide - 1)
                    <span class="page-ellipsis">&hellip;</span>
                @endif

                <a class="page-btn" href="{{ $emails->url($lastPage) }}">{{ $lastPage }}</a>

            @endif


            {{-- Next --}}
            @if($emails->hasMorePages())

                <a class="page-btn"
                   href="{{ $emails->nextPageUrl() }}">

                    <i class="fa-solid fa-chevron-right"
                       style="font-size: 0.75rem;"></i>

                </a>

            @else

                <span class="page-btn disabled">

                    <i class="fa-solid fa-chevron-right"
                       style="font-size: 0.75rem;"></i>

                </span>

            @endif

        </div>

    @endif

</div>

</div>

@endsection
@section('scripts')
<script>
function setReportDates(days, button) {

    const to   = new Date();
    const from = new Date(Date.now() - (days - 1) * 86400000);
    const fmt  = d => d.toISOString().split('T')[0];

    const reportForm = document.getElementById('reportForm');
    reportForm.querySelector('[name="date_from"]').value = fmt(from);
    reportForm.querySelector('[name="date_to"]').value = fmt(to);


    // remove active from all
    document.querySelectorAll('.report-filter-btn')
        .forEach(btn => btn.classList.remove('active'));

    // add active to clicked
    button.classList.add('active');

}
</script>
@endsection