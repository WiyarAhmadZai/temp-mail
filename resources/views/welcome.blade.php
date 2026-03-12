@extends('layouts.app')

@section('title', 'Free Temporary Email')
@section('email', $email->email)

@section('content')
<div class="flex flex-col items-center pt-12 pb-8 md:pt-20">
    <!-- Hero -->
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Temporary Email</h1>
    <p class="text-slate-400 mb-8 text-center">Disposable email address. No registration. Expires in {{ config('tempmail.expiration_hours') }}h.</p>

    <!-- Error banner -->
    @if(session('error'))
    <div class="w-full max-w-xl mb-4 rounded-lg border border-red-800 bg-red-950 px-4 py-3 text-sm text-red-200 text-center animate-fade-in">
        {{ session('error') }}
    </div>
    @endif

    <!-- Email card -->
    <div class="w-full max-w-xl rounded-2xl border border-dark-600 bg-dark-700 p-6 shadow-xl">
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-3">Your Temporary Email</label>

        <!-- Email display -->
        <button onclick="copyEmail('{{ $email->email }}')" class="group relative flex w-full items-center justify-between rounded-xl border border-dark-600 bg-dark-800 px-5 py-4 text-left transition hover:border-sky-500/50">
            <span class="text-lg md:text-xl font-semibold text-sky-400 break-all">{{ $email->email }}</span>
            <span class="ml-3 shrink-0 rounded-lg bg-dark-600 p-2 text-slate-400 transition group-hover:bg-sky-500/20 group-hover:text-sky-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </span>
        </button>

        <!-- Meta row -->
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
            <span>Created {{ $email->created_at->format('H:i, M d') }}</span>
            <span id="expiryTimer" class="text-amber-500">Expires {{ $email->expires_at->diffForHumans() }}</span>
        </div>

        <!-- Action buttons -->
        <div class="mt-5 flex flex-col sm:flex-row gap-3">
            <form action="{{ route('email.generate') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-500 active:scale-[0.98]">
                    <svg class="inline h-4 w-4 mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    New Email
                </button>
            </form>
            <a href="{{ route('inbox') }}" class="flex-1 flex items-center justify-center gap-2 rounded-xl border border-dark-600 bg-dark-700 px-5 py-3 text-sm font-semibold text-slate-200 transition hover:border-sky-500/50 hover:text-sky-400 active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                Inbox
                <span id="inboxBadge" class="rounded-full bg-sky-600 px-2 py-0.5 text-xs text-white {{ $messageCount > 0 ? '' : 'hidden' }}">{{ $messageCount }}</span>
            </a>
        </div>
    </div>

    <!-- How it works -->
    <div class="mt-12 w-full max-w-xl">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4 text-center">How it works</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-dark-600 bg-dark-700 p-4 text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-sky-500/10 text-sky-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-sm font-medium text-white">Get an email</p>
                <p class="text-xs text-slate-500 mt-1">Instant random address</p>
            </div>
            <div class="rounded-xl border border-dark-600 bg-dark-700 p-4 text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
                <p class="text-sm font-medium text-white">Receive emails</p>
                <p class="text-xs text-slate-500 mt-1">Messages appear live</p>
            </div>
            <div class="rounded-xl border border-dark-600 bg-dark-700 p-4 text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-medium text-white">Auto-deletes</p>
                <p class="text-xs text-slate-500 mt-1">After {{ config('tempmail.expiration_hours') }} hours</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function poll() {
        try {
            const res = await fetch("{{ route('api.poll') }}", { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (data.expired) { location.reload(); return; }

            const badge = document.getElementById('inboxBadge');
            if (data.message_count > 0) { badge.textContent = data.message_count; badge.classList.remove('hidden'); }
            else { badge.classList.add('hidden'); }

            document.getElementById('expiryTimer').textContent = 'Expires ' + data.expires_at;
        } catch (e) { console.error('Poll failed:', e); }
    }
    setInterval(poll, {{ config('tempmail.inbox_refresh_seconds') * 2000 }});
</script>
@endsection
