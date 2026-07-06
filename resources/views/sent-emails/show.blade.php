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



                    <span>
                        <i class="fa-solid fa-clock"></i>
                        {{ $sentEmail->sent_at?->format('M d, Y h:i A') ?? 'N/A' }}
                    </span>


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
            ['Status',$sentEmail->status],
            ['Sent at',$sentEmail->sent_at?->format('M d, Y H:i:s') ?? 'N/A'],
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
    <!-- Right Column: Delivery Telemetry & Events Timeline -->

            {{-- ── SendGrid Events Timeline ──────────────────────────────── --}}
            <div style="position:sticky; top:20px;">
    <div class="card" style="padding: 20px;">
    
        <div style="display: flex; justify-content: space-between;
                    align-items: center; margin-bottom: 20px;">
            <div style="font-family: var(--font-display); font-size: 1rem;
                        font-weight: 600; color: #fff; display: flex;
                        align-items: center; gap: 8px;">
                <i class="fa-solid fa-heart-pulse" style="color: #4ade80;"></i>
                SendGrid Events Timeline
            </div>
            <span style="font-size: 0.75rem; color: var(--text-muted);">
                {{ $sentEmail->events->count() }} event(s)
            </span>
        </div>
    
        @if($sentEmail->events->count() > 0)
            <ul style="position: relative; padding-left: 28px; list-style: none; margin: 0;">
    
                {{-- vertical line --}}
                <li style="position: absolute; left: 9px; top: 8px; bottom: 8px;
                           width: 2px; background: var(--glass-border); list-style: none;"></li>
    
                @foreach($sentEmail->events as $event)
                    @php
                        $dotColor = match($event->event_type) {
                            'delivered'              => '#22c55e',
                            'open'                   => '#8b5cf6',
                            'click'                  => '#6366f1',
                            'bounce'                 => '#ef4444',
                            'deferred'               => '#f59e0b',
                            'spamreport','spam_report'=> '#dc2626',
                            'dropped'                => '#f97316',
                            'unsubscribe'            => '#6b7280',
                            'processed'              => '#3b82f6',
                            default                  => '#6b7280',
                        };
                        $dotIcon = match($event->event_type) {
                            'delivered'              => 'fa-check',
                            'open'                   => 'fa-eye',
                            'click'                  => 'fa-arrow-pointer',
                            'bounce'                 => 'fa-exclamation',
                            'deferred'               => 'fa-clock',
                            'spamreport','spam_report'=> 'fa-shield',
                            'dropped'                => 'fa-xmark',
                            'unsubscribe'            => 'fa-user-minus',
                            'processed'              => 'fa-cog',
                            default                  => 'fa-circle',
                        };
                    @endphp
    
                    <li style="position: relative; margin-bottom: 18px;">
    
                        {{-- dot --}}
                        <span style="position: absolute; left: -28px; top: 6px;
                                     width: 20px; height: 20px; border-radius: 50%;
                                     background: {{ $dotColor }}; display: flex;
                                     align-items: center; justify-content: center;
                                     font-size: 0.58rem; color: #fff;">
                            <i class="fa-solid {{ $dotIcon }}"></i>
                        </span>
    
                        {{-- body --}}
                        <div style="background: rgba(255,255,255,0.02);
                                    border: 1px solid var(--glass-border);
                                    border-radius: 10px; padding: 12px 14px;">
    
                            <div style="font-weight: 600; font-size: 0.88rem; color: #fff;
                                        display: flex; justify-content: space-between; align-items: center;">
                                <span>{{ ucfirst($event->event_type) }}</span>
                                <span style="font-size: 0.62rem; color: var(--text-muted);
                                             font-weight: 400; background: rgba(255,255,255,0.04);
                                             padding: 2px 6px; border-radius: 4px;">Webhook</span>
                            </div>
    
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                {{ $event->event_timestamp?->format('M d, Y — h:i:s A') ?? 'N/A' }}
                                @if($event->event_timestamp)
                                    <span style="margin-left: 6px; opacity: 0.5;">
                                        ({{ $event->event_timestamp->diffForHumans() }})
                                    </span>
                                @endif
                            </div>
    
                            {{-- click URL --}}
                            @if($event->event_type === 'click' && $event->url)
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 8px;
                                        padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.04);
                                        word-break: break-all;">
                                <i class="fa-solid fa-link" style="margin-right: 4px;"></i>
                                {{ \Illuminate\Support\Str::limit($event->url, 65) }}
                            </div>
                            @endif
    
                            {{-- open/click: IP + user agent --}}
                            @if(in_array($event->event_type, ['open','click']) && ($event->ip || $event->useragent))
                            <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 8px;
                                        padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.04);
                                        line-height: 1.7; word-break: break-all;">
                                @if($event->ip)
                                    <div><i class="fa-solid fa-globe" style="margin-right: 4px;"></i>{{ $event->ip }}</div>
                                @endif
                                @if($event->useragent)
                                    <div><i class="fa-solid fa-display" style="margin-right: 4px;"></i>{{ \Illuminate\Support\Str::limit($event->useragent, 68) }}</div>
                                @endif
                            </div>
                            @endif
    
                            {{-- bounce/drop reason --}}
                            @if(in_array($event->event_type, ['bounce','dropped','deferred']) && $event->reason)
                            <div style="font-size: 0.72rem; color: #fca5a5; margin-top: 8px;
                                        padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.04);
                                        word-break: break-all;">
                                <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i>
                                {{ $event->reason }}
                            </div>
                            @endif
    
                        </div>
                    </li>
                @endforeach
            </ul>
    
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