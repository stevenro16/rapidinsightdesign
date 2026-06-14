@extends('layouts.portal')
@section('title', $workOrder->title)
@section('page-title', 'Project')
@section('breadcrumb', $workOrder->title)

@php
    $steps = [
        ['key' => 'new', 'label' => 'Received'],
        ['key' => 'in_progress', 'label' => 'In Progress'],
        ['key' => 'awaiting_customer_validation', 'label' => 'Your Approval'],
        ['key' => 'completed', 'label' => 'Completed'],
    ];
    $order        = ['new' => 0, 'in_progress' => 1, 'awaiting_customer_validation' => 2, 'completed' => 3];
    $current      = $order[$workOrder->status] ?? 0;
    $canceled     = $workOrder->status === 'canceled';
    $agreements   = $workOrder->agreements->where('status', '!=', 'draft');
@endphp

@section('content')
<div class="space-y-6">

    {{-- Hero header --}}
    <div class="card">
        <a href="{{ route('work-orders.index') }}" class="text-xs text-muted hover:text-primary inline-flex items-center gap-1 mb-3">
            <x-icon name="chevron-left" class="w-3.5 h-3.5" /> All projects
        </a>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-display font-bold text-text">{{ $workOrder->title }}</h1>
                    <span class="badge {{ $workOrder->statusBadgeClass() }}">{{ $workOrder->statusLabel() }}</span>
                </div>
                @if($workOrder->summary)<p class="text-muted mt-1">{{ $workOrder->summary }}</p>@endif
            </div>
            @if($workOrder->website_url)
            <a href="{{ $workOrder->website_url }}" target="_blank" rel="noopener" class="btn-primary gap-2 shrink-0 animate-glow-pulse text-base px-5 py-2.5">
                <x-icon name="external" class="w-5 h-5" /> See Your Demo
            </a>
            @endif
        </div>
        <div class="flex flex-wrap gap-x-6 gap-y-1 mt-4 text-xs text-muted">
            <span>Started {{ $workOrder->created_at->format('M j, Y') }}</span>
            <span>Last update {{ $workOrder->updated_at->diffForHumans() }}</span>
            @if($agreements->isNotEmpty())<span>{{ $agreements->count() }} {{ Str::plural('agreement', $agreements->count()) }}</span>@endif
        </div>
    </div>

    {{-- Progress --}}
    @if($canceled)
    <div class="card border border-red-500/30 bg-red-500/5">
        <p class="text-sm text-red-400 flex items-center gap-2"><x-icon name="warning" class="w-4 h-4" /> This project has been canceled. Reach out if you have any questions.</p>
    </div>
    @else
    <div class="card">
        <p class="label mb-4">Progress</p>
        <div class="overflow-x-auto">
            <div class="flex items-start min-w-[480px]">
                @foreach($steps as $i => $step)
                @php $done = $i < $current; $isCurrent = $i === $current; @endphp
                <div class="flex flex-col items-center text-center w-24 shrink-0">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $done ? 'bg-primary text-bg' : ($isCurrent ? 'bg-primary/15 text-primary border-2 border-primary' : 'bg-surface-2 text-muted border border-border') }}">
                        @if($done)<x-icon name="check" class="w-5 h-5" />@else{{ $i + 1 }}@endif
                    </div>
                    <span class="text-[11px] mt-2 leading-tight {{ $isCurrent ? 'text-text font-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                </div>
                @if(! $loop->last)
                <div class="flex-1 h-0.5 mt-4 rounded {{ $i < $current ? 'bg-primary' : 'bg-border' }}"></div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Approval prompt --}}
    @if($workOrder->awaitingCustomer())
    <div class="card border {{ $workOrder->customerValidated() ? 'border-primary/40' : 'border-amber-500/40 bg-amber-500/10' }}">
        @if($workOrder->customerValidated())
        <p class="text-sm text-primary flex items-center gap-2"><x-icon name="check" class="w-4 h-4" /> Thank you! You approved this on {{ $workOrder->customer_validated_at->format('M j, Y') }}. We'll finalize it shortly.</p>
        @else
        <p class="font-semibold text-text mb-1">Your approval is requested</p>
        <p class="text-sm text-muted mb-3">Please review the project and confirm everything looks good. We'll wrap things up once you approve.</p>
        <form method="POST" action="{{ route('work-orders.validate', $workOrder) }}">
            @csrf
            <button class="btn-primary gap-1.5"><x-icon name="check" class="w-4 h-4" /> Approve this project</button>
        </form>
        @endif
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main: updates + agreements --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Project updates timeline --}}
            <div class="card">
                <h3 class="font-semibold text-text mb-4 flex items-center gap-2"><x-icon name="chat" class="w-4 h-4 text-primary" /> Project Updates</h3>
                @if($workOrder->notes->isEmpty())
                <p class="text-sm text-muted">No updates have been posted yet. We'll keep you posted here as the project moves along.</p>
                @else
                <div class="space-y-0">
                    @foreach($workOrder->notes as $note)
                    @php $mine = $note->author_id === $workOrder->user_id; @endphp
                    <div class="flex gap-3 pb-4 ml-1.5 pl-5 relative {{ ! $loop->last ? 'border-l border-border' : '' }}">
                        <span class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full {{ $mine ? 'bg-muted' : 'bg-primary' }}"></span>
                        <div class="min-w-0">
                            <p class="text-sm text-text whitespace-pre-line leading-relaxed">{{ $note->body }}</p>
                            <p class="text-xs text-muted mt-1">{{ $mine ? 'You' : 'RapidInsight Designs' }} · {{ $note->created_at->format('M j, Y · g:i A') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Agreements --}}
            @if($agreements->isNotEmpty())
            <div class="card">
                <h3 class="font-semibold text-text mb-4 flex items-center gap-2"><x-icon name="document" class="w-4 h-4 text-primary" /> Agreements</h3>
                <div class="space-y-2">
                    @foreach($agreements as $agreement)
                    <a href="{{ route('agreements.show', $agreement) }}"
                       class="flex items-center justify-between gap-3 rounded-lg bg-surface-2 px-4 py-3 border border-transparent hover:border-primary/40 transition-colors">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-text truncate">{{ $agreement->title }}</p>
                            <p class="text-xs mt-0.5">
                                <span class="badge {{ $agreement->statusBadgeClass() }} text-[10px]">{{ $agreement->statusLabel() }}</span>
                                @if($agreement->has_cost)<span class="text-muted ml-1">${{ number_format($agreement->total_amount, 2) }}</span>@endif
                            </p>
                        </div>
                        <span class="text-sm text-primary shrink-0">{{ $agreement->actionNeededForCustomer() ? 'Review & sign' : 'View' }} →</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar: at a glance + billing + help --}}
        <div class="space-y-6">
            <div class="card">
                <h3 class="font-semibold text-text mb-3">At a glance</h3>
                <dl class="text-sm divide-y divide-border">
                    <div class="flex justify-between py-2"><dt class="text-muted">Status</dt><dd><span class="badge {{ $workOrder->statusBadgeClass() }}">{{ $workOrder->statusLabel() }}</span></dd></div>
                    <div class="flex justify-between py-2"><dt class="text-muted">Started</dt><dd class="text-text">{{ $workOrder->created_at->format('M j, Y') }}</dd></div>
                    <div class="flex justify-between py-2"><dt class="text-muted">Last update</dt><dd class="text-text">{{ $workOrder->updated_at->diffForHumans() }}</dd></div>
                    @if($workOrder->completed_at)<div class="flex justify-between py-2"><dt class="text-muted">Completed</dt><dd class="text-text">{{ $workOrder->completed_at->format('M j, Y') }}</dd></div>@endif
                </dl>
            </div>

            {{-- Billing for this project --}}
            @if($workOrder->invoices->isNotEmpty())
            <div class="card">
                <h3 class="font-semibold text-text mb-3">Billing</h3>
                <div class="space-y-2">
                    @foreach($workOrder->invoices as $invoice)
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <div class="min-w-0">
                            <p class="text-text">{{ $invoice->number }} <span class="text-xs text-muted">· ${{ number_format($invoice->amount, 2) }}</span></p>
                            <span class="badge {{ $invoice->statusBadgeClass() }} text-[10px]">{{ $invoice->status }}</span>
                        </div>
                        <a href="{{ route('billing.pdf', $invoice) }}" target="_blank" class="btn-ghost btn-sm shrink-0" title="View PDF"><x-icon name="document" class="w-3.5 h-3.5" /></a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Send a message --}}
            <div class="card">
                <h3 class="font-semibold text-text mb-1">Send a message</h3>
                <p class="text-xs text-muted mb-3">Questions or notes about this project? Post a message and our team is notified right away.</p>
                <form method="POST" action="{{ route('work-orders.notes.store', $workOrder) }}" class="space-y-2">
                    @csrf
                    <textarea name="body" rows="3" required class="input resize-none" placeholder="Type your message…"></textarea>
                    <button class="btn-primary btn-sm w-full justify-center gap-1.5"><x-icon name="chat" class="w-3.5 h-3.5" /> Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
