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

            setItem(id, slides, title, preview) {
                if (this.selected?.id === id) { this.close(); return; }

                // Window mode: open the preview in a new tab and leave the page as-is.
                if (preview && preview.mode === 'window' && preview.url) {
                    window.open(preview.url, '_blank', 'noopener');
                    return;
                }

                this.slideReady = false;
                this.paused = false;
                this.selected = { id, slides, title, current: 0, preview: preview || null };
                // Only the image slideshow auto-advances; an embedded preview frame does not.
                if (!this.selected.preview) {
                    this.startTimer();
                }
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
            next() { this.goTo((this.selected.current + 1) % this.selected.slides.length); },

            // ── Lightbox image cycling (only slides that actually have an image) ──
            lightboxIndexes() {
                if (!this.selected) return [];
                return this.selected.slides
                    .map((s, i) => (s.image ? i : null))
                    .filter(i => i !== null);
            },
            openLightbox() {
                const img = this.selected.slides[this.selected.current]?.image;
                if (img) this.lightbox = img;
            },
            lightboxStep(dir) {
                const idxs = this.lightboxIndexes();
                if (idxs.length < 2) return;
                const pos = idxs.indexOf(this.selected.current);
                const next = idxs[(pos + dir + idxs.length) % idxs.length];
                this.selected.current = next;
                this.lightbox = this.selected.slides[next].image;
            }
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
        {{-- Clip lives on the cards grid (below) so the wide preview panel can break out --}}
        <div style="display: grid;">

            {{-- ── Cards grid (grid-area 1/1 — defines row height) ───────────────── --}}
            {{-- 90vw on a clean element (Alpine :style below would clobber a static width) --}}
            <div style="grid-area: 1/1; width: 90vw; margin-left: calc(50% - 45vw);">
                {{-- fly-out wrapper: fades the grid out when a preview opens --}}
                <div :style="selected
                         ? 'opacity: 0; transform: translateY(24px) scale(0.985); pointer-events: none; transition: opacity 0.4s ease, transform 0.5s cubic-bezier(0.4,0,0.2,1);'
                         : 'opacity: 1; transform: none; transition: opacity 0.45s ease 0.1s, transform 0.55s cubic-bezier(0.34,1.2,0.64,1) 0.1s;'">

                <div class="grid grid-cols-3 gap-6">
                    @foreach($items as $i => $item)
                    @php
                        $slidesJson = $item->slides->map(fn($s) => [
                            'title'       => $s->title,
                            'headline'    => $s->headline,
                            'description' => $s->description,
                            'bullets'     => $s->bullets ?? [],
                            'image'       => $s->image_path ? Storage::url($s->image_path) : null,
                        ])->values();
                        $previewJson = $item->hasPreview()
                            ? ['url' => $item->previewUrl(), 'mode' => $item->previewMode()]
                            : null;
                        $isWindow = $item->hasPreview() && $item->previewMode() === 'window';
                    @endphp
                    <button type="button"
                            @click="setItem({{ $item->id }}, {{ json_encode($slidesJson) }}, {{ json_encode($item->title) }}, {{ json_encode($previewJson) }})"
                            class="group relative w-full h-[500px] overflow-hidden rounded-2xl border border-border bg-surface-2 text-left cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-xl hover:shadow-black/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/60">

                        {{-- Preview image as the card background --}}
                        @if($item->thumbnail_path)
                        <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}"
                             class="absolute inset-0 w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105">
                        @else
                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-surface-2 to-surface">
                            <x-icon name="computer" class="w-16 h-16 text-border" />
                        </div>
                        @endif

                        {{-- Subtle darkening for depth & legibility --}}
                        <div class="absolute inset-0 pointer-events-none bg-gradient-to-t from-black/50 via-transparent to-black/10"></div>

                        {{-- Live-preview marker (opens externally) --}}
                        @if($isWindow)
                        <span class="absolute top-3 right-3 z-10 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium text-white"
                              style="background: rgba(13,17,23,0.55); backdrop-filter: blur(6px);">↗ Live</span>
                        @endif

                        {{-- Bottom info banner — slightly transparent, frosted glass --}}
                        <div class="absolute inset-x-0 bottom-0 z-10 px-4 py-3.5 border-t border-white/10"
                             style="background: rgba(13,17,23,0.70); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
                            <h3 class="font-display font-semibold text-white leading-snug line-clamp-1">{{ $item->title }}</h3>
                            @if($item->description)
                            <p class="text-xs text-white/65 mt-1 leading-snug line-clamp-2">{{ $item->description }}</p>
                            @endif
                            @if($item->tech_tags)
                            <div class="flex flex-wrap gap-1 mt-2.5">
                                @foreach(array_slice($item->techTagsArray(), 0, 3) as $tag)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium text-primary" style="background: rgba(109,190,46,0.15);">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>
                </div>
            </div>

            {{-- ── Slideshow panel (grid-area 1/1 — overlays card area) ──────────── --}}
            <div x-show="selected && !selected.preview"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 scale-[0.97] translate-y-6"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-350"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-[0.97] translate-y-6"
                 style="grid-area: 1/1; z-index: 10; align-self: start; width: 90vw; margin-left: calc(50% - 45vw);"
                 class="rounded-2xl overflow-hidden border border-primary/20 bg-surface">
                <template x-if="selected && !selected.preview">
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
                                {{-- Image + text — stacked by default, side-by-side on wide (2K+) screens --}}
                                <div class="flex flex-col xl:flex-row xl:items-center">
                                {{-- Preview image (fixed height so the absolutely-positioned img always renders) --}}
                                <div class="relative overflow-hidden bg-surface-2 h-[520px] xl:w-[58%] xl:shrink-0">

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
                                <div class="px-8 md:px-16 xl:px-12 pb-10 pt-6 xl:pt-8 max-w-4xl mx-auto xl:max-w-none xl:flex-1 xl:flex xl:flex-col xl:justify-center">

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
                                </div>{{-- end image+text row --}}

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
                             @keydown.arrow-left.window="if (lightbox) lightboxStep(-1)"
                             @keydown.arrow-right.window="if (lightbox) lightboxStep(1)"
                             class="fixed inset-0 z-50 flex items-center justify-center p-6"
                             style="background: rgba(0,0,0,0.88); backdrop-filter: blur(10px);">

                            {{-- Prev arrow --}}
                            <button x-show="lightbox && lightboxIndexes().length > 1"
                                    @click.stop="lightboxStep(-1)"
                                    class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-10 w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110"
                                    style="background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);">
                                <x-icon name="chevron-left" class="w-6 h-6 text-white" />
                            </button>

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

                                {{-- Counter --}}
                                <div x-show="lightboxIndexes().length > 1"
                                     class="absolute bottom-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full text-xs font-mono text-white"
                                     style="background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);"
                                     x-text="`${lightboxIndexes().indexOf(selected.current) + 1} / ${lightboxIndexes().length}`">
                                </div>
                            </div>

                            {{-- Next arrow --}}
                            <button x-show="lightbox && lightboxIndexes().length > 1"
                                    @click.stop="lightboxStep(1)"
                                    class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-10 w-12 h-12 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110"
                                    style="background: rgba(0,0,0,0.55); backdrop-filter: blur(6px);">
                                <x-icon name="chevron-right" class="w-6 h-6 text-white" />
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- X close button — separate grid sibling, never inside overflow:hidden, always top-right --}}
            <button x-show="selected && !selected.preview && !lightbox"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-75"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-75"
                    @click="close()"
                    title="Close preview"
                    style="grid-area: 1/1; justify-self: end; align-self: start; z-index: 30; margin-top: 0.5rem; margin-right: calc(50% - 45vw + 0.5rem); background: var(--color-surface-2);"
                    class="w-8 h-8 rounded-full flex items-center justify-center border border-border transition-all hover:scale-110 hover:border-primary/50">
                <x-icon name="x" class="w-4 h-4 text-muted" />
            </button>

        </div>{{-- end CSS grid stacking container --}}

        {{-- ── Frame preview overlay — fixed, centered, 80% of screen width ──────── --}}
        <div x-show="selected && selected.preview"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="close()"
             @keydown.escape.window="if (selected?.preview && !lightbox) close()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-8"
             style="background: rgba(0,0,0,0.85); backdrop-filter: blur(8px);">
            <template x-if="selected && selected.preview">
                <div x-data="{ loaded: false }"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative rounded-2xl overflow-hidden border border-primary/20 bg-surface shadow-2xl flex flex-col"
                     style="width: 90vw; height: 88vh;">
                    {{-- Header --}}
                    <div class="flex items-center gap-3 px-5 py-3 border-b border-border bg-surface-2 shrink-0">
                        <span class="font-display font-semibold text-text truncate" x-text="selected.title"></span>
                        <div class="flex-1"></div>
                        <a :href="selected.preview.url" target="_blank" rel="noopener"
                           class="btn-ghost btn-sm gap-1.5 shrink-0">
                            <x-icon name="external" class="w-3.5 h-3.5" />
                            <span class="hidden sm:inline">Open in new tab</span>
                        </a>
                        <button @click="close()" class="btn-ghost btn-sm gap-1.5 shrink-0">
                            <x-icon name="x" class="w-3.5 h-3.5" />
                            Close
                        </button>
                    </div>
                    {{-- Iframe --}}
                    <div class="relative flex-1 bg-surface-2">
                        <div x-show="!loaded" class="absolute inset-0 skeleton"></div>
                        <iframe :src="selected.preview.url" @load="loaded = true"
                                title="Live preview"
                                loading="lazy"
                                sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
                                class="relative w-full h-full border-0"></iframe>
                    </div>
                </div>
            </template>
        </div>

    </div>
    @endif
</section>

@push('scripts')
<script>
    document.addEventListener('open-login', () => {
        document.querySelector('[x-data="loginModal()"]')?.__x?.$data?.show?.();
    });
</script>
@endpush
@endsection
