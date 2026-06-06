@extends('layouts.public')
@section('title', 'Showcase')

@section('content')
<section class="wide py-24">
    <div class="max-w-2xl mx-auto text-center mb-16">
        <p class="label text-[var(--color-primary)] mb-2">Live Demos</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold text-[var(--color-text)] mb-4">
            The <span class="gradient-text">Showcase</span>
        </h1>
        <p class="text-[var(--color-muted)] leading-relaxed">
            A preview of the products and tools we've built. Sign in for full interactive access to your demos.
        </p>
    </div>

    @if($items->isEmpty())
    <div class="text-center py-16">
        <x-icon name="grid" class="w-12 h-12 text-[var(--color-border)] mx-auto mb-4" />
        <p class="text-[var(--color-muted)]">Showcase items coming soon.</p>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($items as $i => $item)
        <div x-data="scrollReveal({{ $i * 80 }})"
             :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
             class="transition-all duration-500 card card-hover group relative overflow-hidden">

            {{-- Thumbnail / placeholder --}}
            <div class="h-40 rounded-lg mb-4 overflow-hidden bg-[var(--color-surface-2)] flex items-center justify-center relative">
                @if($item->thumbnail_path)
                <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                @else
                <x-icon name="computer" class="w-12 h-12 text-[var(--color-border)]" />
                @endif
                {{-- Locked overlay --}}
                @guest
                <div class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                    <x-icon name="lock" class="w-8 h-8 text-[var(--color-primary)]" />
                </div>
                @endguest
            </div>

            <h3 class="font-display font-semibold text-[var(--color-text)] mb-1">{{ $item->title }}</h3>
            @if($item->description)
            <p class="text-sm text-[var(--color-muted)] mb-3 line-clamp-2">{{ $item->description }}</p>
            @endif

            @if($item->tech_tags)
            <div class="flex flex-wrap gap-1 mb-4">
                @foreach($item->techTagsArray() as $tag)
                <span class="badge badge-muted">{{ trim($tag) }}</span>
                @endforeach
            </div>
            @endif

            @auth
            <a href="/showroom/{{ $item->id }}" class="btn-ghost btn-sm w-full justify-center">
                Launch Demo
                <x-icon name="external" class="w-4 h-4" />
            </a>
            @else
            <button x-data onclick="$dispatch('open-login')" class="btn-ghost btn-sm w-full justify-center"
                    @click="$dispatch('open-login')">
                <x-icon name="lock" class="w-4 h-4" />
                Sign In to Access
            </button>
            @endauth
        </div>
        @endforeach
    </div>
    @endif

    {{-- Login CTA for guests --}}
    @guest
    <div class="mt-16 text-center card border-dashed border-[var(--color-primary)]/30 max-w-lg mx-auto">
        <x-icon name="lock" class="w-10 h-10 text-[var(--color-primary)] mx-auto mb-3" />
        <h3 class="font-display font-semibold text-[var(--color-text)] mb-2">Full Access Available</h3>
        <p class="text-sm text-[var(--color-muted)] mb-4">
            Customers can log in to launch and interact with live demo applications.
        </p>
        <button x-data @click="$dispatch('open-login')" class="btn-primary">
            Sign In for Full Access
        </button>
    </div>
    @endguest
</section>

@push('scripts')
<script>
    document.addEventListener('open-login', () => {
        document.querySelector('[x-data="loginModal()"]')?.__x?.$data?.show?.();
    });
</script>
@endpush
@endsection
