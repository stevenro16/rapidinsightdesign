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
    <div x-data="{
            selected: null,
            setItem(id, url, title) {
                if (this.selected?.id === id) { this.selected = null; return; }
                this.selected = { id, url, title };
                this.$nextTick(() => this.$refs.preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' }));
            },
            close() { this.selected = null; }
        }">

        {{-- Cards grid --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $i => $item)
            <div @click="setItem({{ $item->id }}, {{ json_encode($item->public_url) }}, {{ json_encode($item->title) }})"
                 :class="selected?.id === {{ $item->id }} ? 'ring-2 ring-primary rounded-2xl' : ''"
                 class="cursor-pointer transition-all duration-200">
                <div x-data="scrollReveal({{ $i * 80 }})"
                     :class="visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
                     class="transition-all duration-500 card card-hover group relative overflow-hidden flex flex-col h-full">

                    {{-- Thumbnail / placeholder --}}
                    <div class="h-40 rounded-lg mb-4 overflow-hidden bg-surface-2 flex items-center justify-center relative shrink-0">
                        @if($item->thumbnail_path)
                        <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                        @else
                        <x-icon name="computer" class="w-12 h-12 text-[var(--color-border)]" />
                        @endif
                        @guest
                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity">
                            <x-icon name="lock" class="w-8 h-8 text-[var(--color-primary)]" />
                        </div>
                        @endguest
                    </div>

                    <div class="flex-1">
                        <h3 class="font-display font-semibold text-[var(--color-text)] mb-1">{{ $item->title }}</h3>
                        @if($item->description)
                        <p class="text-sm text-[var(--color-muted)] mb-3 line-clamp-2">{{ $item->description }}</p>
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

        {{-- Expanded preview panel --}}
        <div x-ref="preview"
             x-show="selected"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-3"
             class="mt-8 card p-0 overflow-hidden border border-[var(--color-primary)]/30">
            <template x-if="selected">
                <div>
                    {{-- Title bar --}}
                    <div class="flex items-center gap-3 px-4 py-3 bg-surface-2 border-b border-border">
                        <span class="w-2 h-2 rounded-full bg-[var(--color-primary)] animate-pulse shrink-0"></span>
                        <span x-text="selected.title"
                              class="font-semibold text-sm text-[var(--color-text)] shrink-0"></span>
                        <template x-if="selected.url">
                            <span x-text="selected.url"
                                  class="text-xs text-[var(--color-muted)] font-mono truncate flex-1"></span>
                        </template>
                        <button @click="close()"
                                class="btn-ghost btn-sm ml-auto shrink-0 gap-1.5">
                            <x-icon name="x" class="w-3.5 h-3.5" />
                            Close
                        </button>
                    </div>

                    {{-- iframe or no-preview message --}}
                    <template x-if="selected.url">
                        <iframe :src="selected.url"
                                class="w-full border-0 block"
                                style="height: 540px;"
                                allow="fullscreen; clipboard-write"
                                sandbox="allow-scripts allow-forms allow-same-origin allow-popups">
                        </iframe>
                    </template>
                    <template x-if="!selected.url">
                        <div class="flex flex-col items-center justify-center py-20 text-center">
                            <x-icon name="lock" class="w-10 h-10 text-[var(--color-border)] mb-3" />
                            <p class="font-semibold text-[var(--color-text)] mb-1">No public preview available</p>
                            <p class="text-sm text-[var(--color-muted)]">Sign in to access the full interactive demo.</p>
                        </div>
                    </template>
                </div>
            </template>
        </div>

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
