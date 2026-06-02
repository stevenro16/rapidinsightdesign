@extends('layouts.portal')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="flex justify-between items-center mb-4">
    <p class="text-sm text-[var(--color-muted)]">{{ $users->total() }} total users</p>
    <a href="/admin/users/create" class="btn-primary btn-sm">
        <x-icon name="plus" class="w-4 h-4" />
        New User
    </a>
</div>

<div class="card p-0">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Company</th>
                <th>Joined</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td class="font-medium text-[var(--color-text)]">{{ $user->name }}</td>
                <td class="text-[var(--color-muted)]">{{ $user->email }}</td>
                <td>
                    <span class="badge {{ $user->isAdmin() ? 'badge-green' : ($user->isStaff() ? 'badge-blue' : 'badge-muted') }}">
                        {{ $user->role }}
                    </span>
                </td>
                <td class="text-[var(--color-muted)]">{{ $user->company ?? '—' }}</td>
                <td class="text-[var(--color-muted)] text-xs">{{ $user->created_at->format('M j, Y') }}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <a href="/admin/users/{{ $user->id }}/edit" class="btn-ghost btn-sm">Edit</a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="/admin/users/{{ $user->id }}" x-data="confirmDelete('Delete {{ addslashes($user->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" @click.prevent="confirm($el.closest('form'))" class="btn-danger btn-sm">
                                <x-icon name="trash" class="w-3.5 h-3.5" />
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4">{{ $users->links() }}</div>
</div>
@endsection
