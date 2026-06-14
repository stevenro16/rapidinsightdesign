<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RapidInsight Designs') — Smart Design, Optimized Workflows</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="loginModal()" @keydown.escape.window="hide(); hideRegister()">

    {{-- ── Navigation ──────────────────────────────────────────────────── --}}
    <header class="fixed top-0 inset-x-0 z-40 border-b border-[var(--color-border)] bg-[var(--color-bg)]/90 backdrop-blur-md">
        <div class="wide flex items-center justify-between h-16">
            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2 group">
                <img src="/images/logo.png" alt="RapidInsight Designs" class="h-14 w-auto transition-all duration-300 group-hover:drop-shadow-[0_0_8px_var(--color-primary)]" style="max-height: 56px; overflow: visible;">
                <div class="hidden sm:flex flex-col leading-tight">
                    <span class="font-display font-bold text-base text-text tracking-tight">RapidInsight</span>
                    <span class="font-display text-xs text-primary tracking-widest uppercase">Designs</span>
                </div>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-6">
                <a href="/"            class="nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
                <a href="/how-we-work" class="nav-link {{ request()->is('how-we-work') ? 'active' : '' }}">How We Work</a>
                <a href="/products"    class="nav-link {{ request()->is('products') ? 'active' : '' }}">Products</a>
                <a href="/showcase"    class="nav-link {{ request()->is('showcase') ? 'active' : '' }}">Showcase</a>
                <a href="/contact"     class="nav-link {{ request()->is('contact') ? 'active' : '' }}">Contact</a>
            </nav>

            {{-- Auth area --}}
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isCustomer() ? '/showroom' : (auth()->user()->isStaff() ? '/staff/dashboard' : '/admin/dashboard') }}"
                       class="btn-ghost btn-sm">
                        <x-icon name="user" class="w-4 h-4" />
                        {{ auth()->user()->name }}
                    </a>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="btn-ghost btn-sm">
                            <x-icon name="logout" class="w-4 h-4" />
                        </button>
                    </form>
                @else
                    <button @click="show()" class="btn-ghost btn-sm">
                        <x-icon name="lock" class="w-4 h-4" />
                        Sign In
                    </button>
                    <button @click="showRegister()" class="btn-primary btn-sm animate-glow-pulse">
                        <x-icon name="user" class="w-4 h-4" />
                        Create Account
                    </button>
                @endauth

                {{-- Mobile menu toggle --}}
                <button class="md:hidden text-[var(--color-muted)] hover:text-[var(--color-text)] transition-colors"
                        @click="$dispatch('toggle-mobile-nav')">
                    <x-icon name="menu" class="w-6 h-6" />
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <div x-data="{ open: false }" @toggle-mobile-nav.window="open = !open">
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="md:hidden border-t border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-3 space-y-1">
                <a href="/"            class="block py-2 text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Home</a>
                <a href="/how-we-work" class="block py-2 text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">How We Work</a>
                <a href="/products"    class="block py-2 text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Products</a>
                <a href="/showcase"    class="block py-2 text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Showcase</a>
                <a href="/contact"     class="block py-2 text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Contact</a>
            </div>
        </div>
    </header>

    {{-- ── Flash messages ───────────────────────────────────────────────── --}}
    @if(session('success') || session('error') || session('status'))
    <div class="fixed top-20 right-4 z-50 max-w-sm" x-data="flash()">
        <div x-show="show" x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4"
             class="card border-l-4 {{ session('success') || session('status') ? 'border-[var(--color-primary)]' : 'border-[var(--color-danger)]' }} flex items-start gap-3 shadow-xl">
            <x-icon name="{{ session('error') ? 'warning' : 'check' }}" class="w-5 h-5 shrink-0 mt-0.5 {{ session('error') ? 'text-[var(--color-danger)]' : 'text-[var(--color-primary)]' }}" />
            <p class="text-sm text-[var(--color-text)]">{{ session('success') ?? session('error') ?? session('status') }}</p>
            <button @click="show = false" class="ml-auto text-[var(--color-muted)] hover:text-[var(--color-text)] transition-colors">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>
    </div>
    @endif

    {{-- ── Main content ─────────────────────────────────────────────────── --}}
    <main class="pt-16">
        @yield('content')
    </main>

    {{-- ── Footer ───────────────────────────────────────────────────────── --}}
    <footer class="border-t border-[var(--color-border)] bg-[var(--color-surface)] mt-24">
        <div class="wide py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex flex-col">
                <img src="/images/logo.png" alt="RapidInsight Designs" class="w-48 h-auto mb-auto">
                <p class="text-sm text-[var(--color-muted)] leading-relaxed mt-6">
                    Smart Design, Optimized Workflows built for Efficiency.
                </p>
            </div>
            <div>
                <p class="label mb-3">Navigation</p>
                <div class="space-y-1.5">
                    <a href="/how-we-work" class="block text-sm text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">How We Work</a>
                    <a href="/products"    class="block text-sm text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Products</a>
                    <a href="/showcase"    class="block text-sm text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Showcase</a>
                    <a href="/contact"     class="block text-sm text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Contact</a>
                </div>
            </div>
            <div>
                <p class="label mb-3">Account</p>
                @auth
                    <p class="text-sm text-[var(--color-muted)]">Signed in as <span class="text-[var(--color-primary)]">{{ auth()->user()->name }}</span></p>
                @else
                    <button @click="show()" class="text-sm text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors">Sign In →</button>
                @endauth
            </div>
        </div>
        <div class="section-divider"></div>
        <div class="wide py-4 flex items-center justify-between text-xs text-[var(--color-muted)]">
            <span>&copy; {{ date('Y') }} RapidInsight Designs. All rights reserved.</span>
            <a href="/contact" class="hover:text-[var(--color-primary)] transition-colors">Get in touch</a>
        </div>
    </footer>

    {{-- ── Login modal ──────────────────────────────────────────────────── --}}
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="hide()"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-md"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="card border-[var(--color-border)] shadow-2xl shadow-black/50">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-display font-semibold text-[var(--color-text)]">Welcome back</h2>
                        <p class="text-sm text-[var(--color-muted)] mt-0.5">Sign in to your account</p>
                    </div>
                    <button @click="hide()" class="text-[var(--color-muted)] hover:text-[var(--color-text)] transition-colors p-1">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                @if($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-sm text-red-400">
                    {{ $errors->first() }}
                </div>
                @endif

                <form x-ref="loginForm" method="POST" action="/login" @submit.prevent="submit()">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="label">Email</label>
                            <input x-ref="emailInput" x-model="email" type="email" name="email"
                                   class="input" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                        <div>
                            <label class="label">Password</label>
                            <input x-model="password" type="password" name="password"
                                   class="input" placeholder="••••••••" autocomplete="current-password" required>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="remember" name="remember"
                                       class="rounded border-[var(--color-border)] bg-[var(--color-surface-2)] text-[var(--color-primary)] focus:ring-[var(--color-primary-glow)]">
                                <span class="text-sm text-[var(--color-muted)]">Remember me</span>
                            </label>
                            <a href="/forgot-password" class="text-sm text-[var(--color-primary)] hover:underline">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center" :disabled="loading">
                            <span x-show="!loading">Sign In</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Signing in…
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Register modal ───────────────────────────────────────────────── --}}
    @php
        // Lightweight, self-contained captcha (no external service / keys needed).
        $captchaA = random_int(2, 9);
        $captchaB = random_int(2, 9);
        session(['register_captcha' => $captchaA + $captchaB]);
    @endphp
    <div x-show="registerOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-init="$nextTick(() => { @if($errors->getBag('register')->isNotEmpty()) registerOpen = true @endif })"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         style="display: none;">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="hideRegister()"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-md"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="card border-[var(--color-border)] shadow-2xl shadow-black/50">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-display font-semibold text-[var(--color-text)]">Create your account</h2>
                        <p class="text-sm text-[var(--color-muted)] mt-0.5">Join RapidInsight Designs</p>
                    </div>
                    <button @click="hideRegister()" class="text-[var(--color-muted)] hover:text-[var(--color-text)] transition-colors p-1">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                @if($errors->getBag('register')->isNotEmpty())
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-sm text-red-400">
                    {{ $errors->getBag('register')->first() }}
                </div>
                @endif

                <form x-ref="registerForm" method="POST" action="/register" @submit.prevent="registerSubmit()">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="label">Name</label>
                            <input x-ref="registerEmail" type="text" name="name" value="{{ old('name') }}"
                                   class="input" placeholder="Jane Doe" autocomplete="name" required>
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="input" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                        <div>
                            <label class="label">Password</label>
                            <input type="password" name="password"
                                   class="input" placeholder="At least 8 characters" autocomplete="new-password" required minlength="8">
                        </div>
                        <div>
                            <label class="label">Confirm Password</label>
                            <input type="password" name="password_confirmation"
                                   class="input" placeholder="Re-enter password" autocomplete="new-password" required minlength="8">
                        </div>
                        <div>
                            <label class="label">Verification — what is {{ $captchaA }} + {{ $captchaB }}?</label>
                            <input type="number" name="captcha" class="input" placeholder="Answer" required autocomplete="off">
                        </div>
                        <button type="submit" class="btn-primary w-full justify-center" :disabled="registerLoading">
                            <span x-show="!registerLoading">Create Account</span>
                            <span x-show="registerLoading" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Creating…
                            </span>
                        </button>
                        <p class="text-center text-sm text-[var(--color-muted)]">
                            Already have an account?
                            <button type="button" @click="show()" class="text-[var(--color-primary)] hover:underline">Sign in</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
