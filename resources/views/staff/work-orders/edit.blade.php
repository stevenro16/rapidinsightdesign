@extends('layouts.portal')
@section('title', 'Work Order')
@section('page-title', 'Work Order')
@section('breadcrumb', $workOrder->customer->name)

@php $locked = $workOrder->isLocked(); @endphp

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="card flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <a href="{{ route('staff.work-orders.index') }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1 mb-1">
                <x-icon name="chevron-left" class="w-3.5 h-3.5" /> All work orders
            </a>
            <div class="flex items-center gap-2">
                <h2 class="font-display font-semibold text-lg text-text">{{ $workOrder->title }}</h2>
                <span class="badge {{ $workOrder->statusBadgeClass() }}">{{ $workOrder->statusLabel() }}</span>
            </div>
            <p class="text-xs text-muted mt-0.5">
                <a href="{{ route('staff.customers.show', $workOrder->customer) }}" class="hover:text-primary">{{ $workOrder->customer->name }}</a>
                · created {{ $workOrder->created_at->format('M j, Y') }}
            </p>
        </div>
        <form method="POST" action="{{ route('staff.work-orders.destroy', $workOrder) }}" x-data="confirmDelete('Delete this work order? Agreements will be detached, not deleted.')">
            @csrf @method('DELETE')
            <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-[var(--color-danger)] gap-1.5"><x-icon name="trash" class="w-3.5 h-3.5" /> Delete</button>
        </form>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left: details + agreements + invoices + notes --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Project details --}}
            <form method="POST" action="{{ route('staff.work-orders.update', $workOrder) }}" class="card space-y-3">
                @csrf @method('PATCH')
                @if($errors->any())
                <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif
                <h3 class="font-semibold text-text">Project Details</h3>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2"><label class="label">Title</label><input type="text" name="title" value="{{ old('title', $workOrder->title) }}" class="input" required @disabled($locked)></div>
                    <div class="sm:col-span-2"><label class="label">Summary <span class="text-muted font-normal">(shown to customer)</span></label><input type="text" name="summary" value="{{ old('summary', $workOrder->summary) }}" class="input" @disabled($locked)></div>
                    <div><label class="label">Website URL <span class="text-muted font-normal">(shown to customer)</span></label><input type="text" name="website_url" value="{{ old('website_url', $workOrder->website_url) }}" class="input" placeholder="https://…" @disabled($locked)></div>
                    <div><label class="label">Hosting <span class="text-muted font-normal">(internal)</span></label><input type="text" name="hosting" value="{{ old('hosting', $workOrder->hosting) }}" class="input" @disabled($locked)></div>
                    <div class="sm:col-span-2"><label class="label">Tech stack <span class="text-muted font-normal">(internal)</span></label><input type="text" name="tech_stack" value="{{ old('tech_stack', $workOrder->tech_stack) }}" class="input" placeholder="Laravel, Alpine.js, MySQL…" @disabled($locked)></div>
                    <div class="sm:col-span-2"><label class="label">Details / scratchpad <span class="text-muted font-normal">(internal)</span></label><textarea name="details" rows="6" class="input resize-y" @disabled($locked)>{{ old('details', $workOrder->details) }}</textarea></div>
                </div>
                @unless($locked)<div><button class="btn-primary btn-sm">Save Details</button></div>@endunless
            </form>

            {{-- Agreements --}}
            <div class="card space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <h3 class="font-semibold text-text">Agreements</h3>
                    <form method="POST" action="{{ route('staff.customers.agreements.store', $workOrder->customer) }}">
                        @csrf
                        <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">
                        <button class="btn-primary btn-sm gap-1.5"><x-icon name="plus" class="w-3.5 h-3.5" />New Agreement</button>
                    </form>
                </div>
                @forelse($workOrder->agreements as $agreement)
                <div class="flex items-center justify-between gap-2 rounded-lg bg-surface-2 px-3 py-2">
                    <div class="min-w-0">
                        <p class="text-sm text-text truncate">{{ $agreement->title }}</p>
                        <p class="text-xs text-muted"><span class="badge {{ $agreement->statusBadgeClass() }} text-[10px]">{{ $agreement->statusLabel() }}</span>
                            @if($agreement->has_cost) · ${{ number_format($agreement->total_amount, 2) }}@endif</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('staff.customers.agreements.edit', [$workOrder->customer, $agreement]) }}" class="btn-ghost btn-sm">Open</a>
                        <form method="POST" action="{{ route('staff.work-orders.agreements.detach', [$workOrder, $agreement]) }}">
                            @csrf @method('DELETE')
                            <button class="btn-ghost btn-sm text-[var(--color-danger)]" title="Detach"><x-icon name="x" class="w-3.5 h-3.5" /></button>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-sm text-muted">No agreements linked.</p>
                @endforelse

                @if($availableAgreements->isNotEmpty())
                <form method="POST" action="#" class="flex gap-2 pt-1" id="attach-{{ $workOrder->id }}">
                    @csrf
                    <select class="select flex-1" id="attach-sel-{{ $workOrder->id }}">
                        <option value="">— Attach an existing agreement —</option>
                        @foreach($availableAgreements as $ag)
                        <option value="{{ $ag->id }}">{{ $ag->title }} ({{ $ag->statusLabel() }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-ghost btn-sm whitespace-nowrap"
                            @click.prevent="const v=document.getElementById('attach-sel-{{ $workOrder->id }}').value; if(v){ const f=$el.closest('form'); f.action='/staff/work-orders/{{ $workOrder->id }}/agreements/'+v+'/attach'; f.submit(); }">
                        Attach
                    </button>
                </form>
                @endif
            </div>

            {{-- Invoices (rolled up via agreements) --}}
            @php $invoices = $workOrder->invoices()->get(); @endphp
            @if($invoices->isNotEmpty())
            <div class="card space-y-2">
                <h3 class="font-semibold text-text">Invoices</h3>
                @foreach($invoices as $inv)
                <div class="flex items-center justify-between text-sm border-b border-border py-2">
                    <span class="text-text">{{ $inv->number }} <span class="text-xs text-muted">· ${{ number_format($inv->amount, 2) }}</span></span>
                    <span class="badge {{ $inv->statusBadgeClass() }} text-[10px]">{{ $inv->status }}</span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Notes --}}
            <div class="card space-y-3">
                <h3 class="font-semibold text-text">Notes</h3>
                <form method="POST" action="{{ route('staff.work-orders.notes.store', $workOrder) }}" class="space-y-2">
                    @csrf
                    <textarea name="body" rows="2" required class="input resize-none" placeholder="Add a note…"></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-muted cursor-pointer">
                            <input type="checkbox" name="visible_to_customer" value="1" class="rounded"> Visible to customer
                        </label>
                        <button class="btn-primary btn-sm">Add Note</button>
                    </div>
                </form>
                @forelse($workOrder->notes as $note)
                <div class="flex items-start justify-between gap-2 border-b border-border pb-2">
                    <div class="min-w-0">
                        <p class="text-sm text-text whitespace-pre-line">{{ $note->body }}</p>
                        <p class="text-xs text-muted mt-0.5">
                            {{ $note->author?->name ?? 'System' }} · {{ $note->created_at->format('M j, Y g:i A') }}
                            <span class="badge {{ $note->visible_to_customer ? 'badge-blue' : 'badge-muted' }} text-[10px] ml-1">{{ $note->visible_to_customer ? 'customer' : 'internal' }}</span>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('staff.work-orders.notes.destroy', [$workOrder, $note]) }}">
                        @csrf @method('DELETE')
                        <button class="btn-ghost btn-sm text-[var(--color-danger)]"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-muted">No notes yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Right: status control + audit trail --}}
        <div class="space-y-6">
            <div class="card space-y-3">
                <h3 class="font-semibold text-text">Status</h3>
                <p><span class="badge {{ $workOrder->statusBadgeClass() }}">{{ $workOrder->statusLabel() }}</span></p>

                @if($workOrder->awaitingCustomer())
                <div class="text-xs rounded-lg border p-3 {{ $workOrder->customerValidated() ? 'border-primary/40 text-primary' : 'border-amber-500/40 text-amber-400' }}">
                    {{ $workOrder->customerValidated()
                        ? 'Customer approved on ' . $workOrder->customer_validated_at->format('M j, Y')
                        : 'Waiting on the customer to approve.' }}
                </div>
                @endif

                <form method="POST" action="{{ route('staff.work-orders.status', $workOrder) }}" class="space-y-2">
                    @csrf @method('PATCH')
                    <select name="status" class="select">
                        @foreach(\App\Models\WorkOrder::STATUSES as $s)
                        <option value="{{ $s }}" {{ $workOrder->status === $s ? 'selected' : '' }}>{{ \App\Models\WorkOrder::statusLabelFor($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn-primary btn-sm w-full justify-center">Update Status</button>
                </form>
                <p class="text-[11px] text-muted">Setting “Awaiting validation” emails the customer to approve.</p>
            </div>

            {{-- Audit trail --}}
            <div class="card">
                <h3 class="font-semibold text-text mb-3">Audit Trail</h3>
                <div class="space-y-3">
                    @forelse($workOrder->events as $event)
                    <div class="flex gap-2">
                        <div class="w-2 h-2 rounded-full bg-primary/60 mt-1.5 shrink-0"></div>
                        <div class="min-w-0">
                            <p class="text-sm text-text">{{ $event->description }}</p>
                            <p class="text-xs text-muted">{{ $event->actor?->name ?? 'Customer' }} · {{ $event->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-muted">No activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
