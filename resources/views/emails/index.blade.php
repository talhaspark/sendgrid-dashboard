@extends('layouts.app')

@section('title', 'Inbox')
@section('header-title', 'Email Inbox')
@section('header-subtitle', 'Manage inbound parse emails and raw headers')

@section('styles')
<style>
    .inbox-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .inbox-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Filters Card */
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 20px;
    }

    .filter-group:last-child {
        margin-bottom: 0;
    }

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

    /* Email Items List */
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
        position: relative;
    }

    .email-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        border-color: rgba(99, 102, 241, 0.2);
    }

    .email-item.unread {
        border-left: 4px solid var(--accent-primary);
        background: rgba(99, 102, 241, 0.02);
    }

    .email-star {
        font-size: 1.1rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .email-star.active {
        color: var(--status-warning);
    }

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

    .email-body-preview {
        font-size: 0.85rem;
        color: var(--text-muted);
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

    /* Custom pagination */
    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
    }

    .pagination-wrapper nav {
        display: flex;
        gap: 8px;
    }

    .pagination-wrapper a, .pagination-wrapper span {
        padding: 8px 16px;
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 8px;
        color: var(--text-muted);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .pagination-wrapper a:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
    }

    .pagination-wrapper .active span {
        background: var(--accent-primary);
        color: white;
        border-color: var(--accent-primary);
    }
</style>
@section('content')

<div class="inbox-layout">
    
    <!-- Sidebar Filters -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card" style="padding: 20px;">
            <form action="{{ route('emails.index') }}" method="GET" class="filter-group">
                <span class="filter-label">Search messages</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Subject, sender, or content..." class="search-input">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('has_attachments'))
                    <input type="hidden" name="has_attachments" value="{{ request('has_attachments') }}">
                @endif
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 0.85rem;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
            </form>
            
            <div class="filter-group" style="margin-top: 20px;">
                <span class="filter-label">Folder / Category</span>
                <a href="{{ route('emails.index', array_merge(request()->query(), ['status' => null])) }}" class="filter-btn {{ !request('status') ? 'active' : '' }}">
                    <span><i class="fa-solid fa-inbox"></i> All Inbound</span>
                    <span>{{ \App\Models\Email::count() }}</span>
                </a>
                <a href="{{ route('emails.index', array_merge(request()->query(), ['status' => 'unread'])) }}" class="filter-btn {{ request('status') == 'unread' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-envelope-open-text"></i> Unread</span>
                    <span>{{ $unreadCount }}</span>
                </a>
                <a href="{{ route('emails.index', array_merge(request()->query(), ['status' => 'starred'])) }}" class="filter-btn {{ request('status') == 'starred' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-star" style="color: var(--status-warning);"></i> Starred</span>
                    <span>{{ \App\Models\Email::starred()->count() }}</span>
                </a>
                <a href="{{ route('emails.index', array_merge(request()->query(), ['status' => 'spam'])) }}" class="filter-btn {{ request('status') == 'spam' ? 'active' : '' }}">
                    <span><i class="fa-solid fa-shield-virus" style="color: var(--status-danger);"></i> Spam</span>
                    <span>{{ \App\Models\Email::spam()->count() }}</span>
                </a>
            </div>

            <div class="filter-group" style="margin-top: 20px;">
                <span class="filter-label">Additional Filter</span>
                <a href="{{ route('emails.index', array_merge(request()->query(), ['has_attachments' => request('has_attachments') ? null : 1])) }}" class="filter-btn {{ request('has_attachments') ? 'active' : '' }}">
                    <span><i class="fa-solid fa-paperclip"></i> Has Attachments</span>
                    <i class="fa-solid {{ request('has_attachments') ? 'fa-check' : 'fa-plus' }}" style="font-size: 0.8rem;"></i>
                </a>
            </div>

            @if(request()->anyFilled(['search', 'status', 'has_attachments']))
                <div style="margin-top: 20px;">
                    <a href="{{ route('emails.index') }}" class="btn btn-secondary" style="width: 100%; font-size: 0.8rem;">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Clear All Filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Inbox List -->
    <div>
        <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div style="color: var(--text-muted); font-size: 0.9rem;">
                Showing <strong style="color: #fff;">{{ $emails->firstItem() ?? 0 }} - {{ $emails->lastItem() ?? 0 }}</strong> of <strong style="color: #fff;">{{ $emails->total() }}</strong> emails
            </div>
        </div>

        <div class="email-items-container">
            @forelse($emails as $email)
                <div class="email-item {{ !$email->is_read ? 'unread' : '' }}">
                    
                    <!-- Star status toggle -->
                    <i class="fa-solid fa-star email-star {{ $email->is_starred ? 'active' : '' }}" onclick="toggleStar(event, {{ $email->id }}, this)"></i>

                    <a href="{{ route('emails.show', $email) }}" style="display: contents; text-decoration: none; color: inherit;">
                        
                        <div class="email-meta-sender">
                            <span style="font-weight: 600; color: #fff; font-size: 0.95rem;">
                                {{ $email->from_name ?: \Illuminate\Support\Str::before($email->from_address, '@') }}
                            </span>
                            <span style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                                {{ $email->from_address }}
                            </span>
                        </div>

                        <div class="email-main-content">
                            <div class="email-title">
                                {{ $email->subject ?: '(No Subject)' }}
                            </div>
                            <div class="email-body-preview">
                                {{ $email->preview }}
                            </div>
                        </div>

                        <div class="email-meta-right">
                            <span class="email-time">{{ $email->received_at ? $email->received_at->diffForHumans() : 'N/A' }}</span>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                @if($email->attachment_count > 0)
                                    <i class="fa-solid fa-paperclip" style="color: var(--text-muted); font-size: 0.8rem;" title="{{ $email->attachment_count }} Attachments"></i>
                                @endif
                                @if($email->is_spam)
                                    <span class="tag" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: var(--status-danger); font-size: 0.65rem;">Spam</span>
                                @endif
                                @if($email->labels)
                                    @foreach(array_slice($email->labels, 0, 2) as $lbl)
                                        <span class="tag">{{ $lbl }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="card" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i class="fa-solid fa-envelope-open" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.4; display: block;"></i>
                    <h3 style="color: #fff; margin-bottom: 8px; font-family: var(--font-display);">No emails matched your filters</h3>
                    <p style="font-size: 0.9rem;">Try adjusting your search criteria or folder choice.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination-wrapper">
            {{ $emails->links() }}
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
function toggleStar(event, id, element) {
    event.preventDefault();
    event.stopPropagation();
    
    // Add glowing transition effect
    element.style.transform = 'scale(1.3)';
    setTimeout(() => element.style.transform = 'scale(1)', 200);

    fetch(`{{ url('emails') }}/${id}/star`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            if(data.is_starred) {
                element.classList.add('active');
            } else {
                element.classList.remove('active');
            }
        }
    });
}
</script>
@endsection
