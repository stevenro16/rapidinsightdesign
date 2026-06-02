@extends('layouts.portal')
@section('title', $user->name)
@section('page-title', $user->name)
@section('breadcrumb', 'Customer Profile')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Profile card --}}
    <div class="card space-y-4">
        <div class="w-14 h-14 rounded-full bg-[var(--color-primary-glow)] border border-[var(--color-primary)] flex items-center justify-center text-xl font-display font-bold text-[var(--color-primary)]">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div>
            <h2 class="font-display font-semibold text-lg text-[var(--color-text)]">{{ $user->name }}</h2>
            <p class="text-sm text-[var(--color-muted)]">{{ $user->email }}</p>
        </div>
        @if($user->company)
        <div>
            <p class="label">Company</p>
            <p class="text-sm text-[var(--color-text)]">{{ $user->company }}</p>
        </div>
        @endif
        <div>
            <p class="label">Member Since</p>
            <p class="text-sm text-[var(--color-text)]">{{ $user->created_at->format('M j, Y') }}</p>
        </div>
        @if($user->notes)
        <div>
            <p class="label">Internal Notes</p>
            <p class="text-sm text-[var(--color-muted)]">{{ $user->notes }}</p>
        </div>
        @endif
    </div>

    <div class="lg:col-span-2 space-y-6">
        {{-- ShowRoom access --}}
        <div class="card p-0">
            <div class="p-4 border-b border-[var(--color-border)]">
                <h3 class="font-semibold text-[var(--color-text)]">ShowRoom Access</h3>
            </div>
            @if($user->showroomItems->isEmpty())
            <p class="p-4 text-sm text-[var(--color-muted)]">No demo access granted.</p>
            @else
            <div class="divide-y divide-[var(--color-border)]">
                @foreach($user->showroomItems as $item)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-[var(--color-text)]">{{ $item->title }}</p>
                        <p class="text-xs text-[var(--color-muted)]">Granted {{ $item->pivot->granted_at?->diffForHumans() }}</p>
                    </div>
                    <span class="badge {{ $item->is_active ? 'badge-green' : 'badge-muted' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent inquiries --}}
        <div class="card p-0">
            <div class="p-4 border-b border-[var(--color-border)]">
                <h3 class="font-semibold text-[var(--color-text)]">Inquiries</h3>
            </div>
            @if($user->inquiries->isEmpty())
            <p class="p-4 text-sm text-[var(--color-muted)]">No inquiries from this customer.</p>
            @else
            <table class="data-table">
                <thead><tr><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @foreach($user->inquiries as $inquiry)
                    <tr>
                        <td class="text-[var(--color-text)]">{{ Str::limit($inquiry->subject, 40) }}</td>
                        <td><span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->status }}</span></td>
                        <td class="text-[var(--color-muted)] text-xs">{{ $inquiry->created_at->format('M j, Y') }}</td>
                        <td><a href="/staff/inquiries/{{ $inquiry->id }}" class="text-sm text-[var(--color-primary)] hover:underline">View</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
