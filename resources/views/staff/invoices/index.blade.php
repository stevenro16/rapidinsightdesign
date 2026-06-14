@extends('layouts.portal')
@section('title', 'Invoices')
@section('page-title', 'Invoices')
@section('breadcrumb', 'All customer invoices')

@php
    $activeCount   = $invoices->where('status', '!=', 'paid')->count();
    $inactiveCount = $invoices->count() - $activeCount;
@endphp

@section('content')
<div class="card p-0" x-data="{ filter: 'active' }">
    <div class="p-4 border-b border-border flex items-center justify-between gap-3 flex-wrap">
        <p class="text-sm text-muted">Every invoice across all customers. Open one to edit it, manage line items, and record payment.</p>
        <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs shrink-0">
            <button type="button" @click="filter='active'" :class="filter==='active' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Active ({{ $activeCount }})</button>
            <button type="button" @click="filter='inactive'" :class="filter==='inactive' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Inactive ({{ $inactiveCount }})</button>
        </div>
    </div>

    @if($invoices->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="document" class="w-10 h-10 text-border mx-auto mb-3" />
        <p class="text-muted">No invoices yet.</p>
    </div>
    @else
    <table class="data-table">
        <thead>
            <tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Status</th><th>Issued</th><th>Due</th></tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            @php $active = $invoice->status !== 'paid'; @endphp
            <tr class="cursor-pointer" x-cloak x-show="filter === '{{ $active ? 'active' : 'inactive' }}'"
                onclick="window.location.href='{{ route('staff.customers.invoices.edit', [$invoice->customer, $invoice]) }}'">
                <td class="font-medium text-text">{{ $invoice->number }}</td>
                <td class="text-muted">{{ $invoice->customer->name ?? '—' }}</td>
                <td class="text-text">${{ number_format($invoice->amount, 2) }}</td>
                <td>
                    <span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->status }}</span>
                    @if($invoice->isOverdue())<span class="badge badge-red text-[10px]">overdue</span>@endif
                    @if($invoice->visible_to_customer)<span class="badge badge-blue text-[10px]">shared</span>@endif
                </td>
                <td class="text-muted text-xs">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                <td class="text-xs {{ $invoice->isOverdue() ? 'text-red-400' : 'text-muted' }}">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
            </tr>
            @endforeach
            @if($activeCount === 0)<tr x-cloak x-show="filter==='active'"><td colspan="6" class="text-center text-muted py-6">No active (unpaid) invoices.</td></tr>@endif
            @if($inactiveCount === 0)<tr x-cloak x-show="filter==='inactive'"><td colspan="6" class="text-center text-muted py-6">No paid invoices yet.</td></tr>@endif
        </tbody>
    </table>
    @endif
</div>
@endsection
