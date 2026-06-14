@extends('layouts.portal')
@section('title', 'Inquiry')
@section('page-title', 'Inquiry')
@section('breadcrumb', $inquiry->subject)

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Conversation --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="font-display font-semibold text-lg text-text">{{ $inquiry->subject }}</h2>
                    <p class="text-sm text-muted mt-0.5">
                        From <span class="text-text">{{ $inquiry->name }}</span> &lt;{{ $inquiry->email }}&gt; · {{ $inquiry->created_at->format('M j, Y \a\t g:i A') }}
                    </p>
                </div>
                <span class="badge {{ $inquiry->statusBadgeClass() }} shrink-0">{{ $inquiry->statusLabel() }}</span>
            </div>
        </div>

        {{-- Thread --}}
        <div class="card space-y-4">
            <h3 class="font-semibold text-text">Conversation</h3>

            {{-- Original message --}}
            <div class="rounded-lg border-l-2 border-border bg-surface-2 p-4">
                <p class="text-sm text-text whitespace-pre-line leading-relaxed">{{ $inquiry->message }}</p>
                <p class="text-xs text-muted mt-2">{{ $inquiry->name }} · {{ $inquiry->created_at->format('M j, Y · g:i A') }}</p>
            </div>

            @foreach($inquiry->notes as $note)
            @php
                $isCustomer = $inquiry->user_id && $note->author_id === $inquiry->user_id;
                $border = $isCustomer ? 'border-primary' : ($note->visible_to_customer ? 'border-blue-500/60' : 'border-amber-500/60');
                $tag    = $isCustomer ? ['Customer', 'badge-green'] : ($note->visible_to_customer ? ['Shared', 'badge-blue'] : ['Internal', 'badge-amber']);
            @endphp
            <div class="rounded-lg border-l-2 {{ $border }} bg-surface-2 p-4">
                <p class="text-sm text-text whitespace-pre-line leading-relaxed">{{ $note->body }}</p>
                <p class="text-xs text-muted mt-2">
                    {{ $note->author?->name ?? 'System' }}
                    <span class="badge {{ $tag[1] }} text-[10px] ml-1">{{ $tag[0] }}</span>
                    · {{ $note->created_at->format('M j, Y · g:i A') }}
                </p>
            </div>
            @endforeach

            {{-- Add note --}}
            <form method="POST" action="{{ route('staff.inquiries.notes.store', $inquiry) }}" class="space-y-2 pt-2 border-t border-border">
                <label class="label">Add a note</label>
                <textarea name="body" rows="3" required class="input resize-none" placeholder="Reply to the customer, or jot an internal note…"></textarea>
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <label class="flex items-center gap-2 text-sm text-muted cursor-pointer">
                        <input type="checkbox" name="visible_to_customer" value="1" class="rounded"> Visible to customer (sends a reply)
                    </label>
                    <button class="btn-primary btn-sm gap-1.5"><x-icon name="chat" class="w-3.5 h-3.5" /> Post</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="card">
            <p class="label mb-3">Status</p>
            <form method="POST" action="{{ route('staff.inquiries.update', $inquiry) }}">
                @csrf @method('PATCH')
                <select name="status" class="select mb-3">
                    @foreach(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $inquiry->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button class="btn-primary w-full justify-center">Save Status</button>
            </form>
        </div>

        @if($inquiry->user && $inquiry->user->isCustomer())
        <div class="card space-y-3">
            <p class="label">Customer</p>
            <a href="{{ route('staff.customers.show', $inquiry->user) }}" class="flex items-center gap-3 hover:bg-surface-2 -m-1 p-1 rounded-lg transition-colors">
                <div class="w-9 h-9 rounded-full bg-primary-glow border border-primary flex items-center justify-center text-sm font-semibold text-primary shrink-0">{{ strtoupper(substr($inquiry->user->name, 0, 1)) }}</div>
                <div class="min-w-0"><p class="text-sm font-medium text-text truncate">{{ $inquiry->user->name }}</p><p class="text-xs text-muted">View profile →</p></div>
            </a>
            <form method="POST" action="{{ route('staff.customers.agreements.store', $inquiry->user) }}">
                @csrf
                <input type="hidden" name="title" value="{{ $inquiry->subject }}">
                <button class="btn-primary w-full justify-center gap-1.5"><x-icon name="document" class="w-4 h-4" /> Create Agreement</button>
            </form>
            <p class="text-[11px] text-muted">Starts a draft agreement for this customer, prefilled from the inquiry.</p>
        </div>
        @else
        <div class="card">
            <p class="text-xs text-muted">This inquiry isn't linked to a customer account, so an agreement can't be created from it yet. It links automatically if they sign up with <span class="text-text">{{ $inquiry->email }}</span>.</p>
        </div>
        @endif

        <a href="{{ route('staff.inquiries.index') }}" class="btn-ghost w-full justify-center gap-1.5"><x-icon name="chevron-left" class="w-4 h-4" /> Back to Inquiries</a>
    </div>
</div>
@endsection
