@extends('layouts.public')
@section('title', 'Forgot Password')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="RapidInsight Designs" class="h-12 w-auto mx-auto mb-4">
            <h1 class="text-2xl font-display font-bold text-[var(--color-text)]">Reset your password</h1>
            <p class="text-sm text-[var(--color-muted)] mt-1">Enter your email and we'll send a reset link.</p>
        </div>

        <div class="card">
            @if(session('status'))
            <div class="mb-4 p-3 rounded-lg bg-green-500/10 border border-green-500/30 text-sm text-green-400">
                {{ session('status') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-sm text-red-400">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/forgot-password">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="input" placeholder="you@example.com" required autofocus>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">
                        Send Reset Link
                    </button>
                    <a href="/login" class="block text-center text-sm text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">
                        Back to sign in
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
