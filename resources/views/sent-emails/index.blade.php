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

    .email-item.status-delivered   { border-left: 4px solid #22c55e; }
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
    .status-badge.bounce      { background: rgba(239,68,68,0.12);  border-color: rgba(239,68,68,0.25);  color: #f87171; }
    .status-badge.deferred    { background: rgba(245,158,11,0.12); border-color: rgba(245,158,11,0.25); color: #fbbf24; }
    .status-badge.spam_report { background: rgba(168,85,247,0.12); border-color: rgba(168,85,247,0.25); color: #c084fc; }
    .status-badge.blocked     { background: rgba(107,114,128,0.12);border-color: rgba(107,114,128,0.25);color: #9ca3af; }

    .eng-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.78rem;
        color: var(--text-muted);
        margin-right: 10px;
    }

    .eng-pill.active { color: #fff; }

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
    /* ─────────────────────────────────────────────────────────────────── */
</style>
@endsection

@section('content')

<div class="inbox-layout">

    {{-- Sidebar --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card" style="padding: 20px;">

            <form action="{{ route('sent-emails.index') }}" method="GET" class="filter-group">
                <span class="filter-label">Search</span>
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

            @if(request()->anyFilled(['search', 'status']))
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

    <form action="{{ route('sent-emails.download') }}" method="POST"
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

        <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                Showing <strong style="color: #fff;">{{ $emails->firstItem() ?? 0 }} – {{ $emails->lastItem() ?? 0 }}</strong>
                of <strong style="color: #fff;">{{ $emails->total() }}</strong> emails
            </div>
        </div>

        @forelse($emails as $email)
            @php $statusClass = 'status-' . ($email->status ?? ''); @endphp

            <a href="{{ route('sent-emails.show', $email) }}"
               class="email-item {{ $statusClass }}">

                {{-- Status icon --}}
                <div style="width: 28px; flex-shrink: 0; text-align: center;">
                    @if($email->status === 'delivered')
                        <i class="fa-solid fa-circle-check" style="color: #4ade80;"></i>
                    @elseif($email->status === 'bounce')
                        <i class="fa-solid fa-circle-exclamation" style="color: #f87171;"></i>
                    @elseif($email->status === 'not_delivered')
                        <i class="fa-solid fa-circle-xmark" style="color: #ff1010;"></i>
                    @elseif($email->status === 'deferred')
                        <i class="fa-solid fa-clock" style="color: #fbbf24;"></i>
                    @elseif($email->status === 'spam_report')
                        <i class="fa-solid fa-triangle-exclamation" style="color: #c084fc;"></i>
                    @elseif($email->status === 'blocked')
                        <i class="fa-solid fa-ban" style="color: #9ca3af;"></i>
                    @else
                        <i class="fa-solid fa-paper-plane" style="color: var(--text-muted);"></i>
                    @endif
                </div>

                {{-- Recipient --}}
                <div class="email-meta-sender">
                    <span style="font-weight: 600; color: #fff; font-size: 0.95rem;">
                        {{ \Illuminate\Support\Str::before($email->to_email ?? 'Unknown', '@') }}
                    </span>
                    <span style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                        {{ $email->to_email ?? '—' }}
                    </span>
                </div>

                {{-- Subject + counts --}}
                <div class="email-main-content">
                    <div class="email-title">
                        {{ $email->subject ?: '(No Subject)' }}
                    </div>
                    <div style="margin-top: 5px;">
                        <span class="eng-pill {{ $email->opens > 0 ? 'active' : '' }}" title="Opens">
                            <i class="fa-solid fa-eye"></i> {{ $email->opens }}
                        </span>
                        <span class="eng-pill {{ $email->clicks > 0 ? 'active' : '' }}" title="Clicks">
                            <i class="fa-solid fa-arrow-pointer"></i> {{ $email->clicks }}
                        </span>
                        @if($email->bounces > 0)
                            <span class="eng-pill active" style="color: #f87171;" title="Bounces">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $email->bounces }}
                            </span>
                        @endif
                        @if($email->spam_reports > 0)
                            <span class="eng-pill active" style="color: #c084fc;" title="Spam Reports">
                                <i class="fa-solid fa-shield-virus"></i> {{ $email->spam_reports }}
                            </span>
                        @endif
                        @if($email->categories)
                            @foreach(array_slice((array) $email->categories, 0, 2) as $cat)
                                <span class="tag" style="font-size: 0.65rem; margin-left: 4px;">{{ $cat }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Time + badge --}}
                <div class="email-meta-right">
                    <span class="email-time">
                        {{ $email->sent_at ? $email->sent_at->diffForHumans() : 'N/A' }}
                    </span>
                    @if($email->status)
                        <span class="status-badge {{ $email->status }}">
                            {{ ucfirst(str_replace('_', ' ', $email->status)) }}
                        </span>
                    @endif
                </div>

            </a>
        @empty
            <div class="card" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                <i class="fa-solid fa-paper-plane" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.4; display: block;"></i>
                <h3 style="color: #fff; margin-bottom: 8px; font-family: var(--font-display);">No sent emails found</h3>
                <p style="font-size: 0.9rem;">Run <code>php artisan sendgrid:sync</code> to pull in your email logs.</p>
            </div>
        @endforelse

        @if($emails->hasPages())
        <div class="pagination-wrapper">

            {{-- Previous --}}
            @if($emails->onFirstPage())
                <span class="page-btn disabled">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i>
                </span>
            @else
                <a class="page-btn" href="{{ $emails->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}">
                    <i class="fa-solid fa-chevron-left" style="font-size: 0.75rem;"></i>
                </a>
            @endif

            {{-- Page numbers with smart windowing --}}
            @php
                $currentPage  = $emails->currentPage();
                $lastPage     = $emails->lastPage();
                $window       = 2; // pages on each side of current
                $showFirst    = 1;
                $showLast     = $lastPage;
            @endphp

            @for($page = 1; $page <= $lastPage; $page++)
                @php
                    $nearCurrent = abs($page - $currentPage) <= $window;
                    $isEdge      = $page === $showFirst || $page === $showLast;
                    $show        = $nearCurrent || $isEdge;
                    $prevPage    = $page - 1;
                    $showEllipsisBefore = !$nearCurrent && !$isEdge &&
                                         ($page === $showFirst + 1 || ($page > $showFirst + 1 && abs($prevPage - $currentPage) > $window && $prevPage !== $showFirst));
                @endphp

                @if($show)
                    @if($page == $currentPage)
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a class="page-btn"
                           href="{{ $emails->url($page) }}&{{ http_build_query(request()->except('page')) }}">
                            {{ $page }}
                        </a>
                    @endif
                @elseif(!$show && ($page === $showFirst + 1 || ($currentPage - $page === $window + 1) || ($page - $currentPage === $window + 1) || ($page === $showLast - 1)))
                    <span class="page-ellipsis">…</span>
                @endif
            @endfor

            {{-- Next --}}
            @if($emails->hasMorePages())
                <a class="page-btn" href="{{ $emails->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}">
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
                </a>
            @else
                <span class="page-btn disabled">
                    <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i>
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

    document.querySelector('[name="date_from"]').value = fmt(from);
    document.querySelector('[name="date_to"]').value = fmt(to);


    // remove active from all
    document.querySelectorAll('.report-filter-btn')
        .forEach(btn => btn.classList.remove('active'));

    // add active to clicked
    button.classList.add('active');
    
}
</script>
@endsection