@extends('layouts.app')

@section('title', 'Sent Email Detail')
@section('header-title', 'Email Detail')
@section('header-subtitle', $sentEmail->subject ?? 'No Subject')

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

    /* Status badge colors — this page didn't previously load these rules
       at all (they only existed in the index page's own style block,
       which doesn't apply here), so the badge rendered unstyled. */
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
</style>
@endsection
@section('content')
    {{-- Back button --}}
    <a href="{{ route('sent-emails.index') }}"
       style="display: inline-flex; align-items: center; gap: 8px;
              color: var(--text-muted); text-decoration: none;
              font-size: 0.875rem; margin-bottom: 24px;">

        <i class="fa-solid fa-arrow-left"></i>
        Back to Sent Emails

    </a>
<div class="detail-grid">

    <div style="display: flex; flex-direction: column; gap: 24px;">

    {{-- Header card --}}
    <div class="card" style="padding:28px;margin-bottom:20px;">

        <div style="
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
        ">


            <div>

                <h2 style="
                    font-family:var(--font-display);
                    font-size:1.3rem;
                    color:#fff;
                    margin:0 0 8px;
                ">
                    {{ $sentEmail->subject ?: '(No Subject)' }}
                </h2>



                <div style="
                    color:var(--text-muted);
                    font-size:.875rem;
                    display:flex;
                    gap:20px;
                    flex-wrap:wrap;
                ">


                    <span>
                        <i class="fa-solid fa-paper-plane"></i>
                        From:
                        <strong style="color:#fff">
                            {{ $sentEmail->from_email ?? '—' }}
                        </strong>
                    </span>



                    <span>
                        <i class="fa-solid fa-user"></i>
                        To:
                        <strong style="color:#fff">
                            {{ $sentEmail->to_email ?? '—' }}
                        </strong>
                    </span>



                    <span title="Original sent date — never changes when new events arrive">
                        <i class="fa-solid fa-clock"></i>
                        Sent: {{ $sentEmail->sent_at?->format('M d, Y h:i A') ?? 'N/A' }}
                    </span>

                    {{-- Last activity is a distinct concept from sent_at —
                         only shown when there's activity after the send. --}}
                    @if($sentEmail->has_recent_activity)
                        <span title="Latest webhook event received">
                            <i class="fa-solid fa-bolt" style="color:#818cf8;"></i>
                            Last activity: {{ $sentEmail->last_event_at->format('M d, Y h:i A') }}
                            ({{ $sentEmail->last_event_at->diffForHumans() }})
                        </span>
                    @endif


                </div>


            </div>



            @if($sentEmail->status)

            <span class="status-badge {{ $sentEmail->status }}"
                  style="
                    padding:6px 14px;
                    border-radius:20px;
                    font-size:.75rem;
                    font-weight:600;
                    text-transform:uppercase;
                  ">

                {{ $sentEmail->status_label }}

            </span>

            @endif


        </div>

    </div>



    {{-- Stats --}}
    <div style="
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:16px;
        margin-bottom:20px;
    ">

    @foreach([
        ['label'=>'Opens','value'=>$sentEmail->opens,'icon'=>'fa-eye'],
        ['label'=>'Clicks','value'=>$sentEmail->clicks,'icon'=>'fa-arrow-pointer'],
        ['label'=>'Bounces','value'=>$sentEmail->bounces,'icon'=>'fa-circle-exclamation'],
        ['label'=>'Spam','value'=>$sentEmail->spam_reports,'icon'=>'fa-shield-virus'],
    ] as $stat)


        <div class="card" style="
            padding:20px;
            text-align:center;
        ">

            <i class="fa-solid {{ $stat['icon'] }}"
               style="
                font-size:1.5rem;
                margin-bottom:8px;
                display:block;
                color:#818cf8;
               "></i>

            <div style="
                font-size:1.6rem;
                font-weight:700;
                color:#fff;
            ">
                {{ $stat['value'] ?? 0 }}
            </div>
            <div style="
                font-size:.78rem;
                color:var(--text-muted);
                text-transform:uppercase;
            ">
                {{ $stat['label'] }}
            </div>
        </div>
    @endforeach
    </div>

    {{-- Failure Details --}}
    @if($sentEmail->failure_reason)
    <div class="card"
         style="
            padding:24px;
            margin-bottom:20px;
            border-left:4px solid #ef4444;
         ">
        <div style="
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:18px;
        ">
            <i class="fa-solid fa-triangle-exclamation"
               style="
                color:#ef4444;
                font-size:1.3rem;
               "></i>


            <h3 style="
                margin:0;
                color:#fff;
            ">
                Delivery Failure
            </h3>
        </div>
        <div style="
            background:rgba(239,68,68,.08);
            padding:16px;
            border-radius:10px;
        ">
            <p style="margin:0 0 12px;color:#fff;">
                <strong style="color:#fca5a5;">
                    Reason:
                </strong>
                {{ $sentEmail->failure_reason }}
            </p>
            @if($sentEmail->failure_response)
            <p style="margin:0;color:#fff;">
                <strong style="color:#fca5a5;">
                    Response:
                </strong>
                {{ $sentEmail->failure_response }}
            </p>
            @endif
        </div>
    </div>
    @endif

    {{-- Details --}}
    <div class="card" style="padding:24px;margin-bottom:20px;">


        <h3 style="
            color:#fff;
            margin-bottom:16px;
        ">
            Message Details
        </h3>



        <table style="
            width:100%;
            border-collapse:collapse;
        ">


        @foreach([
            ['Message ID',$sentEmail->sg_message_id],
            ['From',$sentEmail->from_email],
            ['To',$sentEmail->to_email],
            ['Subject',$sentEmail->subject ?: '—'],
            ['Status',$sentEmail->status_label],
            ['Sent at',$sentEmail->sent_at?->format('M d, Y H:i:s') ?? 'N/A'],
            ['Last activity',$sentEmail->last_event_at?->format('M d, Y H:i:s') ?? '—'],
            ['Categories',$sentEmail->categories ? implode(', ', $sentEmail->categories) : '—'],
        ] as [$label,$value])


        <tr style="
            border-bottom:1px solid var(--glass-border);
        ">


            <td style="
                padding:10px 0;
                color:var(--text-muted);
                width:140px;
            ">
                {{ $label }}
            </td>


            <td style="
                padding:10px 0;
                color:#fff;
                word-break:break-all;
            ">
                {{ $value }}
            </td>


        </tr>


        @endforeach


        </table>


    </div>
    {{-- Raw payload --}}
    @if($sentEmail->raw_payload)

    <div class="card" style="padding:24px;">


        <details>


            <summary style="
                cursor:pointer;
                color:var(--text-muted);
            ">


                <i class="fa-solid fa-code"></i>

                Raw Payload


            </summary>



            <pre style="
                margin-top:16px;
                color:var(--text-muted);
                font-size:.75rem;
                overflow:auto;
            ">{{ json_encode($sentEmail->raw_payload, JSON_PRETTY_PRINT) }}</pre>



        </details>


    </div>
    @endif


</div>
    <!-- Right Column: Delivery Telemetry & Event History -->

            {{-- Event History: grouped by event type, expandable, with
                 a per-event JSON detail view. Mirrors SendGrid's own
                 Email Activity "Event History" panel. --}}
            <div style="position:sticky; top:20px;">
    <div class="card" style="padding: 20px;">

        <div style="display: flex; justify-content: space-between;
                    align-items: center; margin-bottom: 20px;">
            <div style="font-family: var(--font-display); font-size: 1rem;
                        font-weight: 600; color: #fff; display: flex;
                        align-items: center; gap: 8px;">
                <i class="fa-solid fa-heart-pulse" style="color: #4ade80;"></i>
                Event History
            </div>
            <span style="font-size: 0.75rem; color: var(--text-muted);">
                {{ $sentEmail->events->count() }} event(s)
            </span>
        </div>

        @if($sentEmail->events->count() > 0)

            @php
                // Group stored events by type so repeats (e.g. 5 opens)
                // collapse into a single row with a count and an
                // expandable per-instance list, same as SendGrid's own
                // Email Activity view.
                $eventGroups = $sentEmail->events
                    ->groupBy('event_type')
                    ->map(function ($events) {
                        return (object) [
                            'event_type' => $events->first()->event_type,
                            'count' => $events->count(),
                            'latest' => $events->sortByDesc('event_timestamp')->first(),
                            'instances' => $events->sortByDesc('event_timestamp')->values(),
                        ];
                    })
                    ->sortByDesc(fn ($g) => $g->latest->event_timestamp)
                    ->values();

                // Milestone strip: processed -> delivered -> first
                // meaningful event, oldest first, only showing steps
                // that actually happened for this message.
                $milestoneOrder = ['processed', 'delivered', 'open', 'click', 'bounce', 'dropped', 'deferred', 'spam_report', 'blocked', 'unsubscribe'];
                $milestones = collect($milestoneOrder)
                    ->filter(fn ($type) => $eventGroups->firstWhere('event_type', $type))
                    ->map(function ($type) use ($eventGroups) {
                        $group = $eventGroups->firstWhere('event_type', $type);
                        return (object) [
                            'event_type' => $type,
                            'at' => $group->instances->sortBy('event_timestamp')->first()->event_timestamp,
                        ];
                    })
                    ->sortBy('at')
                    ->values();

                $labelMap = [
                    'processed' => 'Processed', 'delivered' => 'Delivered', 'open' => 'Opened',
                    'click' => 'Clicked', 'bounce' => 'Bounced', 'dropped' => 'Dropped',
                    'deferred' => 'Deferred', 'spam_report' => 'Spam', 'blocked' => 'Blocked',
                    'unsubscribe' => 'Unsubscribed',
                ];
                $iconMap = [
                    'delivered' => 'fa-check', 'open' => 'fa-eye', 'click' => 'fa-arrow-pointer',
                    'bounce' => 'fa-exclamation', 'deferred' => 'fa-clock', 'spam_report' => 'fa-shield',
                    'dropped' => 'fa-xmark', 'blocked' => 'fa-ban', 'unsubscribe' => 'fa-user-minus',
                    'processed' => 'fa-cog',
                ];
                $colorMap = [
                    'delivered' => '#22c55e', 'open' => '#8b5cf6', 'click' => '#6366f1',
                    'bounce' => '#ef4444', 'deferred' => '#f59e0b', 'spam_report' => '#dc2626',
                    'dropped' => '#f97316', 'blocked' => '#6b7280', 'unsubscribe' => '#6b7280',
                    'processed' => '#3b82f6',
                ];
            @endphp

            {{-- Milestone strip --}}
            @if($milestones->count() > 1)
            <div style="display:flex; align-items:flex-start; margin-bottom: 16px; overflow-x:auto; padding-bottom: 4px;">
                @foreach($milestones as $m)
                    <div style="display:flex; flex-direction:column; align-items:center; min-width: 72px; flex-shrink:0;">
                        <div style="width:26px; height:26px; border-radius:50%; background:{{ $colorMap[$m->event_type] ?? '#6b7280' }};
                                    display:flex; align-items:center; justify-content:center; color:#fff; font-size:0.7rem;">
                            <i class="fa-solid {{ $iconMap[$m->event_type] ?? 'fa-circle' }}"></i>
                        </div>
                        <span style="font-size:0.68rem; color:#fff; margin-top:6px; white-space:nowrap;">{{ $labelMap[$m->event_type] ?? ucfirst($m->event_type) }}</span>
                        <span style="font-size:0.6rem; color:var(--text-muted); white-space:nowrap;">{{ $m->at->format('n/j g:i A') }}</span>
                    </div>
                    @if(!$loop->last)
                        <div style="flex:1; height:2px; background:var(--glass-border); margin: 13px 4px 0; min-width: 14px;"></div>
                    @endif
                @endforeach
            </div>

            @php
                $firstMilestone = $milestones->first()->at;
                $lastMilestone = $milestones->last()->at;
                $spanSeconds = $firstMilestone->diffInSeconds($lastMilestone);
            @endphp
            <div style="text-align:center; margin-bottom: 20px;">
                <span style="display:inline-flex; align-items:center; gap:6px; background: rgba(99,102,241,0.1);
                             border:1px solid rgba(99,102,241,0.25); color:#a5b4fc; padding:4px 12px;
                             border-radius:20px; font-size:0.7rem;">
                    <i class="fa-solid fa-circle-info"></i>
                    First to latest event:
                    {{ $spanSeconds < 60 ? 'Less than a minute' : $firstMilestone->diffForHumans($lastMilestone, true) }}
                </span>
            </div>
            @endif

            {{-- Expand all toggle --}}
            <div style="display:flex; justify-content:flex-end; margin-bottom: 10px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:0.75rem; color: var(--text-muted); cursor:pointer;">
                    <input type="checkbox" id="expandAllEvents" onchange="toggleAllEventGroups(this.checked)">
                    Expand all events
                </label>
            </div>

            {{-- Grouped event list --}}
            <div style="border:1px solid var(--glass-border); border-radius:10px; overflow:hidden;">
                @foreach($eventGroups as $gi => $group)
                    <div style="{{ !$loop->last ? 'border-bottom: 1px solid var(--glass-border);' : '' }}">

                        {{-- group header row --}}
                        <div onclick="toggleEventGroup({{ $gi }})"
                             style="display:flex; align-items:center; justify-content:space-between;
                                    padding: 12px 14px; cursor:pointer; background: rgba(255,255,255,0.015);">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="width:10px; height:10px; border-radius:50%; background:{{ $colorMap[$group->event_type] ?? '#6b7280' }}; flex-shrink:0;"></span>
                                <div>
                                    <div style="font-size:0.85rem; color:#fff; font-weight:600;">
                                        {{ $labelMap[$group->event_type] ?? ucfirst($group->event_type) }}
                                        @if($group->count > 1)
                                            <span style="color:var(--text-muted); font-weight:400;">({{ $group->count }})</span>
                                        @endif
                                    </div>
                                    <div style="font-size:0.7rem; color:var(--text-muted);">
                                        Latest: {{ $group->latest->event_timestamp?->format('n/j/Y g:i:s A') }}
                                    </div>
                                </div>
                            </div>
                            <i id="eventGroupChevron-{{ $gi }}" class="fa-solid fa-chevron-down" style="color:var(--text-muted); font-size:0.7rem; transition: transform 0.15s ease;"></i>
                        </div>

                        {{-- individual instances --}}
                        <div id="eventGroupBody-{{ $gi }}" style="display:none; padding: 0 14px 12px 30px;">
                            @foreach($group->instances as $ii => $event)
                                <div style="padding: 8px 0; {{ !$loop->last ? 'border-bottom:1px dashed var(--glass-border);' : '' }}">
                                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <span style="font-size:0.78rem; color:#fff;">
                                            {{ $labelMap[$group->event_type] ?? ucfirst($group->event_type) }} event #{{ $group->count - $ii }}
                                            at {{ $event->event_timestamp?->format('n/j/Y g:i:s A') }}
                                        </span>
                                        <button type="button" onclick="toggleEventJson({{ $gi }}, {{ $ii }})"
                                                style="background:none; border:none; color:#818cf8; font-size:0.75rem; cursor:pointer; padding:0;">
                                            View details
                                        </button>
                                    </div>

                                    @if($event->event_type === 'click' && $event->url)
                                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; word-break: break-all;">
                                            <i class="fa-solid fa-link" style="margin-right: 4px;"></i>{{ \Illuminate\Support\Str::limit($event->url, 60) }}
                                        </div>
                                    @endif
                                    @if(in_array($event->event_type, ['open','click']) && ($event->ip || $event->useragent))
                                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; line-height:1.6; word-break: break-all;">
                                            @if($event->ip)<div><i class="fa-solid fa-globe" style="margin-right:4px;"></i>{{ $event->ip }}</div>@endif
                                            @if($event->useragent)<div><i class="fa-solid fa-display" style="margin-right:4px;"></i>{{ \Illuminate\Support\Str::limit($event->useragent, 60) }}</div>@endif
                                        </div>
                                    @endif
                                    @if(in_array($event->event_type, ['bounce','dropped','deferred','blocked']) && $event->reason)
                                        <div style="font-size: 0.7rem; color: #fca5a5; margin-top: 4px; word-break: break-all;">
                                            <i class="fa-solid fa-circle-info" style="margin-right:4px;"></i>{{ $event->reason }}
                                        </div>
                                    @endif

                                    {{-- per-event JSON, from the raw_payload we already store on every event row --}}
                                    <div id="eventJson-{{ $gi }}-{{ $ii }}" style="display:none; margin-top:8px;">
                                        <pre style="background:#0d1117; border:1px solid var(--glass-border); border-radius:8px;
                                                    padding:12px; font-size:0.68rem; color:#93e6b3; overflow:auto; max-height:260px; margin:0;">{{ json_encode($event->raw_payload, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <div style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                <i class="fa-solid fa-hourglass-half"
                   style="font-size: 2rem; display: block; margin-bottom: 12px; opacity: 0.25;"></i>
                <p style="font-size: 0.85rem; margin: 0;">No webhook events yet.</p>
                <p style="font-size: 0.75rem; margin-top: 6px; opacity: 0.5;">
                    Events appear here in real time<br>when the recipient opens or clicks.
                </p>
            </div>
        @endif

    </div>
    </div>
</div>



@endsection
@section('scripts')
<script>
function toggleEventGroup(i) {
    const body = document.getElementById('eventGroupBody-' + i);
    const chevron = document.getElementById('eventGroupChevron-' + i);
    if (!body) return;
    const isOpen = body.style.display === 'block';
    body.style.display = isOpen ? 'none' : 'block';
    if (chevron) chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

function toggleAllEventGroups(expand) {
    document.querySelectorAll('[id^="eventGroupBody-"]').forEach(function (el) {
        el.style.display = expand ? 'block' : 'none';
    });
    document.querySelectorAll('[id^="eventGroupChevron-"]').forEach(function (el) {
        el.style.transform = expand ? 'rotate(180deg)' : 'rotate(0deg)';
    });
}

function toggleEventJson(groupIndex, instanceIndex) {
    const el = document.getElementById('eventJson-' + groupIndex + '-' + instanceIndex);
    if (!el) return;
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>
@endsection