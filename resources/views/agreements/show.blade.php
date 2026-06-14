@extends('layouts.portal')
@section('title', $agreement->title)
@section('page-title', $agreement->title)
@section('breadcrumb', 'Service agreement')

@php
    $canSign = $agreement->canCustomerSign();
    $signed  = $agreement->hasSignature();
@endphp

@section('content')
<div class="space-y-6" x-data="agreementReview(@js(auth()->user()->name))">

    {{-- Header --}}
    <div class="card">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <a href="{{ route('agreements.index') }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1">
                <x-icon name="chevron-left" class="w-3.5 h-3.5" /> All agreements
            </a>
            <span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span>
        </div>
        @if($agreement->has_cost)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
            <div><p class="label">Total</p><p class="text-lg font-display font-bold text-text">${{ number_format($agreement->total_amount, 2) }}</p></div>
            <div><p class="label">Deposit</p><p class="text-lg font-display font-bold text-text">${{ number_format($agreement->deposit_amount, 2) }}</p></div>
            <div><p class="label">Paid</p><p class="text-lg font-display font-bold text-primary">${{ number_format($agreement->amountPaid(), 2) }}</p></div>
            <div><p class="label">Balance</p><p class="text-lg font-display font-bold text-text">${{ number_format($agreement->balance(), 2) }}</p></div>
        </div>
        @else
        <p class="text-xs text-muted mt-3">This agreement has no cost — just review and sign.</p>
        @endif
        @if(! $canSign)
        <p class="text-sm text-muted mt-3">
            @switch($agreement->status)
                @case('pending_validation') Thanks! Your signature{{ $agreement->requiresPayment() ? ' and payment were' : ' was' }} submitted and {{ $agreement->requiresPayment() ? 'are' : 'is' }} pending our validation. @break
                @case('completed') This agreement is complete. Thank you! @break
                @case('canceled') This agreement was canceled. @break
            @endswitch
        </p>
        @endif
    </div>

    {{-- Attached invoice(s) — jump in to view details & pay --}}
    @php $visibleInvoices = $agreement->invoices->where('visible_to_customer', true); @endphp
    @if($visibleInvoices->isNotEmpty())
    <div class="card border border-primary/30">
        <h3 class="font-semibold text-text">Invoice{{ $visibleInvoices->count() > 1 ? 's' : '' }}</h3>
        <p class="text-xs text-muted mb-3">An invoice has been issued for this agreement. Open it to view the bill and submit payment.</p>
        <div class="space-y-2">
            @foreach($visibleInvoices as $inv)
            <a href="{{ route('billing.show', $inv) }}"
               class="flex items-center justify-between gap-3 rounded-lg border border-border bg-surface-2 p-3 hover:border-primary transition-colors">
                <div class="min-w-0">
                    <p class="text-text font-medium">{{ $inv->number }}</p>
                    <p class="text-xs text-muted">Issued {{ $inv->issued_at?->format('M j, Y') ?? '—' }} · Due {{ $inv->due_at?->format('M j, Y') ?? '—' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-text font-semibold">${{ number_format($inv->amount, 2) }}</p>
                    <span class="badge {{ $inv->statusBadgeClass() }} text-[10px]">{{ $inv->status }}</span>
                </div>
                <x-icon name="arrow-right" class="w-4 h-4 text-muted shrink-0" />
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Agreement text (scroll-gated) --}}
    <div class="card">
        <h3 class="font-semibold text-text mb-3">Agreement</h3>
        <div x-ref="doc" @scroll="onScroll($event)"
             class="max-h-[60vh] overflow-y-auto rounded-lg border border-border bg-surface-2 p-4">
            <div class="whitespace-pre-line text-sm leading-relaxed text-text">{{ $agreement->body }}</div>
        </div>
        @if($canSign && ! $signed)
        <p x-show="!reachedBottom" class="text-xs text-amber-400 mt-2 flex items-center gap-1">
            <x-icon name="warning" class="w-3.5 h-3.5" /> Please scroll to the bottom to enable signing.
        </p>
        @endif
    </div>

    {{-- Signature --}}
    <div class="card">
        <h3 class="font-semibold text-text mb-3">Signature</h3>

        @if($signed)
        <div class="rounded-lg border border-border bg-white p-3 inline-block">
            @if($agreement->signature_method === 'drawn')
            <img src="{{ $agreement->signature_data }}" alt="signature" class="max-h-24">
            @else
            <span style="font-family: '{{ $agreement->signature_font }}', cursive; font-size: 2rem; color:#111;">{{ $agreement->signature_name }}</span>
            @endif
        </div>
        <p class="text-xs text-muted mt-2">Signed as {{ $agreement->signature_name }}@if($agreement->signed_at) on {{ $agreement->signed_at->format('M j, Y g:i A') }}@endif.</p>

        @elseif($canSign)
        {{-- Sign form --}}
        <form x-ref="signForm" method="POST" action="{{ route('agreements.sign', $agreement) }}" @submit.prevent="submitSignature()" class="space-y-4">
            @csrf
            <input type="hidden" name="signature_method" x-ref="fMethod">
            <input type="hidden" name="signature_data" x-ref="fData">
            <input type="hidden" name="signature_font" x-ref="fFont">

            <div>
                <label class="label">Your full legal name <span class="text-muted font-normal" x-show="method === 'typed'">(required to pick a style)</span></label>
                <input type="text" name="signature_name" x-model="name" class="input" placeholder="Jane A. Doe" :required="method === 'typed'">
            </div>

            {{-- method toggle --}}
            <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs">
                <button type="button" @click="method='drawn'" :class="method==='drawn' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Draw it</button>
                <button type="button" @click="method='typed'" :class="method==='typed' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Pick a style</button>
            </div>

            {{-- drawn --}}
            <div x-show="method==='drawn'">
                <canvas x-ref="canvas" width="500" height="180"
                        class="w-full h-44 rounded-lg border border-border bg-white touch-none cursor-crosshair"
                        @mousedown="startDraw($event)" @mousemove="draw($event)" @mouseup="stopDraw()" @mouseleave="stopDraw()"
                        @touchstart="startDraw($event)" @touchmove="draw($event)" @touchend="stopDraw()"></canvas>
                <button type="button" @click="clearPad()" class="text-xs text-muted hover:text-text mt-1">Clear</button>
            </div>

            {{-- typed samples --}}
            <div x-show="method==='typed'">
                <p class="text-xs text-muted mb-2" x-show="!name.trim()">Type your name above to preview signature styles.</p>
                <div class="grid sm:grid-cols-2 gap-2" x-show="name.trim()">
                    <template x-for="font in ['Dancing Script','Great Vibes','Pacifico','Satisfy']" :key="font">
                        <label class="flex items-center gap-3 rounded-lg border p-3 cursor-pointer bg-white"
                               :class="chosenFont===font ? 'border-primary ring-1 ring-primary' : 'border-border'">
                            <input type="radio" name="_font" :value="font" x-model="chosenFont" class="shrink-0">
                            <span :style="`font-family: '${font}', cursive; font-size: 1.6rem; color:#111;`" x-text="name"></span>
                        </label>
                    </template>
                </div>
            </div>

            <label class="flex items-start gap-2 cursor-pointer">
                <input type="checkbox" name="agreed" value="1" x-model="agreed" :disabled="!reachedBottom" class="rounded mt-0.5">
                <span class="text-sm text-text">I have read and agree to the terms of this agreement.
                    <span x-show="!reachedBottom" class="block text-xs text-muted">(scroll the agreement to the bottom first)</span>
                </span>
            </label>

            <button type="submit" :disabled="!canSign()"
                    :class="canSign() ? '' : 'opacity-50 cursor-not-allowed'"
                    class="btn-primary gap-1.5"><x-icon name="pencil" class="w-4 h-4" /> Adopt &amp; Sign</button>
        </form>
        @else
        <p class="text-sm text-muted">Not signed.</p>
        @endif
    </div>

    {{-- Payments (only when the agreement has a cost) --}}
    @if($agreement->has_cost)
    <div class="card">
        <h3 class="font-semibold text-text mb-1">Payment</h3>
        <p class="text-xs text-muted mb-3">Payments are recorded here and confirmed by our team — no card is charged on this page yet.</p>

        @forelse($agreement->payments as $payment)
        <div class="flex items-center justify-between text-sm border-b border-border py-2">
            <span class="text-text">${{ number_format($payment->amount, 2) }} <span class="text-xs text-muted">· {{ $payment->type }}</span></span>
            <span class="badge {{ $payment->statusBadgeClass() }} text-[10px]">{{ $payment->status }}</span>
        </div>
        @empty
        <p class="text-sm text-muted mb-2">No payments yet.</p>
        @endforelse

        @if($canSign && $agreement->balance() > 0)
        <form method="POST" action="{{ route('agreements.payment.store', $agreement) }}"
              class="mt-3 flex flex-wrap items-end gap-2"
              x-data="{ amt: '{{ number_format($agreement->deposit_amount, 2, '.', '') }}' }">
            @csrf
            <div>
                <label class="label">Amount ($)</label>
                <input type="number" step="0.01" min="0.01" max="{{ $agreement->balance() }}" name="amount" x-model="amt" class="input w-36" required>
            </div>
            <div>
                <label class="label">Type</label>
                <select name="type" class="select"
                        @change="$event.target.value==='deposit' ? amt='{{ number_format($agreement->deposit_amount, 2, '.', '') }}' : ($event.target.value==='full' ? amt='{{ number_format($agreement->balance(), 2, '.', '') }}' : null)">
                    <option value="deposit">Deposit</option>
                    <option value="partial" selected>Partial</option>
                    <option value="full">Pay in full</option>
                </select>
            </div>
            <button class="btn-ghost btn-sm">Record payment</button>
        </form>
        @endif
    </div>
    @endif

    {{-- Once signed (and paid, if there's a cost) it's submitted for validation automatically. --}}
    @if($canSign && $signed && $agreement->requiresPayment())
    <div class="card border border-amber-500/30 bg-amber-500/5">
        <p class="text-sm text-text">Almost there — add your payment above and the agreement is automatically submitted for our validation.</p>
    </div>
    @endif

</div>
@endsection
