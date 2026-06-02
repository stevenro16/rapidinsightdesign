@extends('layouts.public')
@section('title', 'Sign In')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-16 px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="RapidInsight Designs" class="h-12 w-auto mx-auto mb-4">
            <h1 class="text-2xl font-display font-bold text-[var(--color-text)]">Welcome back</h1>
            <p class="text-sm text-[var(--color-muted)] mt-1">Sign in to your account</p>
        </div>

        <div class="card">
            @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-sm text-red-400">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/login" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="input" placeholder="you@example.com" required autofocus>
                    </div>
                    <div>
                        <label class="label">Password</label>
                        <input type="password" name="password" class="input" placeholder="••••••••" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded">
                            <span class="text-sm text-[var(--color-muted)]">Remember me</span>
                        </label>
                        <a href="/forgot-password" class="text-sm text-[var(--color-primary)] hover:underline">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center" :disabled="loading">
                        <span x-show="!loading">Sign In</span>
                        <span x-show="loading">Signing in…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
