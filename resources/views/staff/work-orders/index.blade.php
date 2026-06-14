@extends('layouts.portal')
@section('title', 'Work Orders')
@section('page-title', 'Work Orders')
@section('breadcrumb', 'Track all website projects')

@section('content')
<div class="card p-0">
    <div class="p-4 border-b border-border flex items-center gap-3 flex-wrap">
        <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs flex-wrap">
            @foreach($filters as $key => $label)
            <a href="{{ route('staff.work-orders.index', ['status' => $key, 'q' => $search]) }}"
               class="px-3 py-1 rounded-full transition-colors {{ $status === $key ? 'bg-primary text-bg font-semibold' : 'text-muted hover:text-text' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('staff.work-orders.index') }}" class="ml-auto flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search by customer…" class="input py-1.5 text-sm w-56">
            <button class="btn-ghost btn-sm"><x-icon name="search" class="w-3.5 h-3.5" /></button>
        </form>
    </div>

    @if($workOrders->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="grid" class="w-10 h-10 text-border mx-auto mb-3" />
        <p class="text-muted">{{ $search ? 'No work orders match your search.' : 'No work orders yet. Create one from a customer or an agreement.' }}</p>
    </div>
    @else
    <table class="data-table">
        <thead><tr><th>Customer</th><th>Work Order</th><th>Status</th><th>Agreements</th><th>Updated</th><th></th></tr></thead>
        <tbody>
            @foreach($workOrders as $wo)
            <tr>
                <td>
                    <p class="font-medium text-text">{{ $wo->customer->name ?? '—' }}</p>
                    <p class="text-xs text-muted">{{ $wo->customer->email ?? '' }}</p>
                </td>
                <td class="text-text">
                    {{ $wo->title }}
                    @if($wo->unread_messages > 0)<span class="badge badge-blue text-[10px] gap-1"><x-icon name="chat" class="w-3 h-3" />{{ $wo->unread_messages }} new</span>@endif
                </td>
                <td>
                    <span class="badge {{ $wo->statusBadgeClass() }}">{{ $wo->statusLabel() }}</span>
                    @if($wo->awaitingCustomer() && $wo->customerValidated())<span class="badge badge-green text-[10px]">approved</span>@endif
                </td>
                <td class="text-muted">{{ $wo->agreements_count }}</td>
                <td class="text-muted text-xs">{{ $wo->updated_at->diffForHumans() }}</td>
                <td class="text-right"><a href="{{ route('staff.work-orders.edit', $wo) }}" class="btn-ghost btn-sm">Open</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $workOrders->links() }}</div>
    @endif
</div>
@endsection
