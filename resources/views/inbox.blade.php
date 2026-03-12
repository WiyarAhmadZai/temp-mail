@extends('layouts.app')

@section('title', 'Inbox')
@section('email', $email->email)

@section('content')
<!-- Expired banner -->
<div id="expiredBanner" class="hidden mb-4 rounded-lg border border-red-800 bg-red-950 px-4 py-3 text-sm text-red-200 text-center animate-fade-in">
    Your email has expired. <a href="{{ route('home') }}" class="text-sky-400 underline hover:text-sky-300">Get a new one</a>
</div>

<!-- Top bar: email + actions -->
<div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <a href="{{ route('home') }}" class="rounded-lg border border-dark-600 bg-dark-700 p-2 text-slate-400 hover:text-white hover:border-slate-500 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-lg font-bold text-white">Inbox</h1>
            <p class="text-xs text-slate-500">{{ $email->email }}</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <!-- Refresh indicator -->
        <div id="refreshIndicator" class="flex items-center gap-2 text-xs text-slate-500">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            </span>
            <span id="refreshText">Live</span>
        </div>
        <span class="text-xs text-slate-600" id="messageCount">{{ $messages->total() }} messages</span>
    </div>
</div>

<!-- Skeleton loader (hidden by default, shown during first load if needed) -->
<div id="skeletonLoader" class="hidden space-y-3">
    @for($i = 0; $i < 3; $i++)
    <div class="rounded-xl border border-dark-600 bg-dark-700 p-4">
        <div class="flex justify-between mb-3">
            <div class="skeleton-line h-4 w-32 rounded"></div>
            <div class="skeleton-line h-3 w-16 rounded"></div>
        </div>
        <div class="skeleton-line h-4 w-48 rounded mb-2"></div>
        <div class="skeleton-line h-3 w-full rounded"></div>
    </div>
    @endfor
</div>

<!-- Message list -->
<div id="messageList" class="space-y-2">
    @if($messages->isEmpty())
        <div id="emptyState" class="flex flex-col items-center justify-center py-20 text-slate-500">
            <div class="mb-4 rounded-full bg-dark-700 p-6">
                <svg class="h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
            </div>
            <p class="text-base font-medium text-slate-400">No messages yet</p>
            <p class="mt-1 text-sm">Waiting for emails to <span class="text-sky-400">{{ $email->email }}</span></p>
        </div>
    @else
        @foreach($messages as $message)
            <a href="{{ route('message.show', $message->id) }}" class="group block rounded-xl border border-dark-600 bg-dark-700 p-4 transition hover:border-sky-500/30 hover:bg-dark-700/80" data-id="{{ $message->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="h-8 w-8 shrink-0 rounded-full bg-sky-500/10 flex items-center justify-center text-sky-400 text-xs font-bold">
                                {{ strtoupper(substr($message->sender, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white truncate">{{ $message->sender }}</p>
                                <p class="text-sm text-slate-300 truncate group-hover:text-sky-400 transition">{{ $message->subject }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 truncate ml-10">{{ Str::limit(strip_tags($message->body), 120) }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-600 whitespace-nowrap">{{ $message->created_at->diffForHumans(short: true) }}</span>
                </div>
            </a>
        @endforeach
    @endif
</div>

<!-- Pagination -->
@if($messages->hasPages())
<div class="mt-6 flex items-center justify-center gap-2">
    @if($messages->onFirstPage())
        <span class="rounded-lg border border-dark-600 bg-dark-700 px-3 py-2 text-xs text-slate-600 cursor-not-allowed">Previous</span>
    @else
        <a href="{{ $messages->previousPageUrl() }}" class="rounded-lg border border-dark-600 bg-dark-700 px-3 py-2 text-xs text-slate-300 hover:border-sky-500/50 transition">Previous</a>
    @endif
    <span class="px-3 py-2 text-xs text-slate-500">{{ $messages->currentPage() }} / {{ $messages->lastPage() }}</span>
    @if($messages->hasMorePages())
        <a href="{{ $messages->nextPageUrl() }}" class="rounded-lg border border-dark-600 bg-dark-700 px-3 py-2 text-xs text-slate-300 hover:border-sky-500/50 transition">Next</a>
    @else
        <span class="rounded-lg border border-dark-600 bg-dark-700 px-3 py-2 text-xs text-slate-600 cursor-not-allowed">Next</span>
    @endif
</div>
@endif
@endsection

@section('scripts')
<script>
    const POLL_INTERVAL = {{ config('tempmail.inbox_refresh_seconds') * 1000 }};
    const pollUrl = "{{ route('api.poll') }}";
    let knownIds = new Set([...document.querySelectorAll('[data-id]')].map(el => parseInt(el.dataset.id)));

    function esc(text) { const d = document.createElement('div'); d.textContent = text; return d.innerHTML; }

    function renderMessage(msg) {
        const initial = msg.sender.charAt(0).toUpperCase();
        return `<a href="${esc(msg.url)}" class="group block rounded-xl border border-dark-600 bg-dark-700 p-4 transition hover:border-sky-500/30 animate-slide-up" data-id="${msg.id}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="h-8 w-8 shrink-0 rounded-full bg-sky-500/10 flex items-center justify-center text-sky-400 text-xs font-bold">${esc(initial)}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">${esc(msg.sender)}</p>
                            <p class="text-sm text-slate-300 truncate">${esc(msg.subject)}</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 truncate ml-10">${esc(msg.preview)}</p>
                </div>
                <span class="shrink-0 text-xs text-slate-600 whitespace-nowrap">${esc(msg.time)}</span>
            </div>
        </a>`;
    }

    async function poll() {
        const indicator = document.getElementById('refreshText');
        indicator.textContent = 'Checking...';

        try {
            const res = await fetch(pollUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();

            if (data.expired) { document.getElementById('expiredBanner').classList.remove('hidden'); indicator.textContent = 'Expired'; return; }

            document.getElementById('messageCount').textContent = data.message_count + ' messages';

            const list = document.getElementById('messageList');
            const empty = document.getElementById('emptyState');

            if (data.messages.length === 0 && !empty) {
                list.innerHTML = `<div id="emptyState" class="flex flex-col items-center justify-center py-20 text-slate-500">
                    <div class="mb-4 rounded-full bg-dark-700 p-6"><svg class="h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg></div>
                    <p class="text-base font-medium text-slate-400">No messages yet</p>
                    <p class="mt-1 text-sm">Waiting for emails to <span class="text-sky-400">${esc(data.email)}</span></p></div>`;
                knownIds.clear();
            } else {
                const newMsgs = data.messages.filter(m => !knownIds.has(m.id));
                if (newMsgs.length > 0) {
                    if (empty) empty.remove();
                    const first = list.querySelector('[data-id]');
                    newMsgs.reverse().forEach(msg => {
                        const tmp = document.createElement('div');
                        tmp.innerHTML = renderMessage(msg);
                        const el = tmp.firstElementChild;
                        first ? list.insertBefore(el, first) : list.appendChild(el);
                        knownIds.add(msg.id);
                    });
                }

                const serverIds = new Set(data.messages.map(m => m.id));
                document.querySelectorAll('[data-id]').forEach(el => {
                    const id = parseInt(el.dataset.id);
                    if (!serverIds.has(id)) { el.remove(); knownIds.delete(id); }
                });

                data.messages.forEach(msg => {
                    const timeEl = document.querySelector(`[data-id="${msg.id}"] .whitespace-nowrap`);
                    if (timeEl) timeEl.textContent = msg.time;
                });
            }
        } catch (e) { console.error('Poll failed:', e); }

        setTimeout(() => { indicator.textContent = 'Live'; }, 1000);
    }

    setInterval(poll, POLL_INTERVAL);
</script>
@endsection
