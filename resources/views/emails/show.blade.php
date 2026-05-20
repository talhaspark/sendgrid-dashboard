@extends('layouts.app')

@section('title', $email->subject)
@section('header-title', 'Email View')
@section('header-subtitle', 'Detailed payload, raw headers, and delivery events telemetry')

@section('styles')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .email-meta-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        margin-bottom: 20px;
    }

    .meta-item {
        display: flex;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .meta-label {
        width: 100px;
        color: var(--text-muted);
        font-weight: 500;
        flex-shrink: 0;
    }

    .meta-value {
        color: var(--text-main);
        word-break: break-all;
    }

    .body-card {
        background: #0d1117;
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 24px;
        margin-top: 24px;
        color: #e6edf3;
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Attachments styles */
    .attachment-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        text-decoration: none;
        color: var(--text-main);
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .attachment-badge:hover {
        background: rgba(99, 102, 241, 0.1);
        border-color: rgba(99, 102, 241, 0.3);
        transform: translateY(-1px);
    }

    /* Event Timeline style */
    .timeline {
        position: relative;
        padding-left: 24px;
        list-style: none;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: var(--glass-border);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-icon {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        color: white;
    }

    .timeline-content {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        padding: 12px;
    }

    .timeline-title {
        font-weight: 600;
        font-size: 0.85rem;
        color: #fff;
    }

    .timeline-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    /* Tab controls for payload */
    .tab-nav {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        margin-bottom: 16px;
    }

    .tab-link {
        padding: 8px 16px;
        cursor: pointer;
        color: var(--text-muted);
        font-weight: 500;
        font-size: 0.85rem;
        border-bottom: 2px solid transparent;
        transition: all 0.2s ease;
    }

    .tab-link.active {
        color: var(--accent-primary);
        border-bottom-color: var(--accent-primary);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    pre {
        background: #090d16;
        padding: 16px;
        border-radius: 10px;
        overflow-x: auto;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 0.8rem;
        border: 1px solid var(--glass-border);
        max-height: 400px;
        color: #38bdf8;
    }
</style>
@section('content')

<div style="margin-bottom: 20px;">
    <a href="{{ route('emails.index') }}" class="btn btn-secondary" style="font-size: 0.85rem;">
        <i class="fa-solid fa-arrow-left"></i> Back to Inbox
    </a>
</div>

<div class="detail-grid">
    
    <!-- Left Column: Email Core Content -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Header Info Card -->
        <div class="card">
            <div class="email-meta-header">
                <div>
                    <h2 style="font-family: var(--font-display); font-size: 1.4rem; color: #fff; margin-bottom: 8px;">
                        {{ $email->subject ?: '(No Subject)' }}
                    </h2>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                        @if($email->is_spam)
                            <span class="tag" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: var(--status-danger); font-weight: 600;">
                                <i class="fa-solid fa-triangle-exclamation"></i> SPAM (Score: {{ $email->spam_score }})
                            </span>
                        @endif
                        @if($email->labels)
                            @foreach($email->labels as $lbl)
                                <span class="tag" style="font-size: 0.75rem;">{{ $lbl }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
                
                <!-- Action Controls -->
                <div style="display: flex; gap: 10px;">
                    <form action="{{ route('emails.mark-read', $email) }}" method="POST">
                        @csrf
                        <input type="hidden" name="is_read" value="{{ $email->is_read ? '0' : '1' }}">
                        <button type="submit" class="btn btn-secondary" style="padding: 8px 14px; font-size: 0.8rem;" title="{{ $email->is_read ? 'Mark as Unread' : 'Mark as Read' }}">
                            <i class="fa-solid {{ $email->is_read ? 'fa-envelope' : 'fa-envelope-open' }}"></i>
                        </button>
                    </form>

                    <form action="{{ route('emails.spam', $email) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="padding: 8px 14px; font-size: 0.8rem; color: {{ $email->is_spam ? 'var(--status-danger)' : '' }}" title="{{ $email->is_spam ? 'Mark not Spam' : 'Flag Spam' }}">
                            <i class="fa-solid fa-shield-virus"></i>
                        </button>
                    </form>

                    <form action="{{ route('emails.destroy', $email) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this email?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary" style="padding: 8px 14px; font-size: 0.8rem; color: var(--status-danger); border-color: rgba(239, 68, 68, 0.2);" title="Delete Email">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sender & Recipients -->
            <div>
                <div class="meta-item">
                    <span class="meta-label">From:</span>
                    <span class="meta-value">
                        <strong>{{ $email->from_name }}</strong> &lt;{{ $email->from_address }}&gt;
                    </span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">To:</span>
                    <span class="meta-value">{{ $email->to_string }}</span>
                </div>
                @if($email->cc_addresses)
                    <div class="meta-item">
                        <span class="meta-label">Cc:</span>
                        <span class="meta-value">{{ implode(', ', $email->cc_addresses) }}</span>
                    </div>
                @endif
                <div class="meta-item">
                    <span class="meta-label">Date:</span>
                    <span class="meta-value">
                        {{ $email->received_at ? $email->received_at->format('l, F j, Y - g:i A') : 'N/A' }} 
                        <span style="color: var(--text-muted); font-size: 0.8rem; margin-left: 6px;">({{ $email->received_at ? $email->received_at->diffForHumans() : '' }})</span>
                    </span>
                </div>
                @if($email->sender_ip)
                    <div class="meta-item">
                        <span class="meta-label">Sender IP:</span>
                        <span class="meta-value" style="font-family: monospace;">{{ $email->sender_ip }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- HTML / Text Message Body Card -->
        <div class="card" style="padding: 24px;">
            <div class="tab-nav">
                <span class="tab-link active" onclick="switchBodyTab(this, 'formatted-body')">Formatted Email</span>
                <span class="tab-link" onclick="switchBodyTab(this, 'text-body')">Plain Text</span>
            </div>
            
            <div id="formatted-body" class="tab-content active">
                <div class="body-card">
                    {!! $email->sanitized_html !!}
                </div>
            </div>
            
            <div id="text-body" class="tab-content">
                <div style="background: #0d1117; padding: 20px; border-radius: 12px; font-family: monospace; white-space: pre-wrap; font-size: 0.9rem; border: 1px solid rgba(255, 255, 255, 0.05); color: #e6edf3;">{{ $email->text_body ?: 'No plain text content.' }}</div>
            </div>
        </div>

        <!-- Attachments Section -->
        @if($email->attachments->count() > 0)
            <div class="card" style="padding: 20px;">
                <div style="font-family: var(--font-display); font-weight: 600; margin-bottom: 14px; color: #fff;">
                    <i class="fa-solid fa-paperclip" style="color: var(--accent-secondary); margin-right: 6px;"></i>
                    Attachments ({{ $email->attachments->count() }})
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @foreach($email->attachments as $attachment)
                        <a href="{{ route('attachments.download', $attachment) }}" class="attachment-badge" target="_blank">
                            <i class="fa-solid {{ $attachment->icon_class }}" style="font-size: 1.1rem; color: var(--accent-primary);"></i>
                            <div style="display: flex; flex-direction: column; text-align: left;">
                                <span style="font-weight: 600; color: #fff;">{{ $attachment->original_filename }}</span>
                                <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $attachment->human_size }} &bull; {{ strtoupper($attachment->extension) }}</span>
                            </div>
                            <i class="fa-solid fa-arrow-down-to-line" style="margin-left: 6px; font-size: 0.8rem; color: var(--text-muted);"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Raw Webhook Payload & Headers -->
        <div class="card" style="padding: 20px;">
            <div class="tab-nav">
                <span class="tab-link active" onclick="switchPayloadTab(this, 'tab-headers')">Parsed SMTP Headers</span>
                <span class="tab-link" onclick="switchPayloadTab(this, 'tab-payload')">SendGrid Inbound JSON</span>
            </div>
            
            <div id="tab-headers" class="tab-content active">
                <pre><code>{{ json_encode($email->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
            
            <div id="tab-payload" class="tab-content">
                <pre><code>{{ json_encode($email->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>

    </div>

    <!-- Right Column: Delivery Telemetry & Events Timeline -->
    <div class="card" style="padding: 20px;">
        <div style="font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; color: #fff; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-heart-pulse" style="color: var(--status-success);"></i>
            SendGrid Events Timeline
        </div>
        
        @if($email->events->count() > 0)
            <ul class="timeline">
                @foreach($email->events as $event)
                    <li class="timeline-item">
                        <span class="timeline-icon" style="background-color: {{ $event->badge_color }}">
                            <i class="fa-solid {{ $event->icon }}"></i>
                        </span>
                        <div class="timeline-content">
                            <div class="timeline-title" style="display: flex; justify-content: space-between;">
                                <span>{{ ucfirst($event->event_type) }}</span>
                                <span class="tag" style="font-size: 0.6rem; padding: 1px 4px; background: rgba(255,255,255,0.03);">Webhook</span>
                            </div>
                            <div class="timeline-time">{{ $event->event_timestamp ? $event->event_timestamp->format('M d, Y - g:i A') : 'N/A' }}</div>
                            @if($event->ip || $event->useragent)
                                <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 6px; border-top: 1px solid rgba(255,255,255,0.02); padding-top: 4px;">
                                    @if($event->ip) <span>IP: {{ $event->ip }}</span> @endif
                                    @if($event->useragent) <br><span style="word-break: break-all;">UA: {{ \Illuminate\Support\Str::limit($event->useragent, 60) }}</span> @endif
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                <i class="fa-solid fa-hourglass-start" style="font-size: 2rem; display: block; margin-bottom: 12px; opacity: 0.4;"></i>
                No events received yet for this message id.
            </div>
        @endif
    </div>

</div>

@endsection

@section('scripts')
<script>
function switchBodyTab(element, tabId) {
    // formatted-body vs text-body
    const container = element.closest('.card');
    container.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
    element.classList.add('active');
    
    container.querySelector('#formatted-body').classList.remove('active');
    container.querySelector('#text-body').classList.remove('active');
    container.querySelector('#' + tabId).classList.add('active');
}

function switchPayloadTab(element, tabId) {
    const container = element.closest('.card');
    container.querySelectorAll('.tab-link').forEach(link => link.classList.remove('active'));
    element.classList.add('active');
    
    container.querySelector('#tab-headers').classList.remove('active');
    container.querySelector('#tab-payload').classList.remove('active');
    container.querySelector('#' + tabId).classList.add('active');
}
</script>
@endsection
