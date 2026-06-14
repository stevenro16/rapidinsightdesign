@extends('layouts.portal')
@section('title', 'Invoice ' . $invoice->number)
@section('page-title', 'Invoice ' . $invoice->number)
@section('breadcrumb', $user->name)

@section('content')
<div class="space-y-6"
     x-data="{
        items: @js($invoice->items->map(fn ($i) => ['description' => $i->description, 'quantity' => (float) $i->quantity, 'unit_price' => (float) $i->unit_price])->values()),
        taxRate: {{ (float) $invoice->tax_rate }},
        status: @js($invoice->status),
        addItem() { this.items.push({ description: '', quantity: 1, unit_price: 0 }); },
        removeItem(i) { this.items.splice(i, 1); },
        get subtotal() { return this.items.reduce((s, it) => s + (parseFloat(it.quantity) || 0) * (parseFloat(it.unit_price) || 0), 0); },
        get taxAmount() { return this.subtotal * ((parseFloat(this.taxRate) || 0) / 100); },
        get total() { return this.subtotal + this.taxAmount; },
        money(n) { return '$' + (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
     }">

    {{-- Header --}}
    <div class="card flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <a href="{{ route('staff.customers.show', $user) }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1 mb-1">
                <x-icon name="chevron-left" class="w-3.5 h-3.5" /> Back to {{ $user->name }}
            </a>
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="font-display font-semibold text-lg text-text">Invoice {{ $invoice->number }}</h2>
                <span class="badge {{ $invoice->statusBadgeClass() }}">{{ ucfirst($invoice->status) }}</span>
                @if($invoice->visible_to_customer)
                <span class="badge badge-blue text-[10px]"><x-icon name="eye" class="w-3 h-3 inline" /> shared</span>
                @endif
            </div>
            <p class="text-xs text-muted mt-0.5">
                Created {{ $invoice->created_at->format('M j, Y g:i A') }}
                @if($invoice->agreement) · billed against agreement
                    <a href="{{ route('staff.customers.agreements.edit', [$user, $invoice->agreement]) }}" class="text-primary hover:underline">{{ $invoice->agreement->title }}</a>
                @endif
            </p>
        </div>
        <a href="{{ route('staff.customers.invoices.pdf', [$user, $invoice]) }}" target="_blank" class="btn-ghost btn-sm gap-1.5 shrink-0">
            <x-icon name="document" class="w-3.5 h-3.5" /> PDF
        </a>
    </div>

    <form method="POST" action="{{ route('staff.customers.invoices.update', [$user, $invoice]) }}" enctype="multipart/form-data" class="grid lg:grid-cols-3 gap-6">
        @csrf @method('PATCH')

        {{-- Main column --}}
        <div class="lg:col-span-2 space-y-6">

            @if($errors->any())
            <div class="card p-3 bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            {{-- Line items --}}
            <div class="card space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-text">Line Items</h3>
                    <button type="button" @click="addItem()" class="btn-ghost btn-sm gap-1"><x-icon name="plus" class="w-3.5 h-3.5" /> Add item</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-muted border-b border-border">
                                <th class="py-2 pr-2 font-medium">Description</th>
                                <th class="py-2 px-2 font-medium w-24 text-right">Qty</th>
                                <th class="py-2 px-2 font-medium w-32 text-right">Unit Price</th>
                                <th class="py-2 px-2 font-medium w-32 text-right">Line Total</th>
                                <th class="py-2 pl-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in items" :key="idx">
                                <tr class="border-b border-border/60 align-top">
                                    <td class="py-2 pr-2">
                                        <input type="text" :name="`items[${idx}][description]`" x-model="item.description"
                                               class="input" placeholder="Description of work / item" required>
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" step="0.01" min="0" :name="`items[${idx}][quantity]`"
                                               x-model.number="item.quantity" class="input text-right">
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" step="0.01" min="0" :name="`items[${idx}][unit_price]`"
                                               x-model.number="item.unit_price" class="input text-right">
                                    </td>
                                    <td class="py-2 px-2 text-right text-text whitespace-nowrap" x-text="money((parseFloat(item.quantity)||0) * (parseFloat(item.unit_price)||0))"></td>
                                    <td class="py-2 pl-2 text-right">
                                        <button type="button" @click="removeItem(idx)" class="text-muted hover:text-red-400" title="Remove">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="5" class="py-4 text-center text-sm text-muted">No line items. Click “Add item” to start.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="flex justify-end pt-2">
                    <table class="text-sm w-64">
                        <tr>
                            <td class="py-1 text-muted">Subtotal</td>
                            <td class="py-1 text-right text-text" x-text="money(subtotal)"></td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">
                                Tax
                                <input type="number" step="0.01" min="0" max="100" name="tax_rate" x-model.number="taxRate"
                                       class="input inline-block w-16 text-right ml-1 !py-0.5"> %
                            </td>
                            <td class="py-1 text-right text-text" x-text="money(taxAmount)"></td>
                        </tr>
                        <tr class="border-t border-border">
                            <td class="py-2 font-semibold text-text">Total Due</td>
                            <td class="py-2 text-right font-semibold text-primary text-base" x-text="money(total)"></td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Details --}}
            <div class="card grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="label">Invoice # / Ref</label>
                    <input type="text" name="number" value="{{ old('number', $invoice->number) }}" class="input" required>
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="select" x-model="status">
                        @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('status', $invoice->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Description <span class="text-muted font-normal">(short summary)</span></label>
                    <input type="text" name="description" value="{{ old('description', $invoice->description) }}" class="input" placeholder="Website build — milestone 1">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Summary of Work <span class="text-muted font-normal">(appears on the PDF)</span></label>
                    <textarea name="work_summary" rows="3" class="input resize-y" placeholder="Describe the work performed.">{{ old('work_summary', $invoice->work_summary) }}</textarea>
                </div>
                <div>
                    <label class="label">Issued</label>
                    <input type="date" name="issued_at" value="{{ old('issued_at', $invoice->issued_at?->format('Y-m-d')) }}" class="input">
                </div>
                <div>
                    <label class="label">Due</label>
                    <input type="date" name="due_at" value="{{ old('due_at', $invoice->due_at?->format('Y-m-d')) }}" class="input">
                </div>
                <div x-show="status === 'paid'" x-cloak>
                    <label class="label">Paid on</label>
                    <input type="date" name="paid_at" value="{{ old('paid_at', $invoice->paid_at?->format('Y-m-d')) }}" class="input">
                </div>
                <div class="sm:col-span-2">
                    <label class="label">Internal Notes <span class="text-muted font-normal">(not shown to customer)</span></label>
                    <textarea name="notes" rows="2" class="input resize-none">{{ old('notes', $invoice->notes) }}</textarea>
                </div>
                <label class="sm:col-span-2 flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="visible_to_customer" value="1" class="rounded mt-0.5" {{ old('visible_to_customer', $invoice->visible_to_customer) ? 'checked' : '' }}>
                    <span class="text-sm text-text">Make this invoice visible to the customer
                        <span class="block text-xs text-muted">They can view &amp; download the PDF from their Billing page.</span>
                    </span>
                </label>
                <div class="sm:col-span-2">
                    <label class="label">Attach PDF <span class="text-muted font-normal">(optional, max 10 MB — replaces the generated one)</span></label>
                    @if($invoice->file_path)
                    <p class="text-xs text-muted mb-1">Current: <a href="{{ Storage::url($invoice->file_path) }}" target="_blank" class="text-primary hover:underline">{{ basename($invoice->file_path) }}</a></p>
                    @endif
                    <input type="file" name="file" accept="application/pdf"
                           class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary gap-1.5"><x-icon name="check" class="w-4 h-4" /> Save Invoice</button>
                <a href="{{ route('staff.customers.show', $user) }}" class="btn-ghost btn-sm">Done</a>
            </div>
        </div>

        {{-- Sidebar: summary + audit trail --}}
        <div class="space-y-6">
            {{-- Summary --}}
            <div class="card space-y-2">
                <h3 class="font-semibold text-text">Summary</h3>
                <div class="flex justify-between text-sm"><span class="text-muted">Subtotal</span><span class="text-text" x-text="money(subtotal)"></span></div>
                <div class="flex justify-between text-sm"><span class="text-muted">Tax (<span x-text="(parseFloat(taxRate)||0)"></span>%)</span><span class="text-text" x-text="money(taxAmount)"></span></div>
                <div class="flex justify-between text-base font-semibold border-t border-border pt-2"><span class="text-text">Total</span><span class="text-primary" x-text="money(total)"></span></div>
                <p class="text-[11px] text-muted pt-1">Saved total: ${{ number_format($invoice->amount, 2) }} — recalculated on save.</p>
            </div>

            {{-- Audit trail --}}
            <div class="card">
                <h3 class="font-semibold text-text mb-3">Audit Trail</h3>
                @php
                    $eventBadge = ['created' => 'badge-green', 'status' => 'badge-blue', 'updated' => 'badge-muted'];
                @endphp
                @forelse($invoice->events as $event)
                <div class="flex gap-3 pb-3 mb-3 border-b border-border/60 last:border-0 last:pb-0 last:mb-0">
                    <div class="mt-0.5">
                        <span class="badge {{ $eventBadge[$event->action] ?? 'badge-muted' }} text-[10px] uppercase">{{ $event->action }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-text">{{ $event->description }}</p>
                        <p class="text-[11px] text-muted mt-0.5">
                            {{ $event->actor->name ?? 'System' }} · {{ $event->created_at->format('M j, Y g:i A') }}
                        </p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-muted">No history yet.</p>
                @endforelse
            </div>
        </div>
    </form>
</div>
@endsection
