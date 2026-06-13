@extends('layouts.portal')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="card p-0">
    <div class="p-4 border-b border-[var(--color-border)] flex items-center gap-3 flex-wrap">
        <p class="text-sm text-[var(--color-muted)]">{{ $customers->total() }} total customers</p>
        <form method="GET" action="{{ route('staff.customers.index') }}" class="ml-auto flex items-center gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search name, email, company…"
                   class="input py-1.5 text-sm w-56">
            <button class="btn-ghost btn-sm"><x-icon name="search" class="w-3.5 h-3.5" /></button>
        </form>
    </div>
    @if($customers->isEmpty())
    <div class="p-10 text-center text-[var(--color-muted)]">{{ $search ? 'No customers match your search.' : 'No customers yet.' }}</div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Company</th>
                <th>Status</th>
                <th>Demos</th>
                <th>Billed</th>
                <th>Joined</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td>
                    <p class="font-medium text-[var(--color-text)]">{{ $customer->name }}</p>
                    <p class="text-xs text-[var(--color-muted)]">{{ $customer->email }}</p>
                </td>
                <td class="text-[var(--color-muted)]">{{ $customer->company ?? '—' }}</td>
                <td><span class="badge {{ $customer->is_active ? 'badge-green' : 'badge-red' }}">{{ $customer->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-[var(--color-muted)]">{{ $customer->showroom_items_count }}</td>
                <td class="text-[var(--color-muted)]">${{ number_format($customer->invoices_sum_amount ?? 0, 2) }}</td>
                <td class="text-[var(--color-muted)] text-xs">{{ $customer->created_at->format('M j, Y') }}</td>
                <td>
                    <a href="/staff/customers/{{ $customer->id }}" class="btn-ghost btn-sm">Manage</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
