@extends('layouts.public')
@section('title', 'RapidInsight Designs')

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<section class="relative min-h-[92vh] flex items-center overflow-hidden">
    {{-- Animated background grid --}}
    <div class="absolute inset-0 opacity-[0.04]"
         style="background-image: linear-gradient(var(--color-primary) 1px, transparent 1px), linear-gradient(90deg, var(--color-primary) 1px, transparent 1px); background-size: 60px 60px;">
    </div>
    {{-- Radial glow --}}
    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(109,190,46,0.08) 0%, transparent 70%);">
    </div>
    {{-- Floating orbs --}}
    <div class="absolute top-24 right-1/4 w-64 h-64 rounded-full opacity-10 animate-float"
         style="background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%); animation-duration: 4s;"></div>
    <div class="absolute bottom-32 left-1/4 w-40 h-40 rounded-full opacity-6 animate-float"
         style="background: radial-gradient(circle, var(--color-primary) 0%, transparent 70%); animation-duration: 6s; animation-delay: -2s;"></div>

    <div class="wide relative z-10 py-24 grid lg:grid-cols-2 gap-16 items-center">
        <div>
            {{-- Eyebrow --}}
            <div class="inline-flex items-center gap-2 badge badge-green mb-6 text-xs py-1.5 px-3 animate-fade-in-up">
                <span class="w-1.5 h-1.5 rounded-full bg-[var(--color-primary)] animate-pulse"></span>
                Web Design &amp; Software Development
            </div>

            {{-- Headline --}}
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-display font-bold leading-tight text-[var(--color-text)] animate-fade-in-up delay-100 opacity-0-init">
                Smart Design,<br>
                <span class="gradient-text">Optimized Workflows</span><br>
                <span class="text-[var(--color-muted)]">Built for Efficiency.</span>
            </h1>

            <p class="mt-6 text-lg text-[var(--color-muted)] max-w-xl leading-relaxed animate-fade-in-up delay-200 opacity-0-init">
                We craft high-performance web applications and custom software solutions that scale with your business — fast, fluid, and beautifully engineered.
            </p>

            <div class="mt-10 flex flex-wrap gap-4 animate-fade-in-up delay-300 opacity-0-init">
                <a href="/showcase" class="btn-primary">
                    Explore Our Work
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
                <a href="/contact" class="btn-ghost">Get In Touch</a>
            </div>
        </div>

        {{-- Logo visual ─────────────────────────────────────────────────── --}}
        <div class="hidden lg:flex items-center justify-center animate-fade-in-up delay-400 opacity-0-init">
            <div class="relative">
                {{-- Outer glow ring --}}
                <div class="absolute inset-0 rounded-full animate-glow-pulse"
                     style="background: radial-gradient(circle, rgba(109,190,46,0.18) 0%, transparent 70%); transform: scale(1.6);"></div>
                {{-- Mid ring --}}
                <div class="absolute inset-0 rounded-full border border-primary/20 animate-float"
                     style="transform: scale(1.35); animation-duration: 5s;"></div>
                {{-- Inner ring --}}
                <div class="absolute inset-0 rounded-full border border-primary/10 animate-float"
                     style="transform: scale(1.15); animation-duration: 7s; animation-delay: -1s;"></div>

                {{-- Logo card --}}
                <div class="relative w-56 h-56 rounded-2xl flex items-center justify-center animate-float border border-primary/30"
                     style="background: linear-gradient(135deg, var(--color-surface) 0%, var(--color-surface-2) 100%);
                            box-shadow: 0 0 40px rgba(109,190,46,0.15), 0 0 80px rgba(109,190,46,0.06), inset 0 1px 0 rgba(109,190,46,0.1);">
                    <img src="/images/logo.png" alt="RapidInsight Designs"
                         class="w-40 h-auto drop-shadow-[0_0_12px_rgba(109,190,46,0.4)]">
                </div>

                {{-- Floating tag chips --}}
                <div class="absolute -top-3 -right-4 badge badge-green text-xs animate-float"
                     style="animation-duration: 3.5s; animation-delay: -0.5s;">
                    Laravel
                </div>
                <div class="absolute -bottom-3 -left-4 badge badge-green text-xs animate-float"
                     style="animation-duration: 4.5s; animation-delay: -1.5s;">
                    Alpine.js
                </div>
                <div class="absolute top-1/2 -right-8 badge badge-muted text-xs animate-float"
                     style="animation-duration: 5s; animation-delay: -2s;">
                    Tailwind
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Services strip ───────────────────────────────────────────────────── --}}
<section class="section-divider"></section>
<div class="wide py-8 grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach([
        ['bolt',     'Rapid Development',  'From idea to deployed app in record time.'],
        ['code',     'Clean Architecture', 'Maintainable, scalable code from day one.'],
        ['computer', 'Cross-Platform',     'Web, mobile, and desktop — we build it all.'],
        ['star',     'Client-Focused',     'Your success is our metric.'],
    ] as [$icon, $title, $desc])
    <div x-data="scrollReveal({{ $loop->index * 100 }})"
         :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
         class="transition-all duration-500 flex flex-col items-center text-center p-4 gap-2">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[var(--color-primary-glow)] border border-[var(--color-primary)]/30">
            <x-icon name="{{ $icon }}" class="w-5 h-5 text-[var(--color-primary)]" />
        </div>
        <p class="text-sm font-semibold text-[var(--color-text)]">{{ $title }}</p>
        <p class="text-xs text-[var(--color-muted)]">{{ $desc }}</p>
    </div>
    @endforeach
</div>
<div class="section-divider"></div>

{{-- ── About preview ────────────────────────────────────────────────────── --}}
<section class="wide py-24 grid md:grid-cols-2 gap-16 items-center">
    <div x-data="scrollReveal()"
         :class="visible ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-8'"
         class="transition-all duration-700">
        <p class="label text-[var(--color-primary)] mb-2">Who We Are</p>
        <h2 class="text-3xl md:text-4xl font-display font-bold text-[var(--color-text)] mb-4">
            Engineering solutions that <span class="gradient-text">actually work.</span>
        </h2>
        <p class="text-[var(--color-muted)] leading-relaxed mb-6">
            RapidInsight Designs specializes in building custom web applications and software tools tailored to real business needs.
            We focus on performance, clean design, and seamless user experience.
        </p>
        <a href="/how-we-work" class="btn-ghost">
            See How We Work
            <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>

    {{-- Animated code/stats card --}}
    <div x-data="scrollReveal(150)"
         :class="visible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-8'"
         class="transition-all duration-700">
        <div class="card-glow p-0 overflow-hidden">
            <div class="bg-[var(--color-surface-2)] px-4 py-3 flex items-center gap-2 border-b border-[var(--color-border)]">
                <div class="flex gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-500/60"></span>
                    <span class="w-3 h-3 rounded-full bg-yellow-500/60"></span>
                    <span class="w-3 h-3 rounded-full bg-green-500/60"></span>
                </div>
                <span class="text-xs text-[var(--color-muted)] ml-2">rapid-insight.app</span>
            </div>
            <div class="p-6 font-mono text-sm space-y-2">
                <p><span class="text-[var(--color-primary)]">const</span> <span class="text-blue-400">mission</span> = {</p>
                <p class="pl-4"><span class="text-[var(--color-muted)]">design:</span> <span class="text-amber-400">"smart &amp; modern"</span>,</p>
                <p class="pl-4"><span class="text-[var(--color-muted)]">workflow:</span> <span class="text-amber-400">"optimized"</span>,</p>
                <p class="pl-4"><span class="text-[var(--color-muted)]">goal:</span> <span class="text-amber-400">"efficiency"</span>,</p>
                <p class="pl-4"><span class="text-[var(--color-muted)]">result:</span> <span class="text-[var(--color-primary)]">true</span></p>
                <p>};</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Showcase preview ─────────────────────────────────────────────────── --}}
<section class="bg-[var(--color-surface)] border-y border-[var(--color-border)]">
    <div class="wide py-24 text-center">
        <p class="label text-[var(--color-primary)] mb-2">Our Work</p>
        <h2 class="text-3xl md:text-4xl font-display font-bold text-[var(--color-text)] mb-4">
            See it in action
        </h2>
        <p class="text-[var(--color-muted)] max-w-lg mx-auto mb-10">
            Browse our live showcase of products and tools. Log in to access full interactive demos.
        </p>
        <a href="/showcase" class="btn-primary animate-glow-pulse">
            <x-icon name="eye" class="w-4 h-4" />
            View Showcase
        </a>
    </div>
</section>

{{-- ── CTA ──────────────────────────────────────────────────────────────── --}}
<section class="wide py-24 text-center">
    <h2 class="text-3xl md:text-4xl font-display font-bold text-[var(--color-text)] mb-4">
        Ready to build something <span class="gradient-text">great?</span>
    </h2>
    <p class="text-[var(--color-muted)] max-w-md mx-auto mb-8">
        Tell us about your project and let's get started.
    </p>
    <a href="/contact" class="btn-primary">
        Start a Conversation
        <x-icon name="arrow-right" class="w-4 h-4" />
    </a>
</section>

@endsection
