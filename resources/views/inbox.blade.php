@extends('layouts.app')

@section('title', 'Inbox')
@section('email', $email->email)

@section('styles')
    .inbox-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .inbox-header h2 { font-size: 1.5rem; color: #f8fafc; }

    .header-right { display: flex; align-items: center; gap: 12px; }

    .poll-status {
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .poll-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4ade80;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .message-row {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 10px;
        transition: border-color 0.2s, opacity 0.3s;
    }
    .message-row:hover { border-color: #38bdf8; }
    .message-row.new { animation: slideIn 0.3s ease-out; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message-sender {
        font-weight: 600;
        color: #f8fafc;
        margin-bottom: 4px;
        font-size: 0.95rem;
    }

    .message-subject {
        color: #94a3b8;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }

    .message-preview {
        color: #64748b;
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .message-time {
        float: right;
        font-size: 0.75rem;
        color: #64748b;
    }

    .empty-inbox {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }
    .empty-inbox .icon { font-size: 3rem; margin-bottom: 16px; }
    .empty-inbox p { font-size: 1rem; }

    .expired-banner {
        background: #7f1d1d;
        border: 1px solid #dc2626;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
        text-align: center;
        font-size: 0.9rem;
        color: #fecaca;
        display: none;
    }
@endsection

@section('content')
    <div class="expired-banner" id="expiredBanner">
        Your email has expired. <a href="{{ route('home') }}" style="color:#38bdf8;text-decoration:underline;">Get a new one</a>
    </div>

    <div class="inbox-header">
        <h2>Inbox <span id="messageCount" class="text-muted text-sm">({{ $messages->count() }})</span></h2>
        <div class="header-right">
            <span class="poll-status">
                <span class="poll-dot" id="pollDot"></span>
                <span id="pollText">Auto-refresh on</span>
            </span>
            <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <div id="messageList">
        @if($messages->isEmpty())
            <div class="empty-inbox" id="emptyState">
                <div class="icon">&#9993;</div>
                <p>No messages yet</p>
                <p class="text-sm mt-2">Messages sent to <strong class="text-sky">{{ $email->email }}</strong> will appear here.</p>
            </div>
        @else
            @foreach($messages as $message)
                <a href="{{ route('message.show', $message->id) }}" class="message-row" data-id="{{ $message->id }}">
                    <span class="message-time">{{ $message->created_at->diffForHumans() }}</span>
                    <div class="message-sender">{{ $message->sender }}</div>
                    <div class="message-subject">{{ $message->subject }}</div>
                    <div class="message-preview">{{ Str::limit(strip_tags($message->body), 100) }}</div>
                </a>
            @endforeach
        @endif
    </div>

    <script>
        const POLL_INTERVAL = 5000;
        const pollUrl = "{{ route('api.poll') }}";
        let knownIds = new Set(
            [...document.querySelectorAll('.message-row[data-id]')].map(el => parseInt(el.dataset.id))
        );

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderMessage(msg) {
            return `<a href="${escapeHtml(msg.url)}" class="message-row new" data-id="${msg.id}">
                <span class="message-time">${escapeHtml(msg.time)}</span>
                <div class="message-sender">${escapeHtml(msg.sender)}</div>
                <div class="message-subject">${escapeHtml(msg.subject)}</div>
                <div class="message-preview">${escapeHtml(msg.preview)}</div>
            </a>`;
        }

        async function poll() {
            try {
                const res = await fetch(pollUrl, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();

                // Handle expired email
                if (data.expired) {
                    document.getElementById('expiredBanner').style.display = 'block';
                    return;
                }

                // Update message count
                document.getElementById('messageCount').textContent = `(${data.message_count})`;

                const list = document.getElementById('messageList');
                const empty = document.getElementById('emptyState');

                if (data.messages.length === 0 && !empty) {
                    list.innerHTML = `<div class="empty-inbox" id="emptyState">
                        <div class="icon">&#9993;</div>
                        <p>No messages yet</p>
                        <p class="text-sm mt-2">Messages sent to <strong class="text-sky">${escapeHtml(data.email)}</strong> will appear here.</p>
                    </div>`;
                    knownIds.clear();
                    return;
                }

                // Check for new messages
                const newMsgs = data.messages.filter(m => !knownIds.has(m.id));
                if (newMsgs.length > 0) {
                    // Remove empty state if present
                    if (empty) empty.remove();

                    // Prepend new messages (they come sorted latest-first)
                    const firstExisting = list.querySelector('.message-row');
                    newMsgs.reverse().forEach(msg => {
                        const temp = document.createElement('div');
                        temp.innerHTML = renderMessage(msg);
                        const el = temp.firstElementChild;
                        if (firstExisting) {
                            list.insertBefore(el, firstExisting);
                        } else {
                            list.appendChild(el);
                        }
                        knownIds.add(msg.id);
                    });
                }

                // Remove messages that no longer exist on server
                const serverIds = new Set(data.messages.map(m => m.id));
                document.querySelectorAll('.message-row[data-id]').forEach(el => {
                    const id = parseInt(el.dataset.id);
                    if (!serverIds.has(id)) {
                        el.remove();
                        knownIds.delete(id);
                    }
                });

                // Update timestamps on existing messages
                data.messages.forEach(msg => {
                    const el = document.querySelector(`.message-row[data-id="${msg.id}"] .message-time`);
                    if (el) el.textContent = msg.time;
                });

            } catch (e) {
                console.error('Poll failed:', e);
            }
        }

        setInterval(poll, POLL_INTERVAL);
    </script>
@endsection
