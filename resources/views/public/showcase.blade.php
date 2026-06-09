@extends('layouts.public')
@section('title', 'Showcase')

@section('content')
<section class="wide py-24">

    @if($items->isEmpty())
    <div class="max-w-2xl mx-auto text-center mb-16">
        <p class="label text-primary mb-2">Live Demos</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold text-text mb-4">
            The <span class="gradient-text">Showcase</span>
        </h1>
        <p class="text-muted leading-relaxed">
            A preview of the products and tools we've built. Sign in for full interactive access to your demos.
        </p>
    </div>
    <div class="text-center py-16">
        <x-icon name="grid" class="w-12 h-12 text-border mx-auto mb-4" />
        <p class="text-muted">Showcase items coming soon.</p>
    </div>
    @else
    <div x-data="{
            selected: null,
            progressPct: 0,
            progressInterval: null,
            slideReady: true,
            lightbox: null,
            paused: false,

            setItem(id, slides, title) {
                if (this.selected?.id === id) { this.close(); return; }
                this.slideReady = false;
                this.paused = false;
                this.selected = { id, slides, title, current: 0 };
                this.startTimer();
                this.$nextTick(() => {
                    requestAnimationFrame(() => { this.slideReady = true; });
                });
            },

            close() {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
                this.progressPct = 0;
                this.selected = null;
                this.lightbox = null;
                this.paused = false;
            },

            startTimer() {
                clearInterval(this.progressInterval);
                this.progressPct = 0;
                this.paused = false;
                this.progressInterval = setInterval(() => {
                    if (this.lightbox || this.paused) return;
                    this.progressPct += 100 / 300;
                    if (this.progressPct >= 100) {
                        this.progressPct = 0;
                        if (this.selected) {
                            this.changeSlide((this.selected.current + 1) % this.selected.slides.length);
                        }
                    }
                }, 100);
            },

            changeSlide(i) {
                this.lightbox = null;
                this.slideReady = false;
                this.selected.current = i;
                this.$nextTick(() => {
                    requestAnimationFrame(() => { this.slideReady = true; });
                });
            },

            goTo(i) {
                if (i === this.selected.current) return;
                this.changeSlide(i);
                this.startTimer();
            },
            prev() { this.goTo((this.selected.current - 1 + this.selected.slides.length) % this.selected.slides.length); },
            next() { this.goTo((this.selected.current + 1) % this.selected.slides.length); }
        }">

        {{-- Section header — shrinks when a preview is open --}}
        <div class="max-w-2xl mx-auto text-center transition-all duration-500 ease-in-out"
             :class="selected ? 'mb-5' : 'mb-16'">
            <p class="label text-primary transition-all duration-500"
               :class="selected ? 'mb-0 opacity-50 text-xs' : 'mb-2'">Live Demos</p>
            <h1 class="font-display font-bold text-text transition-all duration-500 leading-tight"
                :class="selected ? 'text-2xl md:text-3xl mb-1' : 'text-4xl md:text-5xl mb-4'">
                The <span class="gradient-text">Showcase</span>
            </h1>
            <div class="overflow-hidden transition-all duration-500"
                 :style="selected ? 'max-height: 0; opacity: 0;' : 'max-height: 6rem; opacity: 1;'">
                <p class="text-muted leading-relaxed">
                    A preview of the products and tools we've built. Sign in for full interactive access to your demos.
                </p>
            </div>
        </div>

        {{-- CSS Grid stacking container: cards and preview occupy the same grid cell --}}
        {{-- overflow-x:clip hides flying cards without affecting position:fixed children --}}
        <div style="display: grid; overflow-x: clip;">

            {{-- ── Cards grid (grid-area 1/1 — defines row height) ──────────────── --}}
            <div style="grid-area: 1/1;" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($items as $i => $item)
                @php
                    $slidesJson = $item->slides->map(fn($s) => [
                        'title'       => $s->title,
                        'headline'    => $s->headline,
                        'description' => $s->description,
                        'bullets'     => $s->bullets ?? [],
                        'image'       => $s->image_path ? Storage::url($s->image_path) : null,
                    ])->values();
                    $flyDir   = $i % 2 === 0 ? '-120vw' : '120vw';
                    $outDelay = $i * 55;
                    $inDelay  = 200 + $i * 55;
                @endphp
                <div :style="selected
                         ? 'transform: translateX({{ $flyDir }}); opacity: 0; pointer-events: none; transition: transform 0.6s cubic-bezier(0.4,0,0.2,1) {{ $outDelay }}ms, opacity 0.4s ease {{ $outDelay }}ms;'
                         : 'transform: none; opacity: 1; transition: transform 0.6s cubic-bezier(0.34,1.2,0.64,1) {{ $inDelay }}ms, opacity 0.4s ease {{ $inDelay }}ms;'">
                    <div x-data="scrollReveal({{ $i * 80 }})"
                         :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                         class="transition-all duration-500 relative rounded-2xl overflow-hidden group h-72 bg-surface-2 cursor-pointer"
                         @click="setItem({{ $item->id }}, {{ json_encode($slidesJson) }}, {{ json_encode($item->title) }})">

                        {{-- Full-bleed image --}}
                        @if($item->thumbnail_path)
                        <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}"
                             class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <x-icon name="computer" class="w-16 h-16 text-border" />
                        </div>
                        @endif

                        {{-- Gradient overlay --}}
                        <div class="absolute inset-0 pointer-events-none"
                             style="background: linear-gradient(to top, rgba(0,0,0,0.92) 0%, rgba(0,0,0,0.55) 45%, transparent 100%);"></div>

                        {{-- Bottom content --}}
                        <div class="absolute inset-x-0 bottom-0 p-5 z-10">
                            <h3 class="font-display font-semibold text-white leading-snug mb-1">{{ $item->title }}</h3>
                            @if($item->description)
                            <p class="text-sm text-white/65 mb-3 line-clamp-2 leading-snug">{{ $item->description }}</p>
                            @endif
                            <button @click.stop="setItem({{ $item->id }}, {{ json_encode($slidesJson) }}, {{ json_encode($item->title) }})"
                                    class="btn-primary btn-sm">
                                + Learn More
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Slideshow panel (grid-area 1/1 — overlays card area) ──────────── --}}
            <div x-show="selected"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-[0.97] translate-y-6"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-350"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-[0.97] translate-y-6"
                 style="grid-area: 1/1; z-index: 10; align-self: start;"
                 class="rounded-2xl overflow-hidden border border-primary/20 bg-surface">
                <template x-if="selected">
                    <div class="relative">
                        {{-- Tab bar --}}
                        <div class="flex items-center gap-1.5 overflow-x-auto px-5 py-3 border-b border-border bg-surface-2">
                            <template x-for="(slide, i) in selected.slides" :key="i">
                                <button @click="goTo(i)"
                                        :class="selected.current === i
                                            ? 'bg-primary text-bg font-semibold shadow-sm'
                                            : 'text-muted hover:text-text hover:bg-surface'"
                                        class="shrink-0 px-3.5 py-1.5 rounded-full text-xs transition-all whitespace-nowrap">
                                    <span x-text="`${String(i+1).padStart(2,'0')} ${slide.title}`"></span>
                                </button>
                            </template>
                        </div>

                        <template x-if="selected.slides.length > 0">
                            <div>
                                {{-- Full-width image --}}
                                <div class="relative overflow-hidden bg-surface-2" style="height: 520px;">

                                    <button @click="prev()"
                                            x-show="selected.slides.length > 1"
                                            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110"
                                            style="background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);">
                                        <x-icon name="chevron-left" class="w-5 h-5 text-white" />
                                    </button>

                                    <img x-show="slideReady && selected.slides[selected.current]?.image"
                                         :src="selected.slides[selected.current]?.image ?? ''"
                                         :alt="selected.slides[selected.current]?.title ?? ''"
                                         @click="lightbox = selected.slides[selected.current].image"
                                         x-transition:enter="transition ease-out duration-500"
                                         x-transition:enter-start="opacity-0 scale-[1.04]"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute inset-0 w-full h-full object-contain p-8 cursor-zoom-in">

                                    <div x-show="!selected.slides[selected.current]?.image"
                                         class="absolute inset-0 flex flex-col items-center justify-center">
                                        <x-icon name="computer" class="w-16 h-16 text-border mb-3" />
                                        <p class="text-sm text-muted">No screenshot uploaded</p>
                                    </div>

                                    <div class="absolute inset-x-0 bottom-0 h-32 pointer-events-none"
                                         style="background: linear-gradient(to bottom, transparent, var(--color-surface));"></div>

                                    <button @click="next()"
                                            x-show="selected.slides.length > 1"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110"
                                            style="background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);">
                                        <x-icon name="chevron-right" class="w-5 h-5 text-white" />
                                    </button>
                                </div>

                                {{-- Text content --}}
                                <div class="px-8 md:px-16 pb-10 pt-2 max-w-4xl mx-auto">

                                    <div x-show="slideReady"
                                         x-transition:enter="transition ease-out duration-500"
                                         x-transition:enter-start="opacity-0 translate-y-2"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         style="transition-delay: 0ms"
                                         class="flex items-center gap-3 mb-5">
                                        <div class="w-8 h-px bg-primary shrink-0"></div>
                                        <span class="text-xs font-mono tracking-widest uppercase text-primary"
                                              x-text="`${String(selected.current+1).padStart(2,'0')} — ${selected.slides[selected.current]?.title ?? ''}`">
                                        </span>
                                    </div>

                                    <template x-if="selected.slides[selected.current]?.headline">
                                        <h2 x-show="slideReady"
                                            x-transition:enter="transition ease-out duration-500"
                                            x-transition:enter-start="opacity-0 translate-y-4"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            style="transition-delay: 80ms"
                                            class="text-3xl md:text-4xl font-display font-bold leading-tight mb-5 text-text"
                                            x-text="selected.slides[selected.current].headline">
                                        </h2>
                                    </template>

                                    <template x-if="selected.slides[selected.current]?.description">
                                        <p x-show="slideReady"
                                           x-transition:enter="transition ease-out duration-500"
                                           x-transition:enter-start="opacity-0 translate-y-3"
                                           x-transition:enter-end="opacity-100 translate-y-0"
                                           style="transition-delay: 160ms"
                                           class="text-base md:text-lg leading-relaxed text-muted mb-8"
                                           x-text="selected.slides[selected.current].description">
                                        </p>
                                    </template>

                                    <template x-if="selected.slides[selected.current]?.bullets?.length">
                                        <div class="grid sm:grid-cols-2 gap-3">
                                            <template x-for="(bullet, bi) in selected.slides[selected.current].bullets" :key="bi">
                                                <div x-show="slideReady"
                                                     x-transition:enter="transition ease-out duration-500"
                                                     x-transition:enter-start="opacity-0 translate-y-3"
                                                     x-transition:enter-end="opacity-100 translate-y-0"
                                                     :style="`transition-delay: ${230 + bi * 65}ms`"
                                                     class="flex items-start gap-3 px-4 py-3 rounded-xl bg-surface-2 border border-border">
                                                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                    <span class="text-sm text-text leading-snug" x-text="bullet"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- Footer --}}
                                <div class="flex items-center gap-3 px-6 py-3 border-t border-border bg-surface-2">
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <template x-for="(slide, i) in selected.slides" :key="i">
                                            <button @click="goTo(i)"
                                                    :class="selected.current === i ? 'w-6 bg-primary' : 'w-2 bg-border hover:bg-muted'"
                                                    class="h-2 rounded-full transition-all duration-300">
                                            </button>
                                        </template>
                                    </div>

                                    <button @click="paused = !paused"
                                            :title="paused ? 'Resume slideshow' : 'Pause slideshow'"
                                            class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-colors border"
                                            :class="paused ? 'border-primary text-primary' : 'border-border text-muted hover:text-text hover:border-muted'">
                                        <svg x-show="!paused" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 4h4v16H6zm8 0h4v16h-4z"/>
                                        </svg>
                                        <svg x-show="paused" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </button>

                                    <div class="flex-1 h-0.5 rounded-full overflow-hidden bg-border">
                                        <div :style="`width: ${progressPct}%`"
                                             class="h-full rounded-full bg-primary"
                                             :class="paused ? 'opacity-40' : ''"
                                             style="transition: width 0.1s linear;"></div>
                                    </div>

                                    <div class="flex items-center gap-4 shrink-0">
                                        <span class="text-xs font-mono text-muted"
                                              x-text="`${String(selected.current+1).padStart(2,'0')} / ${String(selected.slides.length).padStart(2,'0')}`">
                                        </span>
                                        <button @click="close()" class="btn-ghost btn-sm gap-1.5">
                                            <x-icon name="x" class="w-3.5 h-3.5" />
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="selected.slides.length === 0">
                            <div class="flex flex-col items-center justify-center py-24 text-center">
                                <x-icon name="computer" class="w-10 h-10 text-border mb-3" />
                                <p class="font-semibold text-text mb-1">No preview available</p>
                                <p class="text-sm text-muted">Sign in to access the full interactive demo.</p>
                            </div>
                        </template>

                        {{-- Lightbox --}}
                        <div x-show="lightbox"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             @click.self="lightbox = null"
                             @keydown.escape.window="lightbox = null"
                             class="fixed inset-0 z-50 flex items-center justify-center p-6"
                             style="background: rgba(0,0,0,0.88); backdrop-filter: blur(10px);">
                            <div x-show="lightbox"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="relative">
                                <img :src="lightbox ?? ''"
                                     @click="lightbox = null"
                                     class="max-w-[90vw] max-h-[88vh] object-contain rounded-xl shadow-2xl block cursor-zoom-out">
                                <button @click="lightbox = null"
                                        class="absolute -top-3 -right-3 w-8 h-8 rounded-full flex items-center justify-center border border-border transition-colors hover:bg-surface"
                                        style="background: var(--color-surface-2);">
                                    <x-icon name="x" class="w-4 h-4 text-muted" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- X close button — separate grid sibling, never inside overflow:hidden, always top-right --}}
            <button x-show="selected && !lightbox"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-75"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-75"
                    @click="close()"
                    title="Close preview"
                    style="grid-area: 1/1; justify-self: end; align-self: start; z-index: 30; margin: 0.5rem; background: var(--color-surface-2);"
                    class="w-8 h-8 rounded-full flex items-center justify-center border border-border transition-all hover:scale-110 hover:border-primary/50">
                <x-icon name="x" class="w-4 h-4 text-muted" />
            </button>

        </div>{{-- end CSS grid stacking container --}}

    </div>
    @endif

    @guest
    <div class="mt-16 text-center card border-dashed border-primary/30 max-w-lg mx-auto">
        <x-icon name="lock" class="w-10 h-10 text-primary mx-auto mb-3" />
        <h3 class="font-display font-semibold text-text mb-2">Full Access Available</h3>
        <p class="text-sm text-muted mb-4">
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
