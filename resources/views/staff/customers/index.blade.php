@extends('layouts.portal')
@section('title', 'Customers')
@section('page-title', 'Customers')

@section('content')
<div class="card p-0">
    <div class="p-4 border-b border-[var(--color-border)]">
        <p class="text-sm text-[var(--color-muted)]">{{ $customers->total() }} total customers</p>
    </div>
    @if($customers->isEmpty())
    <div class="p-10 text-center text-[var(--color-muted)]">No customers yet.</div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Demos</th>
                <th>Joined</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
            <tr>
                <td class="font-medium text-[var(--color-text)]">{{ $customer->name }}</td>
                <td class="text-[var(--color-muted)]">{{ $customer->email }}</td>
                <td class="text-[var(--color-muted)]">{{ $customer->company ?? '—' }}</td>
                <td class="text-[var(--color-muted)]">—</td>
                <td class="text-[var(--color-muted)] text-xs">{{ $customer->created_at->format('M j, Y') }}</td>
                <td>
                    <a href="/staff/customers/{{ $customer->id }}" class="btn-ghost btn-sm">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
