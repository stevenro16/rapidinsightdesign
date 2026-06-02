@extends('layouts.portal')
@section('title', 'Inquiries')
@section('page-title', 'Inquiries')

@section('content')
<div class="card p-0">
    <div class="p-4 border-b border-[var(--color-border)] flex items-center gap-3 flex-wrap">
        <p class="text-sm text-[var(--color-muted)]">{{ $inquiries->total() }} total</p>
    </div>
    @if($inquiries->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="inbox" class="w-10 h-10 text-[var(--color-border)] mx-auto mb-3" />
        <p class="text-[var(--color-muted)]">No inquiries yet.</p>
    </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($inquiries as $inquiry)
            <tr>
                <td>
                    <p class="font-medium text-[var(--color-text)]">{{ $inquiry->name }}</p>
                    <p class="text-xs text-[var(--color-muted)]">{{ $inquiry->email }}</p>
                </td>
                <td class="text-[var(--color-muted)]">{{ Str::limit($inquiry->subject, 50) }}</td>
                <td><span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->status }}</span></td>
                <td class="text-xs text-[var(--color-muted)]">{{ $inquiry->created_at->format('M j, Y') }}</td>
                <td>
                    <a href="/staff/inquiries/{{ $inquiry->id }}" class="btn-ghost btn-sm">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $inquiries->links() }}</div>
    @endif
</div>
@endsection
