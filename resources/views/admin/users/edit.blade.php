@extends('layouts.portal')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb', $user->name)

@section('content')
<div class="max-w-lg">
    <div class="card">
        <form method="POST" action="/admin/users/{{ $user->id }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                    @error('name')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required>
                    @error('email')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Role</label>
                    <select name="role" class="select" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach(['customer', 'staff', 'admin'] as $role)
                        <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p class="text-xs text-[var(--color-muted)] mt-1">Cannot change your own role.</p>
                    @endif
                </div>
                <div>
                    <label class="label">Company (optional)</label>
                    <input type="text" name="company" value="{{ old('company', $user->company) }}" class="input">
                </div>
                <div>
                    <label class="label">Internal Notes</label>
                    <textarea name="notes" rows="3" class="input resize-none">{{ old('notes', $user->notes) }}</textarea>
                </div>
                <div>
                    <label class="label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="input" placeholder="••••••••">
                    @error('password')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="/admin/users" class="btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
