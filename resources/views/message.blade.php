@extends('layouts.app')

@section('title', $message->subject)
@section('email', $email->email)

@section('styles')
    .msg-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .msg-subject {
        font-size: 1.4rem;
        font-weight: 700;
        color: #f8fafc;
        margin-bottom: 8px;
    }

    .msg-meta {
        font-size: 0.85rem;
        color: #64748b;
    }
    .msg-meta strong { color: #e2e8f0; }

    .msg-body {
        background: #0f172a;
        border: 1px solid #334155;
        border-radius: 8px;
        padding: 24px;
        line-height: 1.7;
        color: #cbd5e1;
        word-wrap: break-word;
    }
@endsection

@section('content')
    <div class="msg-header">
        <div>
            <div class="msg-subject">{{ $message->subject }}</div>
            <div class="msg-meta">
                From: <strong>{{ $message->sender }}</strong>
                &mdash; {{ $message->created_at->format('M d, Y \a\t H:i') }}
            </div>
        </div>
        <a href="{{ route('inbox') }}" class="btn btn-secondary btn-sm">Back to Inbox</a>
    </div>

    <div class="msg-body">
        {!! nl2br(e($message->body)) !!}
    </div>
@endsection
