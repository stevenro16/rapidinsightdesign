@extends('layouts.public')
@section('title', 'Reset Password')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="RapidInsight Designs" class="h-12 w-auto mx-auto mb-4">
            <h1 class="text-2xl font-display font-bold text-[var(--color-text)]">Create new password</h1>
            <p class="text-sm text-[var(--color-muted)] mt-1">Choose a strong password for your account.</p>
        </div>

        <div class="card">
            @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-sm text-red-400">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/reset-password">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div class="space-y-4">
                    <div>
                        <label class="label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}"
                               class="input" required>
                    </div>
                    <div>
                        <label class="label">New Password</label>
                        <input type="password" name="password" class="input" placeholder="Min. 8 characters" required>
                    </div>
                    <div>
                        <label class="label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="input" placeholder="Repeat password" required>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
