@extends('layouts.portal')
@section('title', 'Billing')
@section('page-title', 'Billing & Invoices')

@section('content')
<div class="card p-0">
    <div class="p-4 border-b border-border">
        <p class="text-sm text-muted">Invoices shared with you. Open one to view or download the PDF.</p>
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
            <tr>
                <td class="font-medium text-text">{{ $invoice->number }}</td>
                <td class="text-muted">{{ $invoice->description ?? '—' }}</td>
                <td class="text-text">${{ number_format($invoice->amount, 2) }}</td>
                <td><span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->status }}</span></td>
                <td class="text-muted text-xs">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                <td class="text-xs {{ $invoice->isOverdue() ? 'text-red-400' : 'text-muted' }}">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('billing.pdf', $invoice) }}" target="_blank" class="btn-ghost btn-sm gap-1.5">
                        <x-icon name="document" class="w-3.5 h-3.5" /> View PDF
                    </a>
                    <a href="{{ route('billing.pdf', ['invoice' => $invoice, 'dl' => 1]) }}" class="btn-ghost btn-sm" title="Download">
                        <x-icon name="download" class="w-3.5 h-3.5" />
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
