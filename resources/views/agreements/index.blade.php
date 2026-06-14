@extends('layouts.portal')
@section('title', 'Agreements')
@section('page-title', 'Agreements')
@section('breadcrumb', 'Your service agreements')

@php
    $activeCount   = $agreements->filter(fn ($a) => ! $a->isLocked())->count();
    $inactiveCount = $agreements->count() - $activeCount;
@endphp

@section('content')
<div class="card p-0" x-data="{ filter: 'active' }">
    <div class="p-4 border-b border-border flex items-center justify-between gap-3 flex-wrap">
        <p class="text-sm text-muted">Agreements we've shared with you. Open one to review, sign, and submit payment.</p>
        <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs shrink-0">
            <button type="button" @click="filter='active'" :class="filter==='active' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Active ({{ $activeCount }})</button>
            <button type="button" @click="filter='inactive'" :class="filter==='inactive' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Inactive ({{ $inactiveCount }})</button>
        </div>
    </div>

    @if($agreements->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="document" class="w-10 h-10 text-border mx-auto mb-3" />
        <p class="text-muted">No agreements yet.</p>
    </div>
    @else
    <table class="data-table">
        <thead><tr><th>Agreement</th><th>Status</th><th>Total</th><th>Paid</th><th>Balance</th><th>Created</th><th></th></tr></thead>
        <tbody>
            @foreach($agreements as $agreement)
            @php $active = ! $agreement->isLocked(); @endphp
            <tr class="cursor-pointer" x-cloak x-show="filter === '{{ $active ? 'active' : 'inactive' }}'"
                onclick="window.location.href='{{ route('agreements.show', $agreement) }}'">
                <td class="text-text font-medium">
                    {{ $agreement->title }}
                    @if($agreement->actionNeededForCustomer())<span class="badge badge-amber text-[10px] ml-1">Action needed</span>@endif
                </td>
                <td><span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span></td>
                <td class="text-text">{{ $agreement->has_cost ? '$'.number_format($agreement->total_amount, 2) : '—' }}</td>
                <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->amountPaid(), 2) : '—' }}</td>
                <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->balance(), 2) : '—' }}</td>
                <td class="text-muted text-xs">{{ $agreement->created_at->format('M j, Y') }}</td>
                <td class="text-right">
                    <a href="{{ route('agreements.show', $agreement) }}" onclick="event.stopPropagation()" class="btn-ghost btn-sm gap-1.5">
                        {{ $agreement->actionNeededForCustomer() ? 'Review & sign' : 'View' }}
                    </a>
                </td>
            </tr>
            @endforeach
            @if($activeCount === 0)<tr x-cloak x-show="filter==='active'"><td colspan="7" class="text-center text-muted py-6">No active agreements.</td></tr>@endif
            @if($inactiveCount === 0)<tr x-cloak x-show="filter==='inactive'"><td colspan="7" class="text-center text-muted py-6">No completed or canceled agreements.</td></tr>@endif
        </tbody>
    </table>
    @endif
</div>
@endsection
