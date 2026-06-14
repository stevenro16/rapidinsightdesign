@extends('layouts.portal')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stat cards (single row) --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach([
            ['Active Agreements',     $stats['agreements'],  'document', '/staff/agreements?status=active'],
            ['Active Work Orders',    $stats['work_orders'], 'grid',     '/staff/work-orders?status=active'],
            ['Active Inquiries',      $stats['inquiries'],   'inbox',    '/staff/inquiries?filter=active'],
            ['Shortlisted Prospects', $stats['shortlisted'], 'star',     '/admin/prospects?status=shortlisted'],
        ] as [$label, $value, $icon, $link])
        <a href="{{ $link }}" class="card card-hover flex items-center gap-4 group no-underline">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary-glow border border-primary/30 group-hover:shadow-md transition-shadow shrink-0">
                <x-icon name="{{ $icon }}" class="w-5 h-5 text-primary" />
            </div>
            <div>
                <p class="text-2xl font-display font-bold text-text">{{ $value }}</p>
                <p class="text-xs text-muted">{{ $label }}</p>
            </div>
        </a>
        @endforeach

        {{-- Unpaid invoices (issued + overdue) — opens the full Invoices section --}}
        <a href="/staff/invoices" class="card card-hover flex items-center gap-4 group no-underline">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary-glow border border-primary/30 group-hover:shadow-md transition-shadow shrink-0">
                <x-icon name="document" class="w-5 h-5 text-primary" />
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-display font-bold {{ $stats['unpaid_amount'] > 0 ? 'text-amber-400' : 'text-text' }}">${{ number_format($stats['unpaid_amount'], 2) }}</p>
                <p class="text-xs text-muted">Unpaid Invoices</p>
                <p class="text-[11px] text-muted">{{ $stats['unpaid_count'] }} invoice{{ $stats['unpaid_count'] === 1 ? '' : 's' }}</p>
            </div>
        </a>

        {{-- Collected (YTD by default, toggle to all-time) --}}
        <div class="card flex items-center gap-4" x-data="{ range: 'ytd' }">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-primary-glow border border-primary/30 shrink-0">
                <x-icon name="chart" class="w-5 h-5 text-primary" />
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-display font-bold text-primary">
                    <span x-show="range==='ytd'">${{ number_format($stats['collected_ytd'], 2) }}</span>
                    <span x-show="range==='all'" x-cloak>${{ number_format($stats['collected_all'], 2) }}</span>
                </p>
                <p class="text-xs text-muted">Collected</p>
                <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-[10px] mt-1">
                    <button type="button" @click="range='ytd'" :class="range==='ytd' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-2 py-0.5 rounded-full">YTD</button>
                    <button type="button" @click="range='all'" :class="range==='all' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-2 py-0.5 rounded-full">All</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Active inquiries --}}
    @if($activeInquiries->isNotEmpty())
    <div class="card p-0">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h3 class="font-semibold text-text">Active Inquiries</h3>
            <a href="/staff/inquiries" class="text-sm text-primary hover:underline">Manage all</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Subject</th><th>From</th><th>Status</th><th>Received</th></tr></thead>
            <tbody>
                @foreach($activeInquiries as $inq)
                <tr class="cursor-pointer" onclick="window.location.href='{{ route('staff.inquiries.show', $inq) }}'">
                    <td class="font-medium text-text">{{ $inq->subject }}</td>
                    <td class="text-muted">
                        {{ $inq->user->name ?? $inq->name }}
                        <span class="block text-xs">{{ $inq->user->email ?? $inq->email }}</span>
                    </td>
                    <td><span class="badge {{ $inq->statusBadgeClass() }}">{{ $inq->statusLabel() }}</span></td>
                    <td class="text-muted text-xs">{{ $inq->created_at->diffForHumans() }}</td>
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
            <a href="/staff/work-orders" class="text-sm text-primary hover:underline">Manage all</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Project</th><th>Customer</th><th>Status</th><th>Updated</th></tr></thead>
            <tbody>
                @foreach($activeWorkOrders as $wo)
                <tr class="cursor-pointer" onclick="window.location.href='{{ route('staff.work-orders.edit', $wo) }}'">
                    <td class="font-medium text-text">{{ $wo->title }}</td>
                    <td class="text-muted">{{ $wo->customer->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $wo->statusBadgeClass() }}">{{ $wo->statusLabel() }}</span>
                        @if($wo->awaitingCustomer() && ! $wo->customerValidated())<span class="badge badge-amber text-[10px]">awaiting customer</span>@endif
                    </td>
                    <td class="text-muted text-xs">{{ $wo->updated_at->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Active invoices --}}
    @if($activeInvoices->isNotEmpty())
    <div class="card p-0">
        <div class="p-4 border-b border-border flex items-center justify-between">
            <h3 class="font-semibold text-text">Active Invoices</h3>
            <a href="/staff/invoices" class="text-sm text-primary hover:underline">Manage all</a>
        </div>
        <table class="data-table">
            <thead><tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Status</th><th>Due</th></tr></thead>
            <tbody>
                @foreach($activeInvoices as $inv)
                <tr class="cursor-pointer" onclick="window.location.href='{{ route('staff.customers.invoices.edit', [$inv->customer, $inv]) }}'">
                    <td class="font-medium text-text">{{ $inv->number }}</td>
                    <td class="text-muted">{{ $inv->customer->name ?? '—' }}</td>
                    <td class="text-text">${{ number_format($inv->amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $inv->statusBadgeClass() }}">{{ $inv->status }}</span>
                        @if($inv->isOverdue())<span class="badge badge-red text-[10px]">overdue</span>@endif
                    </td>
                    <td class="text-xs {{ $inv->isOverdue() ? 'text-red-400' : 'text-muted' }}">{{ $inv->due_at?->format('M j, Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Nothing active --}}
    @if($activeInquiries->isEmpty() && $activeWorkOrders->isEmpty() && $activeInvoices->isEmpty())
    <div class="card text-center py-10">
        <x-icon name="check" class="w-8 h-8 text-primary mx-auto mb-2" />
        <p class="text-text font-medium">All clear</p>
        <p class="text-sm text-muted">No active inquiries, work orders, or invoices right now.</p>
    </div>
    @endif
</div>
@endsection
