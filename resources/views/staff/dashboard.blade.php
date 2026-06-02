@extends('layouts.portal')
@section('title', 'Staff Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    @foreach([
        ['New Inquiries',    $stats['new_inquiries'],   'inbox', 'badge-green'],
        ['Open Inquiries',   $stats['open_inquiries'],  'inbox', 'badge-amber'],
        ['Total Customers',  $stats['total_customers'], 'users', 'badge-blue'],
    ] as [$label, $value, $icon, $badge])
    <div class="card flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-[var(--color-surface-2)] border border-[var(--color-border)]">
            <x-icon name="{{ $icon }}" class="w-6 h-6 text-[var(--color-primary)]" />
        </div>
        <div>
            <p class="text-2xl font-display font-bold text-[var(--color-text)]">{{ $value }}</p>
            <p class="text-xs text-[var(--color-muted)]">{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Recent inquiries --}}
<div class="card p-0">
    <div class="p-4 border-b border-[var(--color-border)] flex items-center justify-between">
        <h2 class="font-display font-semibold text-[var(--color-text)]">Recent Inquiries</h2>
        <a href="/staff/inquiries" class="text-sm text-[var(--color-primary)] hover:underline">View all</a>
    </div>
    @if($recentInquiries->isEmpty())
    <div class="p-8 text-center text-[var(--color-muted)] text-sm">No inquiries yet.</div>
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
            @foreach($recentInquiries as $inquiry)
            <tr>
                <td>
                    <p class="font-medium text-[var(--color-text)]">{{ $inquiry->name }}</p>
                    <p class="text-xs text-[var(--color-muted)]">{{ $inquiry->email }}</p>
                </td>
                <td class="text-[var(--color-muted)]">{{ Str::limit($inquiry->subject, 40) }}</td>
                <td><span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->status }}</span></td>
                <td class="text-[var(--color-muted)] text-xs">{{ $inquiry->created_at->diffForHumans() }}</td>
                <td>
                    <a href="/staff/inquiries/{{ $inquiry->id }}" class="text-[var(--color-primary)] hover:underline text-sm">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
