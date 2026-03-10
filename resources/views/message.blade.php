@extends('layouts.app')

@section('title', $message->subject)
@section('email', $email->email)

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Back + meta header -->
    <div class="mb-6 flex items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex items-center gap-3 mb-3">
                <a href="{{ route('inbox') }}" class="rounded-lg border border-dark-600 bg-dark-700 p-2 text-slate-400 hover:text-white hover:border-slate-500 transition shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-xl font-bold text-white truncate">{{ $message->subject }}</h1>
            </div>

            <div class="ml-11 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="h-7 w-7 rounded-full bg-sky-500/10 flex items-center justify-center text-sky-400 text-xs font-bold">
                        {{ strtoupper(substr($message->sender, 0, 1)) }}
                    </span>
                    <span class="font-medium text-slate-200">{{ $message->sender }}</span>
                </div>
                <span class="text-slate-600">{{ $message->created_at->format('M d, Y \a\t H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Verification code detection -->
    @php
        $bodyText = strip_tags($message->body);
        preg_match_all('/\b(\d{4,8})\b/', $bodyText, $codeMatches);
        $codes = array_unique($codeMatches[1] ?? []);
        // Filter out likely non-codes (years, common numbers)
        $codes = array_filter($codes, function($code) {
            $num = (int) $code;
            return !($num >= 1900 && $num <= 2100) && strlen($code) >= 4;
        });
    @endphp

    @if(!empty($codes))
    <div class="mb-5 ml-11 rounded-xl border border-emerald-800/50 bg-emerald-950/30 p-4 animate-fade-in">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500 mb-2">Verification Code Detected</p>
        <div class="flex flex-wrap gap-2">
            @foreach(array_slice($codes, 0, 3) as $code)
            <button onclick="copyEmail('{{ $code }}')" class="group flex items-center gap-2 rounded-lg bg-emerald-600/20 border border-emerald-600/30 px-4 py-2 transition hover:bg-emerald-600/30">
                <span class="text-2xl font-mono font-bold tracking-widest text-emerald-400">{{ $code }}</span>
                <svg class="h-4 w-4 text-emerald-600 group-hover:text-emerald-400 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Message body -->
    <div class="ml-11 rounded-xl border border-dark-600 bg-dark-900 p-6 text-sm leading-relaxed text-slate-300" id="messageBody">
        {!! nl2br(e($message->body)) !!}
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Highlight verification codes in the message body
    (function() {
        const body = document.getElementById('messageBody');
        if (!body) return;

        body.innerHTML = body.innerHTML.replace(
            /\b(\d{4,8})\b/g,
            function(match) {
                const num = parseInt(match);
                if (num >= 1900 && num <= 2100) return match; // skip years
                if (match.length < 4) return match;
                return '<span class="inline-block rounded bg-sky-500/10 px-1.5 py-0.5 font-mono font-bold text-sky-400 cursor-pointer" onclick="copyEmail(\'' + match + '\')">' + match + '</span>';
            }
        );
    })();
</script>
@endsection
