@extends('layouts.portal')
@section('title', 'Work Orders')
@section('page-title', 'Work Orders')
@section('breadcrumb', 'Your projects with us')

@php
    $terminal      = ['completed', 'canceled'];
    $activeCount   = $workOrders->reject(fn ($w) => in_array($w->status, $terminal, true))->count();
    $inactiveCount = $workOrders->count() - $activeCount;
@endphp

@section('content')
<div class="card p-0" x-data="{ filter: 'active' }">
    <div class="p-4 border-b border-border flex items-center justify-between gap-3 flex-wrap">
        <p class="text-sm text-muted">The projects we're working on for you. Open one to see progress and updates.</p>
        <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs shrink-0">
            <button type="button" @click="filter='active'" :class="filter==='active' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Active ({{ $activeCount }})</button>
            <button type="button" @click="filter='inactive'" :class="filter==='inactive' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Inactive ({{ $inactiveCount }})</button>
        </div>
    </div>

    @if($workOrders->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="grid" class="w-10 h-10 text-border mx-auto mb-3" />
        <p class="text-muted">No work orders yet.</p>
    </div>
    @else
    <table class="data-table">
        <thead><tr><th>Project</th><th>Latest update</th><th>Status</th><th>Updated</th></tr></thead>
        <tbody>
            @foreach($workOrders as $wo)
            @php
                $lastNote = $wo->lastCustomerVisibleNote();
                $active   = ! in_array($wo->status, $terminal, true);
            @endphp
            <tr class="cursor-pointer" x-cloak x-show="filter === '{{ $active ? 'active' : 'inactive' }}'"
                onclick="window.location.href='{{ route('work-orders.show', $wo) }}'">
                <td>
                    <p class="font-medium text-text">{{ $wo->title }}</p>
                    @if($wo->summary)<p class="text-xs text-muted">{{ $wo->summary }}</p>@endif
                    @if($wo->website_url)
                    <a href="{{ $wo->website_url }}" target="_blank" rel="noopener" onclick="event.stopPropagation()"
                       class="text-xs text-primary hover:underline inline-flex items-center gap-1 mt-0.5">
                        <x-icon name="external" class="w-3 h-3" /> Visit site
                    </a>
                    @endif
                </td>
                <td class="text-muted">{{ $lastNote ? Str::limit($lastNote->body, 60) : '—' }}</td>
                <td>
                    <span class="badge {{ $wo->statusBadgeClass() }}">{{ $wo->statusLabel() }}</span>
                    @if($wo->awaitingCustomer() && ! $wo->customerValidated())<span class="badge badge-amber text-[10px]">needs your OK</span>@endif
                </td>
                <td class="text-muted text-xs">{{ $wo->updated_at->diffForHumans() }}</td>
            </tr>
            @endforeach
            @if($activeCount === 0)<tr x-cloak x-show="filter==='active'"><td colspan="4" class="text-center text-muted py-6">No active work orders.</td></tr>@endif
            @if($inactiveCount === 0)<tr x-cloak x-show="filter==='inactive'"><td colspan="4" class="text-center text-muted py-6">No completed or canceled work orders.</td></tr>@endif
        </tbody>
    </table>
    @endif
</div>
@endsection
