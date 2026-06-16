@extends('layouts.app')

@section('title', 'Sent Email Detail')
@section('header-title', 'Email Detail')
@section('header-subtitle', $sentEmail->subject ?? 'No Subject')

@section('content')

<div style="max-width: 800px;">

    {{-- Back button --}}
    <a href="{{ route('sent-emails.index') }}"
       style="display: inline-flex; align-items: center; gap: 8px;
              color: var(--text-muted); text-decoration: none; font-size: 0.875rem;
              margin-bottom: 24px;">
        <i class="fa-solid fa-arrow-left"></i> Back to Sent Emails
    </a>

    {{-- Header card --}}
    <div class="card" style="padding: 28px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
            <div>
                <h2 style="font-family: var(--font-display); font-size: 1.3rem;
                           color: #fff; margin: 0 0 8px;">
                    {{ $sentEmail->subject ?: '(No Subject)' }}
                </h2>
                <div style="color: var(--text-muted); font-size: 0.875rem; display: flex; gap: 20px; flex-wrap: wrap;">
                    <span><i class="fa-solid fa-paper-plane"></i>
                        From: <strong style="color: #fff;">{{ $sentEmail->from_email ?? '—' }}</strong>
                    </span>
                    <span><i class="fa-solid fa-user"></i>
                        To: <strong style="color: #fff;">{{ $sentEmail->to_email ?? '—' }}</strong>
                    </span>
                    <span><i class="fa-solid fa-clock"></i>
                        {{ $sentEmail->sent_at?->format('M d, Y h:i A') ?? 'N/A' }}
                    </span>
                </div>
            </div>

            @if($sentEmail->status)
                <span class="status-badge {{ $sentEmail->status }}"
                      style="flex-shrink: 0; padding: 6px 14px; border-radius: 20px;
                             font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
                             border: 1px solid transparent;">
                    {{ $sentEmail->status_label }}
                </span>
            @endif
        </div>
    </div>

    {{-- Stats row --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px;">
        @foreach([
            ['label' => 'Opens',        'value' => $sentEmail->opens,        'icon' => 'fa-eye',                 'color' => '#818cf8'],
            ['label' => 'Clicks',       'value' => $sentEmail->clicks,       'icon' => 'fa-arrow-pointer',       'color' => '#34d399'],
            ['label' => 'Bounces',      'value' => $sentEmail->bounces,      'icon' => 'fa-circle-exclamation',  'color' => '#f87171'],
            ['label' => 'Spam reports', 'value' => $sentEmail->spam_reports, 'icon' => 'fa-shield-virus',        'color' => '#c084fc'],
        ] as $stat)
        <div class="card" style="padding: 20px; text-align: center;">
            <i class="fa-solid {{ $stat['icon'] }}"
               style="font-size: 1.5rem; color: {{ $stat['color'] }}; margin-bottom: 8px; display: block;"></i>
            <div style="font-size: 1.6rem; font-weight: 700; color: #fff;">{{ $stat['value'] }}</div>
            <div style="font-size: 0.78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                {{ $stat['label'] }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Details --}}
    <div class="card" style="padding: 24px; margin-bottom: 20px;">
        <h3 style="font-family: var(--font-display); color: #fff; font-size: 1rem; margin: 0 0 16px;">
            Message Details
        </h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            @foreach([
                ['Message ID',   $sentEmail->sg_message_id],
                ['From',         $sentEmail->from_email],
                ['To',           $sentEmail->to_email],
                ['Subject',      $sentEmail->subject ?: '—'],
                ['Status',       $sentEmail->status],
                ['Sent at',      $sentEmail->sent_at?->format('M d, Y H:i:s') ?? 'N/A'],
                [   'Categories',   $sentEmail->categories ? implode(', ', $sentEmail->categories) : '—'],
            ] as [$label, $value])
            <tr style="border-bottom: 1px solid var(--glass-border);">
                <td style="padding: 10px 0; color: var(--text-muted); width: 140px;">{{ $label }}</td>
                <td style="padding: 10px 0; color: #fff; word-break: break-all;">{{ $value }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    {{-- Raw payload (collapsible) --}}
    @if($sentEmail->raw_payload)
    <div class="card" style="padding: 24px;">
        <details>
            <summary style="cursor: pointer; color: var(--text-muted); font-size: 0.875rem;
                            list-style: none; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-code"></i> Raw Payload (debug)
            </summary>
            <pre style="margin-top: 16px; font-size: 0.75rem; color: var(--text-muted);
                        overflow-x: auto; white-space: pre-wrap; word-break: break-all;">{{ json_encode($sentEmail->raw_payload, JSON_PRETTY_PRINT) }}</pre>
        </details>
    </div>
    @endif

</div>

@endsection