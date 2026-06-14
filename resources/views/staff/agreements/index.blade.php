@extends('layouts.portal')
@section('title', 'Agreements')
@section('page-title', 'Agreements')
@section('breadcrumb', 'All customer agreements')

@section('content')
<div class="card p-0">
    <div class="p-4 border-b border-border flex items-center gap-3 flex-wrap">
        {{-- Status filter pills --}}
        <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs flex-wrap">
            @foreach($filters as $key => $label)
            <a href="{{ route('staff.agreements.index', ['status' => $key, 'q' => $search]) }}"
               class="px-3 py-1 rounded-full transition-colors {{ $status === $key ? 'bg-primary text-bg font-semibold' : 'text-muted hover:text-text' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Customer search --}}
        <form method="GET" action="{{ route('staff.agreements.index') }}" class="ml-auto flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search by customer…" class="input py-1.5 text-sm w-56">
            <button class="btn-ghost btn-sm"><x-icon name="search" class="w-3.5 h-3.5" /></button>
        </form>
    </div>

    @if($agreements->isEmpty())
    <div class="p-10 text-center">
        <x-icon name="document" class="w-10 h-10 text-border mx-auto mb-3" />
        <p class="text-muted">{{ $search ? 'No agreements match your search.' : 'No agreements found.' }}</p>
    </div>
    @else
    <table class="data-table">
        <thead>
            <tr><th>Customer</th><th>Agreement</th><th>Status</th><th>Cost</th><th>Created</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($agreements as $agreement)
            <tr>
                <td>
                    @if($agreement->customer)
                    <p class="font-medium text-text">{{ $agreement->customer->name }}</p>
                    <p class="text-xs text-muted">{{ $agreement->customer->email }}</p>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-text">{{ $agreement->title }}</td>
                <td>
                    <span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span>
                    @if($agreement->actionNeededForAdmin())<span class="badge badge-amber text-[10px]">validate</span>@endif
                </td>
                <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->total_amount, 2) : '—' }}</td>
                <td class="text-muted text-xs">{{ $agreement->created_at->format('M j, Y') }}</td>
                <td class="text-right">
                    @if($agreement->customer)
                    <a href="{{ route('staff.customers.agreements.edit', [$agreement->customer, $agreement]) }}" class="btn-ghost btn-sm">Open</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $agreements->links() }}</div>
    @endif
</div>
@endsection
