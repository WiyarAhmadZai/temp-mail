@extends('layouts.app')

@section('title', 'Home')
@section('email', $email->email)

@section('styles')
    .hero { text-align: center; padding-top: 60px; }
    .hero h1 { font-size: 2.5rem; font-weight: 700; color: #f8fafc; margin-bottom: 8px; }
    .hero .subtitle { color: #94a3b8; margin-bottom: 40px; }

    .email-address {
        font-size: 1.5rem;
        font-weight: 600;
        color: #38bdf8;
        word-break: break-all;
        padding: 16px;
        background: #0f172a;
        border-radius: 8px;
        border: 1px solid #334155;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .email-address:hover { border-color: #38bdf8; }

    .copy-hint { font-size: 0.75rem; color: #64748b; margin-top: 8px; }
    .copy-hint.copied { color: #4ade80; }

    .email-meta {
        display: flex;
        justify-content: space-between;
        margin-top: 16px;
        font-size: 0.8rem;
        color: #64748b;
    }

    .actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }
    .actions form, .actions a, .actions button { flex: 1; }
    .actions .btn { width: 100%; }

    .inbox-badge {
        display: inline-block;
        background: #2563eb;
        color: white;
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 99px;
        margin-left: 6px;
    }
@endsection

@section('content')
    <div class="hero">
        <h1>TempMail</h1>
        <p class="subtitle">Your temporary email address is ready</p>

        <div class="card">
            <div class="text-sm text-muted" style="text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;">
                Your Temporary Email
            </div>
            <div class="email-address" id="emailAddress" onclick="copyEmail()">
                {{ $email->email }}
            </div>
            <p class="copy-hint" id="copyHint">Click to copy</p>

            <div class="email-meta">
                <span>Created: {{ $email->created_at->format('H:i, M d') }}</span>
                <span class="text-orange" id="expiryTimer">Expires: {{ $email->expires_at->diffForHumans() }}</span>
            </div>
        </div>

        <div class="actions">
            <form action="{{ route('email.generate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Generate New Email</button>
            </form>
            <a href="{{ route('inbox') }}" class="btn btn-secondary">
                Inbox
                <span class="inbox-badge" id="inboxBadge" style="{{ $messageCount > 0 ? '' : 'display:none' }}">{{ $messageCount }}</span>
            </a>
            <button class="btn btn-secondary" onclick="copyEmail()">Copy</button>
        </div>
    </div>

    <script>
        function copyEmail() {
            const email = document.getElementById('emailAddress').textContent.trim();
            navigator.clipboard.writeText(email).then(() => {
                const hint = document.getElementById('copyHint');
                hint.textContent = 'Copied!';
                hint.classList.add('copied');
                setTimeout(() => {
                    hint.textContent = 'Click to copy';
                    hint.classList.remove('copied');
                }, 2000);
            });
        }

        // Poll for new messages and update badge + expiry timer
        async function poll() {
            try {
                const res = await fetch("{{ route('api.poll') }}", {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                const data = await res.json();

                if (data.expired) {
                    location.reload();
                    return;
                }

                // Update inbox badge
                const badge = document.getElementById('inboxBadge');
                if (data.message_count > 0) {
                    badge.textContent = data.message_count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }

                // Update expiry timer
                document.getElementById('expiryTimer').textContent = 'Expires: ' + data.expires_at;
            } catch (e) {
                console.error('Poll failed:', e);
            }
        }

        setInterval(poll, {{ config('tempmail.inbox_refresh_seconds') * 2000 }});
    </script>
@endsection
