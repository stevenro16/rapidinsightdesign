@extends('layouts.portal')
@section('title', 'Inquiry')
@section('page-title', 'Inquiry Detail')
@section('breadcrumb', $inquiry->subject)

@section('content')
<div class="grid lg:grid-cols-3 gap-6 max-w-5xl">
    {{-- Message --}}
    <div class="lg:col-span-2 card">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h2 class="font-display font-semibold text-lg text-[var(--color-text)] mb-1">{{ $inquiry->subject }}</h2>
                <p class="text-sm text-[var(--color-muted)]">
                    From <span class="text-[var(--color-text)]">{{ $inquiry->name }}</span>
                    &lt;{{ $inquiry->email }}&gt;
                    · {{ $inquiry->created_at->format('M j, Y \a\t g:ia') }}
                </p>
            </div>
            <span class="badge {{ $inquiry->statusBadgeClass() }} shrink-0">{{ $inquiry->status }}</span>
        </div>
        <div class="bg-[var(--color-surface-2)] rounded-lg p-4 text-sm text-[var(--color-text)] leading-relaxed whitespace-pre-wrap border border-[var(--color-border)]">{{ $inquiry->message }}</div>
    </div>

    {{-- Actions --}}
    <div class="space-y-4">
        <div class="card">
            <p class="label mb-3">Update Status</p>
            <form method="POST" action="/staff/inquiries/{{ $inquiry->id }}">
                @csrf
                @method('PATCH')
                <select name="status" class="select mb-3">
                    @foreach(['new', 'in_progress', 'resolved'] as $status)
                    <option value="{{ $status }}" {{ $inquiry->status === $status ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary w-full justify-center">
                    Save Status
                </button>
            </form>
        </div>

        @if($inquiry->user)
        <div class="card">
            <p class="label mb-3">Customer</p>
            <a href="/staff/customers/{{ $inquiry->user->id }}" class="flex items-center gap-3 hover:bg-[var(--color-surface-2)] -m-1 p-1 rounded-lg transition-colors">
                <div class="w-9 h-9 rounded-full bg-[var(--color-primary-glow)] border border-[var(--color-primary)] flex items-center justify-center text-sm font-semibold text-[var(--color-primary)]">
                    {{ substr($inquiry->user->name, 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-[var(--color-text)]">{{ $inquiry->user->name }}</p>
                    <p class="text-xs text-[var(--color-muted)]">View profile →</p>
                </div>
            </a>
        </div>
        @endif

        <a href="/staff/inquiries" class="btn-ghost w-full justify-center">
            <x-icon name="chevron-left" class="w-4 h-4" />
            Back to Inquiries
        </a>
    </div>
</div>
@endsection
