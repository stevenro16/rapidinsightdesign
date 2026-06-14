@extends('layouts.portal')
@section('title', 'Agreement')
@section('page-title', 'Agreement')
@section('breadcrumb', $user->name)

@php
    $locked = $agreement->isLocked();
    $defaultBody    = \App\Models\SiteContent::get('agreement_default_text', \App\Models\Agreement::DEFAULT_BODY);
    $productionBody = \App\Models\SiteContent::get('agreement_production_text', \App\Models\Agreement::PRODUCTION_BODY);
@endphp

@section('content')
<div class="space-y-6" x-data="{ hasCost: {{ $agreement->has_cost ? 'true' : 'false' }}, body: @js($agreement->body), def: @js($defaultBody), prod: @js($productionBody) }">

    {{-- Header --}}
    <div class="card flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <a href="{{ route('staff.customers.show', $user) }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1 mb-1">
                <x-icon name="chevron-left" class="w-3.5 h-3.5" /> Back to {{ $user->name }}
            </a>
            <div class="flex items-center gap-2">
                <h2 class="font-display font-semibold text-lg text-text">{{ $agreement->title }}</h2>
                <span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span>
            </div>
            <p class="text-xs text-muted mt-0.5">Created {{ $agreement->created_at->format('M j, Y') }}</p>
        </div>
        <a href="{{ route('staff.customers.agreements.pdf', [$user, $agreement]) }}" target="_blank" class="btn-ghost btn-sm gap-1.5 shrink-0">
            <x-icon name="document" class="w-3.5 h-3.5" /> PDF
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Editor --}}
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="{{ route('staff.customers.agreements.update', [$user, $agreement]) }}"
                  class="card space-y-4">
                @csrf @method('PATCH')
                @if($errors->any())
                <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif
                <div><label class="label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $agreement->title) }}" class="input" @disabled($locked)></div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="has_cost" value="1" x-model="hasCost" class="rounded" @disabled($locked)>
                    <span class="text-sm text-text">This agreement includes a cost &amp; payment
                        <span class="block text-xs text-muted">Leave off for a no-cost contract (e.g. to start discovery) — the customer just reviews &amp; signs.</span>
                    </span>
                </label>

                <div x-show="hasCost" class="grid grid-cols-2 gap-3">
                    <div><label class="label">Total ($)</label>
                        <input type="number" step="0.01" min="0" name="total_amount" value="{{ old('total_amount', $agreement->total_amount) }}" class="input" @disabled($locked)></div>
                    <div><label class="label">Deposit ($)</label>
                        <input type="number" step="0.01" min="0" name="deposit_amount" value="{{ old('deposit_amount', $agreement->deposit_amount) }}" class="input" @disabled($locked)></div>
                </div>
                <div>
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <label class="label">Agreement Text</label>
                        @unless($locked)
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-muted">Use template:</span>
                            <button type="button" @click="body = def" class="text-primary hover:underline">Standard</button>
                            <button type="button" @click="body = prod" class="text-primary hover:underline">Production release</button>
                        </div>
                        @endunless
                    </div>
                    <textarea name="body" rows="18" class="input resize-y font-mono text-xs leading-relaxed" x-model="body" @disabled($locked)></textarea>
                </div>
                @unless($locked)
                <div><button type="submit" class="btn-primary btn-sm">Save Changes</button></div>
                @else
                <p class="text-xs text-muted">This agreement is {{ $agreement->status }} and can no longer be edited.</p>
                @endunless
            </form>

            {{-- Signature review --}}
            <div class="card">
                <h3 class="font-semibold text-text mb-3">Customer Signature</h3>
                @if($agreement->hasSignature())
                <div class="rounded-lg border border-border bg-white p-3 inline-block">
                    @if($agreement->signature_method === 'drawn')
                    <img src="{{ $agreement->signature_data }}" alt="signature" class="max-h-24">
                    @else
                    <span style="font-family: '{{ $agreement->signature_font }}', cursive; font-size: 2rem; color:#111;">{{ $agreement->signature_name }}</span>
                    @endif
                </div>
                <p class="text-xs text-muted mt-2">
                    Signed by {{ $agreement->signature_name ?? $user->name }}
                    @if($agreement->signed_at) on {{ $agreement->signed_at->format('M j, Y g:i A') }}@endif
                    · Terms agreed {{ $agreement->agreed ? '✓' : '—' }}
                </p>
                @else
                <p class="text-sm text-muted">Not signed yet.</p>
                @endif
            </div>
        </div>

        {{-- Sidebar: status actions + payments --}}
        <div class="space-y-6">
            {{-- Status / actions --}}
            <div class="card space-y-3">
                <h3 class="font-semibold text-text">Status</h3>
                <p><span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span></p>

                @if($agreement->status === 'draft')
                <form method="POST" action="{{ route('staff.customers.agreements.send', [$user, $agreement]) }}">
                    @csrf
                    <button class="btn-primary btn-sm w-full justify-center gap-1.5"><x-icon name="arrow-right" class="w-3.5 h-3.5" /> Send to Customer</button>
                </form>
                <p class="text-xs text-muted">Requires agreement text. {{ $agreement->has_cost ? '' : 'No cost — review & signature only.' }}</p>
                @elseif($agreement->status === 'pending_customer_review')
                <p class="text-sm text-muted">Waiting on the customer to review, sign{{ $agreement->requiresPayment() ? ', and submit a payment' : '' }}.</p>
                @elseif($agreement->status === 'pending_validation')
                <div class="text-xs text-muted space-y-1 border border-border rounded-lg p-3">
                    <p>Terms agreed: <span class="text-text">{{ $agreement->agreed ? 'Yes' : 'No' }}</span></p>
                    <p>Signed: <span class="text-text">{{ $agreement->hasSignature() ? 'Yes' : 'No' }}</span></p>
                    <p>Paid: <span class="text-text">${{ number_format($agreement->amountPaid(), 2) }}</span> · Pending: ${{ number_format($agreement->amountPending(), 2) }}</p>
                </div>
                <form method="POST" action="{{ route('staff.customers.agreements.complete', [$user, $agreement]) }}">
                    @csrf
                    <button class="btn-primary btn-sm w-full justify-center gap-1.5"><x-icon name="check" class="w-3.5 h-3.5" /> Validate &amp; Complete</button>
                </form>
                <p class="text-[11px] text-muted">Completing confirms any pending payments.</p>
                <form method="POST" action="{{ route('staff.customers.agreements.reopen', [$user, $agreement]) }}">
                    @csrf
                    <button class="btn-ghost btn-sm w-full justify-center">Send back to customer</button>
                </form>
                @else
                <p class="text-sm text-muted">This agreement is {{ $agreement->status }}.</p>
                @endif

                @if($agreement->canCancel())
                <form method="POST" action="{{ route('staff.customers.agreements.cancel', [$user, $agreement]) }}"
                      x-data="confirmDelete('Cancel this agreement?')">
                    @csrf
                    <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm w-full justify-center text-[var(--color-danger)]">Cancel agreement</button>
                </form>
                @endif
                @if($agreement->status === 'draft')
                <form method="POST" action="{{ route('staff.customers.agreements.destroy', [$user, $agreement]) }}"
                      x-data="confirmDelete('Delete this draft permanently?')">
                    @csrf @method('DELETE')
                    <button @click.prevent="confirm($el.closest('form'))" class="text-xs text-[var(--color-danger)] hover:underline w-full text-center">Delete draft</button>
                </form>
                @endif
            </div>

            {{-- Work order --}}
            <div class="card space-y-3">
                <h3 class="font-semibold text-text">Work Order</h3>
                @if($agreement->workOrder)
                <p class="text-sm text-text">Linked to <a href="{{ route('staff.work-orders.edit', $agreement->workOrder) }}" class="text-primary hover:underline">{{ $agreement->workOrder->title }}</a></p>
                <p><span class="badge {{ $agreement->workOrder->statusBadgeClass() }} text-[10px]">{{ $agreement->workOrder->statusLabel() }}</span></p>
                <form method="POST" action="{{ route('staff.work-orders.agreements.detach', [$agreement->workOrder, $agreement]) }}">
                    @csrf @method('DELETE')
                    <button class="btn-ghost btn-sm text-[var(--color-danger)]">Detach from work order</button>
                </form>
                @else
                <p class="text-xs text-muted">Convert this agreement into a tracked work order, or attach it to an existing one.</p>
                <form method="POST" action="{{ route('staff.customers.work-orders.store', $user) }}">
                    @csrf
                    <input type="hidden" name="title" value="{{ $agreement->title }}">
                    <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                    <button class="btn-primary btn-sm w-full justify-center gap-1.5"><x-icon name="plus" class="w-3.5 h-3.5" /> Convert to new work order</button>
                </form>
                @if($workOrders->isNotEmpty())
                <form method="POST" class="flex gap-2">
                    @csrf
                    <select class="select flex-1" id="wo-attach-sel-{{ $agreement->id }}">
                        <option value="">— Attach to existing —</option>
                        @foreach($workOrders as $wo)
                        <option value="{{ $wo->id }}">{{ $wo->title }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-ghost btn-sm"
                            @click.prevent="const v=document.getElementById('wo-attach-sel-{{ $agreement->id }}').value; if(v){const f=$el.closest('form'); f.action='/staff/work-orders/'+v+'/agreements/{{ $agreement->id }}/attach'; f.submit();}">Attach</button>
                </form>
                @endif
                @endif
            </div>

            {{-- Invoices billed against this agreement --}}
            <div class="card space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-text">Invoices</h3>
                    <span class="text-xs text-muted">Agreement = quote · invoice = the bill</span>
                </div>

                @forelse($agreement->invoices as $inv)
                <div class="flex items-center justify-between gap-2 text-sm border-b border-border pb-2">
                    <div class="min-w-0">
                        <a href="{{ route('staff.customers.invoices.edit', [$user, $inv]) }}" class="text-text hover:text-primary font-medium">{{ $inv->number }}</a>
                        <p class="text-xs text-muted">${{ number_format($inv->amount, 2) }} · {{ $inv->issued_at?->format('M j, Y') ?? 'no date' }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="badge {{ $inv->statusBadgeClass() }} text-[10px]">{{ $inv->status }}</span>
                        <a href="{{ route('staff.customers.invoices.pdf', [$user, $inv]) }}" target="_blank" class="btn-ghost btn-sm" title="PDF"><x-icon name="document" class="w-3.5 h-3.5" /></a>
                    </div>
                </div>
                @empty
                <p class="text-sm text-muted">No invoices yet for this agreement.</p>
                @endforelse

                <form method="POST" action="{{ route('staff.customers.agreements.invoices.store', [$user, $agreement]) }}">
                    @csrf
                    <button class="btn-primary btn-sm w-full justify-center gap-1.5">
                        <x-icon name="plus" class="w-3.5 h-3.5" /> Create invoice
                        @if((float) $agreement->total_amount > 0)<span class="text-xs opacity-80">(${{ number_format($agreement->total_amount, 2) }})</span>@endif
                    </button>
                </form>
                <p class="text-[11px] text-muted">Defaults a line item to the agreement’s quoted total &amp; the site default tax rate. Edit before sending.</p>
            </div>

            {{-- Payments (only when the agreement has a cost) --}}
            <div class="card space-y-3" x-show="hasCost" x-cloak>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-text">Payments</h3>
                    <span class="text-xs text-muted">${{ number_format($agreement->amountPaid(), 2) }} / ${{ number_format($agreement->total_amount, 2) }}</span>
                </div>

                @forelse($agreement->payments as $payment)
                <div class="flex items-center justify-between gap-2 text-sm border-b border-border pb-2">
                    <div class="min-w-0">
                        <p class="text-text">${{ number_format($payment->amount, 2) }} <span class="text-xs text-muted">· {{ $payment->type }}</span></p>
                        <p class="text-xs text-muted">{{ $payment->paid_at?->format('M j, Y') ?? 'unpaid' }}@if($payment->reference) · {{ $payment->reference }}@endif</p>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="badge {{ $payment->statusBadgeClass() }} text-[10px]">{{ $payment->status }}</span>
                        @if($payment->status === 'pending' && ! $locked)
                        <form method="POST" action="{{ route('staff.customers.agreements.payments.confirm', [$user, $agreement, $payment]) }}">
                            @csrf @method('PATCH')
                            <button class="btn-ghost btn-sm" title="Confirm received"><x-icon name="check" class="w-3.5 h-3.5 text-primary" /></button>
                        </form>
                        @endif
                        @unless($locked)
                        <form method="POST" action="{{ route('staff.customers.agreements.payments.destroy', [$user, $agreement, $payment]) }}" x-data="confirmDelete('Remove this payment?')">
                            @csrf @method('DELETE')
                            <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-[var(--color-danger)]"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                        </form>
                        @endunless
                    </div>
                </div>
                @empty
                <p class="text-sm text-muted">No payments recorded.</p>
                @endforelse

                @unless($locked)
                <form method="POST" action="{{ route('staff.customers.agreements.payments.store', [$user, $agreement]) }}" class="border-t border-border pt-3 space-y-2">
                    @csrf
                    <p class="label">Record a payment</p>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" min="0.01" name="amount" class="input" placeholder="Amount" required>
                        <select name="type" class="select">
                            <option value="deposit">Deposit</option>
                            <option value="partial" selected>Partial</option>
                            <option value="full">Full</option>
                        </select>
                        <select name="status" class="select">
                            <option value="confirmed" selected>Confirmed (received)</option>
                            <option value="pending">Pending</option>
                        </select>
                        <input type="date" name="paid_at" class="input">
                    </div>
                    <input type="text" name="reference" class="input" placeholder="Reference / memo (optional)">
                    <button class="btn-primary btn-sm w-full justify-center">Add Payment</button>
                </form>
                @endunless
            </div>
        </div>
    </div>
</div>
@endsection
