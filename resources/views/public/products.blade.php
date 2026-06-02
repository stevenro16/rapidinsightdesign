@extends('layouts.public')
@section('title', 'Products')

@section('content')
<section class="wide py-24">
    <div class="max-w-2xl mx-auto text-center mb-16">
        <p class="label text-[var(--color-primary)] mb-2">What We Build</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold text-[var(--color-text)] mb-4">
            Products &amp; <span class="gradient-text">Solutions</span>
        </h1>
        <p class="text-[var(--color-muted)] leading-relaxed">
            We create custom software products tailored to your business — not off-the-shelf tools that almost fit.
        </p>
    </div>

    {{-- Product types --}}
    <div class="grid md:grid-cols-3 gap-6 mb-20">
        @foreach([
            ['computer', 'Web Applications',
             'Full-stack web apps built with modern frameworks. Fast, secure, and designed to scale.',
             ['Laravel / PHP', 'React / Alpine.js', 'Tailwind CSS', 'MySQL / PostgreSQL']],
            ['bolt',     'Workflow Automation',
             'Eliminate repetitive tasks with smart automation. Save hours every week.',
             ['API integrations', 'Custom dashboards', 'Reporting tools', 'Scheduled jobs']],
            ['grid',     'Client Portals',
             'Give your customers a branded space to manage accounts, access files, and communicate.',
             ['Role-based access', 'Document management', 'Messaging', 'Activity tracking']],
        ] as $i => [$icon, $title, $desc, $tags])
        <div x-data="scrollReveal({{ $i * 150 }})"
             :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
             class="transition-all duration-600 card card-hover group">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-[var(--color-primary-glow)] border border-[var(--color-primary)]/30 mb-4 group-hover:shadow-lg group-hover:shadow-[var(--color-primary-glow)] transition-shadow">
                <x-icon name="{{ $icon }}" class="w-6 h-6 text-[var(--color-primary)]" />
            </div>
            <h3 class="font-display font-semibold text-lg text-[var(--color-text)] mb-2">{{ $title }}</h3>
            <p class="text-sm text-[var(--color-muted)] mb-4 leading-relaxed">{{ $desc }}</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach($tags as $tag)
                <span class="badge badge-muted text-xs">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- What purchasing means --}}
    <div class="bg-[var(--color-surface)] border border-[var(--color-border)] rounded-2xl p-8 md:p-12">
        <div class="max-w-2xl mx-auto">
            <p class="label text-[var(--color-primary)] mb-3">What Purchasing Means</p>
            <h2 class="text-3xl font-display font-bold text-[var(--color-text)] mb-6">
                You own it. Fully.
            </h2>
            <div class="space-y-4">
                @foreach([
                    ['check', 'Full source code ownership', 'Every line of code we write for you belongs to you. No licensing fees, no lock-in.'],
                    ['check', 'Transparent, fixed pricing',  'You get a clear quote before work begins. No hidden costs or scope creep surprises.'],
                    ['check', 'Post-launch support',         'We include a support window after launch to address issues and make adjustments.'],
                    ['check', 'Knowledge transfer',          'We document everything and walk your team through the system — no black boxes.'],
                ] as [$icon, $title, $desc])
                <div class="flex gap-4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-[var(--color-primary-glow)] shrink-0 mt-0.5">
                        <x-icon name="{{ $icon }}" class="w-4 h-4 text-[var(--color-primary)]" />
                    </div>
                    <div>
                        <p class="font-semibold text-[var(--color-text)] text-sm">{{ $title }}</p>
                        <p class="text-sm text-[var(--color-muted)]">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-8">
                <a href="/contact" class="btn-primary">
                    Start a Project
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
