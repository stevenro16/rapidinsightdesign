@extends('layouts.portal')
@section('title', $inquiry->subject)
@section('page-title', 'Inquiry')
@section('breadcrumb', $inquiry->subject)

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Header --}}
    <div class="card">
        <a href="{{ route('inquiries.index') }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1 mb-3">
            <x-icon name="chevron-left" class="w-3.5 h-3.5" /> All inquiries
        </a>
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h1 class="text-xl font-display font-bold text-text">{{ $inquiry->subject }}</h1>
            <span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->statusLabel() }}</span>
        </div>
        <p class="text-xs text-muted mt-1">Started {{ $inquiry->created_at->format('M j, Y') }}</p>
    </div>

    {{-- Conversation --}}
    <div class="card space-y-4">
        <h3 class="font-semibold text-text">Conversation</h3>

        {{-- Original message (yours) --}}
        <div class="rounded-lg border-l-2 border-border bg-surface-2 p-4">
            <p class="text-sm text-text whitespace-pre-line leading-relaxed">{{ $inquiry->message }}</p>
            <p class="text-xs text-muted mt-2">You · {{ $inquiry->created_at->format('M j, Y · g:i A') }}</p>
        </div>

        @foreach($inquiry->notes as $note)
        @php $mine = $note->author_id === auth()->id(); @endphp
        <div class="rounded-lg border-l-2 {{ $mine ? 'border-border' : 'border-primary' }} bg-surface-2 p-4">
            <p class="text-sm text-text whitespace-pre-line leading-relaxed">{{ $note->body }}</p>
            <p class="text-xs text-muted mt-2">{{ $mine ? 'You' : 'RapidInsight Designs' }} · {{ $note->created_at->format('M j, Y · g:i A') }}</p>
        </div>
        @endforeach

        {{-- Reply --}}
        <form method="POST" action="{{ route('inquiries.notes.store', $inquiry) }}" class="space-y-2 pt-2 border-t border-border">
            <label class="label">Reply</label>
            <textarea name="body" rows="3" required class="input resize-none" placeholder="Add to the conversation…"></textarea>
            <div class="flex justify-end">
                <button class="btn-primary btn-sm gap-1.5"><x-icon name="chat" class="w-3.5 h-3.5" /> Send Reply</button>
            </div>
        </form>
    </div>
</div>
@endsection
