@extends('layouts.portal')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Welcome back, ' . auth()->user()->name)

@section('content')
<div class="space-y-6">

@if($isNewCustomer)
    {{-- ── Onboarding welcome (brand-new customer) ───────────────────────── --}}
    <div class="card text-center py-10">
        <div class="w-14 h-14 rounded-full bg-primary/15 border border-primary/40 flex items-center justify-center mx-auto mb-4">
            <x-icon name="bolt" class="w-7 h-7 text-primary" />
        </div>
        <h2 class="text-2xl font-display font-bold text-text mb-2">Welcome to RapidInsight Designs!</h2>
        <p class="text-muted max-w-xl mx-auto">We're happy to have you, {{ auth()->user()->name }}. Take a look at what we build in our ShowRoom below — and whenever you're ready, start a project with us.</p>
    </div>

    {{-- ShowRoom preview --}}
    @if($showroomItems->isNotEmpty())
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-text">Take a look at our ShowRoom</h3>
            <a href="/showroom" class="text-sm text-primary hover:underline">View all →</a>
        </div>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($showroomItems as $item)
            @php $status = $access[$item->id] ?? null; @endphp
            <div class="card card-hover flex flex-col">
                <div class="h-36 rounded-lg mb-4 overflow-hidden bg-surface-2 flex items-center justify-center">
                    @if($item->thumbnail_path)
                    <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                    @else
                    <x-icon name="computer" class="w-10 h-10 text-border" />
                    @endif
                </div>
                <div class="flex-1">
                    <h4 class="font-display font-semibold text-text">{{ $item->title }}</h4>
                    @if($item->description)<p class="text-sm text-muted mt-1 line-clamp-2">{{ $item->description }}</p>@endif
                    @if($item->tech_tags)
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($item->techTagsArray() as $tag)<span class="badge badge-green text-xs">{{ trim($tag) }}</span>@endforeach
                    </div>
                    @endif
                </div>
                <div class="mt-3">
                    @if($status === 'approved')
                    <a href="/showroom" class="btn-primary w-full justify-center gap-1.5"><x-icon name="external" class="w-4 h-4" /> Launch</a>
                    @elseif($status === 'pending')
                    <button type="button" disabled class="btn-ghost w-full justify-center opacity-60 cursor-not-allowed"><x-icon name="lock" class="w-4 h-4" /> Awaiting approval</button>
                    @else
                    <form method="POST" action="{{ route('showroom.request', $item) }}">
                        @csrf
                        <button class="btn-primary w-full justify-center gap-1.5"><x-icon name="lock" class="w-4 h-4" /> Request Access</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Start a project CTA --}}
    <div class="card text-center py-8 border border-primary/30 bg-primary/5">
        <h3 class="text-xl font-display font-bold text-text mb-1">Ready to start your project?</h3>
        <p class="text-muted mb-4 max-w-lg mx-auto">Let us know when you're ready — submit an inquiry and track our conversation right here in your portal.</p>
        <a href="{{ route('inquiries.index') }}" class="btn-primary gap-1.5 animate-glow-pulse"><x-icon name="chat" class="w-4 h-4" /> Start a Project</a>
    </div>
@else

    {{-- Action-needed banner --}}
    @if($actionNeeded->isNotEmpty() || $woActionNeeded->isNotEmpty())
    <div class="card border border-amber-500/40 bg-amber-500/10">
        <p class="font-semibold text-text mb-2 flex items-center gap-2">
            <x-icon name="warning" class="w-4 h-4 text-amber-400" /> Action needed
        </p>
        <div class="space-y-2">
            @foreach($actionNeeded as $agreement)
            <a href="{{ route('agreements.show', $agreement) }}"
               class="flex items-center justify-between gap-3 rounded-lg bg-surface-2 px-3 py-2 hover:border-primary/40 border border-transparent transition-colors">
                <span class="text-sm text-text">Review &amp; sign: <strong>{{ $agreement->title }}</strong></span>
                <span class="btn-primary btn-sm shrink-0">Open <x-icon name="arrow-right" class="w-3.5 h-3.5" /></span>
            </a>
            @endforeach
            @foreach($woActionNeeded as $wo)
            <a href="{{ route('work-orders.show', $wo) }}"
               class="flex items-center justify-between gap-3 rounded-lg bg-surface-2 px-3 py-2 hover:border-primary/40 border border-transparent transition-colors">
                <span class="text-sm text-text">Approve project: <strong>{{ $wo->title }}</strong></span>
                <span class="btn-primary btn-sm shrink-0">Open <x-icon name="arrow-right" class="w-3.5 h-3.5" /></span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Stat tiles --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <a href="{{ route('agreements.index') }}" class="card card-hover">
            <p class="label">Agreements</p>
            <p class="text-2xl font-display font-bold text-text">{{ $stats['agreements'] }}</p>
            @if($stats['action'] > 0)<p class="text-xs text-amber-400 mt-0.5">{{ $stats['action'] }} need action</p>@else<p class="text-xs text-muted mt-0.5">All up to date</p>@endif
        </a>
        <a href="{{ route('work-orders.index') }}" class="card card-hover">
            <p class="label">Work Orders</p>
            <p class="text-2xl font-display font-bold text-text">{{ $stats['work_orders'] }}</p>
            @if($stats['wo_action'] > 0)<p class="text-xs text-amber-400 mt-0.5">{{ $stats['wo_action'] }} need approval</p>@else<p class="text-xs text-muted mt-0.5">Your projects</p>@endif
        </a>
        <a href="/showroom" class="card card-hover">
            <p class="label">Demos</p>
            <p class="text-2xl font-display font-bold text-primary">{{ $stats['demos'] }}</p>
            <p class="text-xs text-muted mt-0.5">ShowRoom access</p>
        </a>
        <a href="/billing" class="card card-hover">
            <p class="label">Outstanding</p>
            <p class="text-2xl font-display font-bold {{ $stats['outstanding'] > 0 ? 'text-amber-400' : 'text-text' }}">${{ number_format($stats['outstanding'], 2) }}</p>
            <p class="text-xs text-muted mt-0.5">{{ $stats['invoices'] }} invoice{{ $stats['invoices'] === 1 ? '' : 's' }}</p>
        </a>
        <div class="card">
            <p class="label">Inquiries</p>
            <p class="text-2xl font-display font-bold text-text">{{ $stats['inquiries'] }}</p>
            <p class="text-xs text-muted mt-0.5">Submitted</p>
        </div>
    </div>

    {{-- Active invoices --}}
    @if($activeInvoices->isNotEmpty())
    <div class="card p-0">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h3 class="font-semibold text-text">Active Invoices</h3>
            <a href="/billing" class="text-sm text-primary hover:underline">View all</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Invoice</th><th>Description</th><th>Amount</th><th>Status</th><th>Due</th></tr></thead>
            <tbody>
                @foreach($activeInvoices as $invoice)
                <tr class="cursor-pointer" onclick="window.location.href='{{ route('billing.show', $invoice) }}'">
                    <td class="font-medium text-text">{{ $invoice->number }}</td>
                    <td class="text-muted">{{ $invoice->description ?? '—' }}</td>
                    <td class="text-text">${{ number_format($invoice->amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->status }}</span>
                        @if($invoice->isOverdue())<span class="badge badge-red text-[10px]">overdue</span>@endif
                    </td>
                    <td class="text-xs {{ $invoice->isOverdue() ? 'text-red-400' : 'text-muted' }}">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Active work orders --}}
    @if($activeWorkOrders->isNotEmpty())
    <div class="card p-0">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h3 class="font-semibold text-text">Active Work Orders</h3>
            <a href="{{ route('work-orders.index') }}" class="text-sm text-primary hover:underline">View all</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Project</th><th>Latest update</th><th>Status</th><th>Updated</th></tr></thead>
            <tbody>
                @foreach($activeWorkOrders as $wo)
                @php $lastNote = $wo->lastCustomerVisibleNote(); @endphp
                <tr class="cursor-pointer" onclick="window.location.href='{{ route('work-orders.show', $wo) }}'">
                    <td>
                        <p class="font-medium text-text">{{ $wo->title }}</p>
                        @if($wo->summary)<p class="text-xs text-muted">{{ $wo->summary }}</p>@endif
                        @if($wo->website_url)
                        <a href="{{ $wo->website_url }}" target="_blank" rel="noopener" onclick="event.stopPropagation()"
                           class="text-xs text-primary hover:underline inline-flex items-center gap-1 mt-0.5">
                            <x-icon name="external" class="w-3 h-3" /> Visit site
                        </a>
                        @endif
                    </td>
                    <td class="text-muted">{{ $lastNote ? Str::limit($lastNote->body, 60) : '—' }}</td>
                    <td>
                        <span class="badge {{ $wo->statusBadgeClass() }}">{{ $wo->statusLabel() }}</span>
                        @if($wo->awaitingCustomer() && ! $wo->customerValidated())<span class="badge badge-amber text-[10px]">needs your OK</span>@endif
                    </td>
                    <td class="text-muted text-xs">{{ $wo->updated_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Recent agreements --}}
    <div class="card p-0">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h3 class="font-semibold text-text">Your Agreements</h3>
            <a href="{{ route('agreements.index') }}" class="text-sm text-primary hover:underline">View all</a>
        </div>
        @if($agreements->isEmpty())
        <p class="p-6 text-sm text-muted text-center">No agreements yet. When we send you one, it'll appear here.</p>
        @else
        <table class="data-table">
            <thead><tr><th>Agreement</th><th>Status</th><th>Total</th><th>Balance</th><th></th></tr></thead>
            <tbody>
                @foreach($agreements->take(5) as $agreement)
                <tr class="cursor-pointer" onclick="window.location.href='{{ route('agreements.show', $agreement) }}'">
                    <td class="text-text font-medium">{{ $agreement->title }}</td>
                    <td><span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span></td>
                    <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->total_amount, 2) : '—' }}</td>
                    <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->balance(), 2) : '—' }}</td>
                    <td class="text-right"><a href="{{ route('agreements.show', $agreement) }}" onclick="event.stopPropagation()" class="text-sm text-primary hover:underline">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
@endif
</div>
@endsection
