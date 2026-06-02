@extends('layouts.public')
@section('title', 'How We Work')

@section('content')
<section class="wide py-24">
    <div class="max-w-2xl mx-auto text-center mb-16">
        <p class="label text-[var(--color-primary)] mb-2">Our Process</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold text-[var(--color-text)] mb-4">
            How We <span class="gradient-text">Work</span>
        </h1>
        <p class="text-[var(--color-muted)] leading-relaxed">
            We follow a transparent, collaborative process designed to deliver exceptional results — on time and on budget.
        </p>
    </div>

    {{-- Steps --}}
    <div class="relative max-w-3xl mx-auto">
        {{-- Vertical line --}}
        <div class="absolute left-8 top-0 bottom-0 w-px bg-gradient-to-b from-[var(--color-primary)] via-[var(--color-border)] to-transparent hidden md:block"></div>

        @foreach([
            ['bolt',     'Discovery & Planning',
             'We start by deeply understanding your goals, users, and constraints. You get a clear scope, timeline, and cost estimate before a single line of code is written.'],
            ['code',     'Architecture & Design',
             'We design both the system architecture and the visual interface in parallel. You see interactive mockups before development begins — no surprises.'],
            ['computer', 'Rapid Development',
             'We build in focused sprints with continuous delivery. You get working software to review early and often — not just at the end.'],
            ['eye',      'Review & Iterate',
             'Every release goes through rigorous testing and your feedback loop. We move fast without breaking things.'],
            ['bolt',     'Deployment & Support',
             'We handle production deployment, monitoring, and ongoing support. Your success doesn\'t end at launch — it starts there.'],
        ] as $i => [$icon, $title, $desc])
        <div x-data="scrollReveal({{ $i * 100 }})"
             :class="visible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-6'"
             class="transition-all duration-600 relative flex gap-6 mb-10">
            {{-- Step number bubble --}}
            <div class="shrink-0 w-16 h-16 rounded-full flex items-center justify-center border-2 border-[var(--color-primary)] bg-[var(--color-bg)] z-10 shadow-lg shadow-[var(--color-primary-glow)]">
                <x-icon name="{{ $icon }}" class="w-6 h-6 text-[var(--color-primary)]" />
            </div>
            {{-- Content --}}
            <div class="card card-hover flex-1 mt-2">
                <div class="flex items-center gap-3 mb-2">
                    <span class="badge badge-green">Step {{ $i + 1 }}</span>
                    <h3 class="font-display font-semibold text-[var(--color-text)]">{{ $title }}</h3>
                </div>
                <p class="text-sm text-[var(--color-muted)] leading-relaxed">{{ $desc }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- CTA --}}
    <div class="text-center mt-16">
        <p class="text-[var(--color-muted)] mb-6">Ready to start your project?</p>
        <a href="/contact" class="btn-primary">
            Let's Talk
            <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>
</section>
@endsection
