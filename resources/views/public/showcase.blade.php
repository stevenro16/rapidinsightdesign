@extends('layouts.public')
@section('title', 'Showcase')

@section('content')
<section class="wide py-24">
    <div class="max-w-2xl mx-auto text-center mb-16">
        <p class="label text-primary mb-2">Live Demos</p>
        <h1 class="text-4xl md:text-5xl font-display font-bold text-text mb-4">
            The <span class="gradient-text">Showcase</span>
        </h1>
        <p class="text-muted leading-relaxed">
            A preview of the products and tools we've built. Sign in for full interactive access to your demos.
        </p>
    </div>

    @if($items->isEmpty())
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
                this.selected = { id, slides, title, current: 0 };
                this.startTimer();
                this.$nextTick(() => {
                    requestAnimationFrame(() => { this.slideReady = true; });
                    this.$refs.preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            },

            close() {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
                this.progressPct = 0;
                this.selected = null;
                this.lightbox = null;
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

        {{-- Cards grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $i => $item)
            @php
                $slidesJson = $item->slides->map(fn($s) => [
                    'title'       => $s->title,
                    'headline'    => $s->headline,
                    'description' => $s->description,
                    'bullets'     => $s->bullets ?? [],
                    'image'       => $s->image_path ? Storage::url($s->image_path) : null,
                ])->values();
            @endphp
            <div @click="setItem({{ $item->id }}, {{ json_encode($slidesJson) }}, {{ json_encode($item->title) }})"
                 :class="selected?.id === {{ $item->id }} ? 'ring-2 ring-primary rounded-2xl' : ''"
                 class="cursor-pointer transition-all duration-200">
                <div x-data="scrollReveal({{ $i * 80 }})"
                     :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                     class="transition-all duration-500 card card-hover group relative overflow-hidden flex flex-col h-full">

                    <div class="h-40 rounded-lg mb-4 overflow-hidden bg-surface-2 flex items-center justify-center relative shrink-0">
                        @if($item->thumbnail_path)
                        <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                        @else
                        <x-icon name="computer" class="w-12 h-12 text-border" />
                        @endif
                        @guest
                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            <x-icon name="lock" class="w-8 h-8 text-primary" />
                        </div>
                        @endguest
                    </div>

                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h3 class="font-display font-semibold text-text">{{ $item->title }}</h3>
                            @if($item->slides->isNotEmpty())
                            <span class="badge badge-muted shrink-0 text-xs">{{ $item->slides->count() }} slides</span>
                            @endif
                        </div>
                        @if($item->description)
                        <p class="text-sm text-muted mb-3 line-clamp-2">{{ $item->description }}</p>
                        @endif
                        @if($item->tech_tags)
                        <div class="flex flex-wrap gap-1 mb-3">
                            @foreach($item->techTagsArray() as $tag)
                            <span class="badge badge-muted">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    @auth
                    <a href="/showroom/{{ $item->id }}" @click.stop
                       class="btn-ghost btn-sm w-full justify-center mt-2">
                        Launch Demo
                        <x-icon name="external" class="w-4 h-4" />
                    </a>
                    @else
                    <button @click.stop="$dispatch('open-login')"
                            class="btn-ghost btn-sm w-full justify-center mt-2">
                        <x-icon name="lock" class="w-4 h-4" />
                        Sign In to Access
                    </button>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Slideshow panel ─────────────────────────────────────────────── --}}
        <div x-ref="preview"
             x-show="selected"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mt-8 rounded-2xl overflow-hidden border border-primary/20 bg-surface">
            <template x-if="selected">
                <div>
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
                            {{-- Full-width image area --}}
                            <div class="relative overflow-hidden bg-surface-2" style="height: 520px;">

                                {{-- Prev arrow --}}
                                <button @click="prev()"
                                        x-show="selected.slides.length > 1"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110"
                                        style="background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);">
                                    <x-icon name="chevron-left" class="w-5 h-5 text-white" />
                                </button>

                                {{-- Image --}}
                                <img x-show="slideReady && selected.slides[selected.current]?.image"
                                     :src="selected.slides[selected.current]?.image ?? ''"
                                     :alt="selected.slides[selected.current]?.title ?? ''"
                                     @click="lightbox = selected.slides[selected.current].image"
                                     x-transition:enter="transition ease-out duration-500"
                                     x-transition:enter-start="opacity-0 scale-[1.04]"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute inset-0 w-full h-full object-contain p-8 cursor-zoom-in">

                                {{-- No image fallback --}}
                                <div x-show="!selected.slides[selected.current]?.image"
                                     class="absolute inset-0 flex flex-col items-center justify-center">
                                    <x-icon name="computer" class="w-16 h-16 text-border mb-3" />
                                    <p class="text-sm text-muted">No screenshot uploaded</p>
                                </div>

                                {{-- Bottom fade to surface --}}
                                <div class="absolute inset-x-0 bottom-0 h-32 pointer-events-none"
                                     style="background: linear-gradient(to bottom, transparent, var(--color-surface));"></div>

                                {{-- Next arrow --}}
                                <button @click="next()"
                                        x-show="selected.slides.length > 1"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110"
                                        style="background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);">
                                    <x-icon name="chevron-right" class="w-5 h-5 text-white" />
                                </button>
                            </div>

                            {{-- Text content --}}
                            <div class="px-8 md:px-16 pb-10 pt-2 max-w-4xl mx-auto">

                                {{-- Section label --}}
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

                                {{-- Headline --}}
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

                                {{-- Description --}}
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

                                {{-- Bullets — 2-col card grid --}}
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
                                {{-- Dot indicators --}}
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <template x-for="(slide, i) in selected.slides" :key="i">
                                        <button @click="goTo(i)"
                                                :class="selected.current === i ? 'w-6 bg-primary' : 'w-2 bg-border hover:bg-muted'"
                                                class="h-2 rounded-full transition-all duration-300">
                                        </button>
                                    </template>
                                </div>

                                {{-- Pause / Play button --}}
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

                                {{-- Progress bar --}}
                                <div class="flex-1 h-0.5 rounded-full overflow-hidden bg-border">
                                    <div :style="`width: ${progressPct}%`"
                                         class="h-full rounded-full bg-primary"
                                         :class="paused ? 'opacity-40' : ''"
                                         style="transition: width 0.1s linear;"></div>
                                </div>

                                {{-- Counter + close --}}
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

                    {{-- No slides fallback --}}
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
