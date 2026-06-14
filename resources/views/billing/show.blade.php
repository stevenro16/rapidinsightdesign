@extends('layouts.portal')
@section('title', 'Invoice ' . $invoice->number)
@section('page-title', 'Invoice ' . $invoice->number)
@section('breadcrumb', 'Billing')

@php
    $ag      = $invoice->agreement;
    $payable = ($ag && $ag->has_cost) ? $ag->balance() : 0;
@endphp

@section('content')
<div class="space-y-6 max-w-3xl">

    {{-- Header --}}
    <div class="card">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <a href="{{ route('billing.index') }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1">
                <x-icon name="chevron-left" class="w-3.5 h-3.5" /> All invoices
            </a>
            <span class="badge {{ $invoice->statusBadgeClass() }}">{{ ucfirst($invoice->status) }}</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
            <div><p class="label">Invoice</p><p class="text-text font-medium">{{ $invoice->number }}</p></div>
            <div><p class="label">Issued</p><p class="text-text">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</p></div>
            <div><p class="label">Due</p><p class="{{ $invoice->isOverdue() ? 'text-red-400' : 'text-text' }}">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</p></div>
            <div><p class="label">Total</p><p class="text-lg font-display font-bold text-primary">${{ number_format($invoice->amount, 2) }}</p></div>
        </div>

        <div class="flex items-center gap-2 mt-4 flex-wrap">
            <a href="{{ route('billing.pdf', $invoice) }}" target="_blank" class="btn-ghost btn-sm gap-1.5">
                <x-icon name="document" class="w-3.5 h-3.5" /> View PDF
            </a>
            <a href="{{ route('billing.pdf', ['invoice' => $invoice, 'dl' => 1]) }}" class="btn-ghost btn-sm gap-1.5">
                <x-icon name="download" class="w-3.5 h-3.5" /> Download
            </a>
            @if($ag)
            <a href="{{ route('agreements.show', $ag) }}" class="btn-ghost btn-sm gap-1.5">
                <x-icon name="document" class="w-3.5 h-3.5" /> View agreement
            </a>
            @endif
        </div>
    </div>

    {{-- Line items --}}
    <div class="card">
        <h3 class="font-semibold text-text mb-3">Bill details</h3>

        @if($invoice->description || $invoice->work_summary)
        <div class="mb-3">
            @if($invoice->description)<p class="text-text font-medium">{{ $invoice->description }}</p>@endif
            @if($invoice->work_summary)<p class="text-sm text-muted whitespace-pre-line mt-1">{{ $invoice->work_summary }}</p>@endif
        </div>
        @endif

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-border">
                    <th class="py-2 pr-2 font-medium">Description</th>
                    <th class="py-2 px-2 font-medium text-right w-16">Qty</th>
                    <th class="py-2 px-2 font-medium text-right w-28">Unit Price</th>
                    <th class="py-2 pl-2 font-medium text-right w-28">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                <tr class="border-b border-border/60">
                    <td class="py-2 pr-2 text-text">{{ $item->description }}</td>
                    <td class="py-2 px-2 text-right text-muted">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td class="py-2 px-2 text-right text-muted">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-2 pl-2 text-right text-text">${{ number_format($item->lineTotal(), 2) }}</td>
                </tr>
                @empty
                <tr class="border-b border-border/60">
                    <td class="py-2 pr-2 text-text">{{ $invoice->description ?: 'Professional services' }}</td>
                    <td class="py-2 px-2 text-right text-muted">1</td>
                    <td class="py-2 px-2 text-right text-muted">${{ number_format($invoice->amount, 2) }}</td>
                    <td class="py-2 pl-2 text-right text-text">${{ number_format($invoice->amount, 2) }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="flex justify-end mt-3">
            <table class="text-sm w-56">
                <tr><td class="py-1 text-muted">Subtotal</td><td class="py-1 text-right text-text">${{ number_format($invoice->subtotal, 2) }}</td></tr>
                @if((float) $invoice->tax_rate > 0 || (float) $invoice->tax_amount > 0)
                <tr><td class="py-1 text-muted">Tax ({{ rtrim(rtrim(number_format($invoice->tax_rate, 2), '0'), '.') }}%)</td><td class="py-1 text-right text-text">${{ number_format($invoice->tax_amount, 2) }}</td></tr>
                @endif
                <tr class="border-t border-border"><td class="py-2 font-semibold text-text">Total</td><td class="py-2 text-right font-semibold text-primary text-base">${{ number_format($invoice->amount, 2) }}</td></tr>
            </table>
        </div>
    </div>

    {{-- Payment --}}
    @if($ag && $ag->has_cost)
    <div class="card">
        <h3 class="font-semibold text-text mb-1">Payment</h3>
        <p class="text-xs text-muted mb-3">Payments are recorded here and confirmed by our team — no card is charged on this page yet.</p>

        <div class="grid grid-cols-3 gap-3 mb-3">
            <div><p class="label">Total</p><p class="font-display font-bold text-text">${{ number_format($ag->total_amount, 2) }}</p></div>
            <div><p class="label">Paid</p><p class="font-display font-bold text-primary">${{ number_format($ag->amountPaid(), 2) }}</p></div>
            <div><p class="label">Balance</p><p class="font-display font-bold text-text">${{ number_format($ag->balance(), 2) }}</p></div>
        </div>

        @forelse($ag->payments as $payment)
        <div class="flex items-center justify-between text-sm border-b border-border py-2">
            <span class="text-text">${{ number_format($payment->amount, 2) }} <span class="text-xs text-muted">· {{ $payment->type }}</span></span>
            <span class="badge {{ $payment->statusBadgeClass() }} text-[10px]">{{ $payment->status }}</span>
        </div>
        @empty
        <p class="text-sm text-muted mb-2">No payments yet.</p>
        @endforelse

        @if($payable > 0)
        <form method="POST" action="{{ route('billing.payment.store', $invoice) }}"
              class="mt-3 flex flex-wrap items-end gap-2"
              x-data="{ amt: '{{ number_format($ag->balance(), 2, '.', '') }}' }">
            @csrf
            <div>
                <label class="label">Amount ($)</label>
                <input type="number" step="0.01" min="0.01" max="{{ $ag->balance() }}" name="amount" x-model="amt" class="input w-36" required>
            </div>
            <div>
                <label class="label">Type</label>
                <select name="type" class="select"
                        @change="$event.target.value==='full' ? amt='{{ number_format($ag->balance(), 2, '.', '') }}' : null">
                    <option value="full" selected>Pay in full</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
            <button class="btn-primary btn-sm gap-1.5"><x-icon name="check" class="w-3.5 h-3.5" /> Submit payment</button>
        </form>
        @else
        <p class="text-sm text-primary mt-2">This invoice is paid in full. Thank you!</p>
        @endif
    </div>
    @endif

</div>
@endsection
