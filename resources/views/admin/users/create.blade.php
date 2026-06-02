@extends('layouts.portal')
@section('title', 'New User')
@section('page-title', 'Create User')

@section('content')
<div class="max-w-lg">
    <div class="card">
        <form method="POST" action="/admin/users">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input" required>
                    @error('name')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input" required>
                    @error('email')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Role</label>
                    <select name="role" class="select" required>
                        @foreach(['customer', 'staff', 'admin'] as $role)
                        <option value="{{ $role }}" {{ old('role', 'customer') === $role ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Company (optional)</label>
                    <input type="text" name="company" value="{{ old('company') }}" class="input">
                </div>
                <div>
                    <label class="label">Password</label>
                    <input type="password" name="password" class="input" required>
                    @error('password')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary">Create User</button>
                    <a href="/admin/users" class="btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
