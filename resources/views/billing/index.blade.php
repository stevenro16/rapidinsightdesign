@extends('layouts.portal')
@section('title', 'Billing')
@section('page-title', 'Billing & Invoices')

@php
    $activeCount   = $invoices->where('status', '!=', 'paid')->count();
    $inactiveCount = $invoices->count() - $activeCount;
@endphp

@section('content')
<div class="card p-0" x-data="{ filter: 'active' }">
    <div class="p-4 border-b border-border flex items-center justify-between gap-3 flex-wrap">
        <p class="text-sm text-muted">Invoices shared with you. Open one to view details, download the PDF, or submit a payment.</p>
        <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs shrink-0">
            <button type="button" @click="filter='active'" :class="filter==='active' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Active ({{ $activeCount }})</button>
            <button type="button" @click="filter='inactive'" :class="filter==='inactive' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Inactive ({{ $inactiveCount }})</button>
        </div>
    </div>

    @if($invoices->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="document" class="w-10 h-10 text-border mx-auto mb-3" />
        <p class="text-muted">No invoices have been shared with you yet.</p>
    </div>
    @else
    <table class="data-table">
        <thead>
            <tr><th>Invoice</th><th>Description</th><th>Amount</th><th>Status</th><th>Issued</th><th>Due</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            @php $active = $invoice->status !== 'paid'; @endphp
            <tr class="cursor-pointer" x-cloak x-show="filter === '{{ $active ? 'active' : 'inactive' }}'"
                onclick="window.location.href='{{ route('billing.show', $invoice) }}'">
                <td class="font-medium text-text">{{ $invoice->number }}</td>
                <td class="text-muted">{{ $invoice->description ?? '—' }}</td>
                <td class="text-text">${{ number_format($invoice->amount, 2) }}</td>
                <td>
                    <span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->status }}</span>
                    @if($invoice->isOverdue())<span class="badge badge-red text-[10px]">overdue</span>@endif
                </td>
                <td class="text-muted text-xs">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                <td class="text-xs {{ $invoice->isOverdue() ? 'text-red-400' : 'text-muted' }}">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('billing.show', $invoice) }}" onclick="event.stopPropagation()" class="btn-ghost btn-sm gap-1.5">
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" /> View &amp; pay
                    </a>
                    <a href="{{ route('billing.pdf', ['invoice' => $invoice, 'dl' => 1]) }}" onclick="event.stopPropagation()" class="btn-ghost btn-sm" title="Download PDF">
                        <x-icon name="download" class="w-3.5 h-3.5" />
                    </a>
                </td>
            </tr>
            @endforeach
            @if($activeCount === 0)<tr x-cloak x-show="filter==='active'"><td colspan="7" class="text-center text-muted py-6">No active invoices.</td></tr>@endif
            @if($inactiveCount === 0)<tr x-cloak x-show="filter==='inactive'"><td colspan="7" class="text-center text-muted py-6">No paid invoices yet.</td></tr>@endif
        </tbody>
    </table>
    @endif
</div>
@endsection
