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

            setItem(id, slides, title) {
                if (this.selected?.id === id) { this.close(); return; }
                this.selected = { id, slides, title, current: 0 };
                this.startTimer();
                this.$nextTick(() => this.$refs.preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
            },

            close() {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
                this.progressPct = 0;
                this.selected = null;
            },

            startTimer() {
                clearInterval(this.progressInterval);
                this.progressPct = 0;
                this.progressInterval = setInterval(() => {
                    this.progressPct += 100 / 80;
                    if (this.progressPct >= 100) {
                        this.progressPct = 0;
                        if (this.selected) {
                            this.selected.current = (this.selected.current + 1) % this.selected.slides.length;
                        }
                    }
                }, 100);
            },

            goTo(i) {
                this.selected.current = i;
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

                    {{-- Thumbnail --}}
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
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mt-8 rounded-2xl overflow-hidden border border-primary/30 bg-surface">
            <template x-if="selected">
                <div>
                    {{-- Tab bar --}}
                    <div class="flex items-center gap-1 overflow-x-auto px-4 py-3 border-b border-border bg-surface-2">
                        <template x-for="(slide, i) in selected.slides" :key="i">
                            <button @click="goTo(i)"
                                    :class="selected.current === i
                                        ? 'bg-primary text-bg font-semibold'
                                        : 'text-muted hover:text-text'"
                                    class="shrink-0 px-3 py-1.5 rounded-full text-xs transition-colors whitespace-nowrap">
                                <span x-text="`${String(i+1).padStart(2,'0')} ${slide.title}`"></span>
                            </button>
                        </template>
                        <template x-if="selected.slides.length === 0">
                            <span class="text-xs text-muted px-2">No slides for this item.</span>
                        </template>
                    </div>

                    <template x-if="selected.slides.length > 0">
                        <div>
                            {{-- Main area --}}
                            <div class="flex flex-col lg:flex-row min-h-120">

                                {{-- Left: screenshot --}}
                                <div class="lg:w-3/4 relative flex items-center justify-center p-6 overflow-hidden bg-surface-2">

                                    {{-- Prev arrow --}}
                                    <button @click="prev()"
                                            class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                                            style="background: rgba(0,0,0,0.5);"
                                            x-show="selected.slides.length > 1">
                                        <x-icon name="chevron-left" class="w-5 h-5 text-white" />
                                    </button>

                                    <template x-if="selected.slides[selected.current]?.image">
                                        <img :src="selected.slides[selected.current].image"
                                             :alt="selected.slides[selected.current].title"
                                             class="max-w-full max-h-110 object-contain rounded-lg shadow-2xl transition-opacity duration-300">
                                    </template>
                                    <template x-if="!selected.slides[selected.current]?.image">
                                        <div class="flex flex-col items-center justify-center text-center py-12">
                                            <x-icon name="computer" class="w-16 h-16 text-border mb-3" />
                                            <p class="text-sm text-muted">No screenshot uploaded</p>
                                        </div>
                                    </template>

                                    {{-- Next arrow --}}
                                    <button @click="next()"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                                            style="background: rgba(0,0,0,0.5);"
                                            x-show="selected.slides.length > 1">
                                        <x-icon name="chevron-right" class="w-5 h-5 text-white" />
                                    </button>
                                </div>

                                {{-- Right: content --}}
                                <div class="flex-1 flex flex-col justify-center px-8 py-10">
                                    {{-- Section label --}}
                                    <div class="flex items-center gap-3 mb-5">
                                        <div class="w-8 h-px bg-primary"></div>
                                        <span class="text-xs font-mono tracking-widest uppercase text-primary"
                                              x-text="`${String(selected.current+1).padStart(2,'0')} — ${selected.slides[selected.current]?.title ?? ''}`">
                                        </span>
                                    </div>

                                    {{-- Headline --}}
                                    <template x-if="selected.slides[selected.current]?.headline">
                                        <h2 class="text-2xl lg:text-3xl font-display font-bold leading-tight mb-4 text-text"
                                            x-text="selected.slides[selected.current].headline">
                                        </h2>
                                    </template>

                                    {{-- Description --}}
                                    <template x-if="selected.slides[selected.current]?.description">
                                        <p class="leading-relaxed mb-6 text-sm lg:text-base text-muted"
                                           x-text="selected.slides[selected.current].description">
                                        </p>
                                    </template>

                                    {{-- Bullets --}}
                                    <template x-if="selected.slides[selected.current]?.bullets?.length">
                                        <ul class="space-y-3">
                                            <template x-for="(bullet, bi) in selected.slides[selected.current].bullets" :key="bi">
                                                <li class="flex items-start gap-3 text-sm text-text">
                                                    <svg class="w-4 h-4 shrink-0 mt-0.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                    </svg>
                                                    <span x-text="bullet"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </template>
                                </div>
                            </div>

                            {{-- Footer bar --}}
                            <div class="flex items-center gap-4 px-4 py-3 border-t border-border bg-surface-2">

                                {{-- Dot indicators --}}
                                <div class="flex items-center gap-1.5">
                                    <template x-for="(slide, i) in selected.slides" :key="i">
                                        <button @click="goTo(i)"
                                                :class="selected.current === i ? 'w-6 bg-primary' : 'w-2 bg-border hover:bg-muted'"
                                                class="h-2 rounded-full transition-all duration-300">
                                        </button>
                                    </template>
                                </div>

                                {{-- Progress bar --}}
                                <div class="flex-1 h-0.5 rounded-full overflow-hidden bg-border">
                                    <div :style="`width: ${progressPct}%`"
                                         class="h-full rounded-full bg-primary"
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
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <x-icon name="computer" class="w-10 h-10 text-border mb-3" />
                            <p class="font-semibold text-text mb-1">No preview available</p>
                            <p class="text-sm text-muted">Sign in to access the full interactive demo.</p>
                        </div>
                    </template>
                </div>
            </template>
        </div>

    </div>
    @endif

    {{-- Login CTA for guests --}}
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
