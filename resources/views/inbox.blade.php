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

    .message-row {
        display: block;
        text-decoration: none;
        color: inherit;
        background: #1e293b;
        border: 1px solid #334155;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 10px;
        transition: border-color 0.2s;
    }
    .message-row:hover { border-color: #38bdf8; }

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
@endsection

@section('content')
    <div class="inbox-header">
        <h2>Inbox</h2>
        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    @if($messages->isEmpty())
        <div class="empty-inbox">
            <div class="icon">&#9993;</div>
            <p>No messages yet</p>
            <p class="text-sm mt-2">Messages sent to <strong class="text-sky">{{ $email->email }}</strong> will appear here.</p>
        </div>
    @else
        @foreach($messages as $message)
            <a href="{{ route('message.show', $message->id) }}" class="message-row">
                <span class="message-time">{{ $message->created_at->diffForHumans() }}</span>
                <div class="message-sender">{{ $message->sender }}</div>
                <div class="message-subject">{{ $message->subject }}</div>
                <div class="message-preview">{{ Str::limit(strip_tags($message->body), 100) }}</div>
            </a>
        @endforeach
    @endif
@endsection
