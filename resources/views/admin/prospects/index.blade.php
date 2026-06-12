@extends('layouts.portal')
@section('title', 'Prospects')
@section('page-title', 'Prospects')

@section('content')
<div class="-m-6 relative overflow-hidden" style="height: calc(100vh - 4rem);"
     x-data="prospectsMap(@js(['center' => $center, 'statuses' => $statuses]))">

    {{-- Map --}}
    <div x-ref="map" class="absolute inset-0"></div>

    {{-- ── Left panel: filters + list ─────────────────────────────────── --}}
    <div class="absolute top-4 left-4 bottom-4 w-80 z-[1000] flex flex-col rounded-2xl border border-border shadow-2xl shadow-black/40 overflow-hidden"
         style="background: color-mix(in srgb, var(--color-surface) 95%, transparent); backdrop-filter: blur(8px);"
         @click.stop>

        {{-- Header --}}
        <div class="px-4 pt-4 pb-3 border-b border-border shrink-0">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="font-display font-semibold text-text leading-tight">Prospects</h2>
                    <p class="text-xs text-muted" x-text="`${visible().length} of ${prospects.length} businesses`"></p>
                </div>
                <a :href="exportUrl()" class="btn-ghost btn-sm" title="Export filtered list to CSV">
                    <x-icon name="download" class="w-4 h-4" />
                </a>
            </div>

            <button @click="searchMode ? cancelSearch() : enterSearchMode()"
                    :class="searchMode ? 'btn-ghost' : 'btn-primary'"
                    class="btn-sm w-full justify-center">
                <template x-if="!searchMode">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="search" class="w-4 h-4" />
                        Search New Area
                    </span>
                </template>
                <template x-if="searchMode">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="x" class="w-4 h-4" />
                        Cancel Search
                    </span>
                </template>
            </button>
        </div>

        {{-- Filters --}}
        <div class="px-4 py-3 border-b border-border space-y-2.5 shrink-0">
            <div class="flex flex-wrap gap-1.5">
                @foreach($statuses as $status)
                <button @click="toggleStatusFilter('{{ $status }}')"
                        :class="filters.statuses.includes('{{ $status }}')
                            ? 'border-primary/40 bg-primary/10 text-text'
                            : 'border-border text-muted opacity-50'"
                        class="flex items-center gap-1.5 px-2 py-1 rounded-full border text-xs transition-all hover:opacity-100">
                    <span class="status-dot status-dot-{{ $status }}"></span>
                    <span x-text="statusLabels['{{ $status }}']"></span>
                </button>
                @endforeach
            </div>

            <div class="flex gap-2">
                <select x-model="filters.category" class="select text-xs flex-1 py-1.5">
                    <option value="">All categories</option>
                    <template x-for="cat in categories" :key="cat">
                        <option :value="cat" x-text="cat"></option>
                    </template>
                </select>
                <select x-model="filters.band" class="select text-xs flex-1 py-1.5">
                    <option value="">Any presence</option>
                    <option value="low">Low (hot 🔥)</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <input type="text" x-model.debounce.300ms="filters.q" placeholder="Search by name…"
                   class="input text-xs py-1.5">
        </div>

        {{-- List --}}
        <div class="flex-1 overflow-y-auto">
            <template x-if="prospects.length === 0">
                <div class="p-6 text-center">
                    <p class="text-sm text-muted leading-relaxed">
                        No prospects yet.<br>
                        Hit <span class="text-primary font-medium">Search New Area</span> to find businesses around Redlands.
                    </p>
                </div>
            </template>

            <template x-for="p in listItems()" :key="p.id">
                <button @click="select(p.id)"
                        :class="selected?.id === p.id ? 'bg-primary/10 border-l-primary' : 'border-l-transparent hover:bg-surface-2'"
                        class="w-full text-left px-4 py-2.5 border-l-2 border-b border-b-border/50 transition-colors">
                    <div class="flex items-center gap-2">
                        <span class="status-dot shrink-0" :class="`status-dot-${p.status}`"></span>
                        <span class="text-sm font-medium text-text truncate flex-1" x-text="p.name"></span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded shrink-0"
                              :class="{
                                  'bg-primary/15 text-primary': band(p) === 'low',
                                  'bg-amber-500/15 text-amber-400': band(p) === 'medium',
                                  'bg-surface-2 text-muted': band(p) === 'high',
                              }"
                              x-text="bandLabel(p)"></span>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 pl-4">
                        <span class="text-xs text-muted truncate" x-text="p.category ?? 'Uncategorized'"></span>
                        <span x-show="p.notes_count > 0" class="text-[10px] text-muted shrink-0 flex items-center gap-0.5">
                            <x-icon name="chat" class="w-3 h-3" />
                            <span x-text="p.notes_count"></span>
                        </span>
                    </div>
                </button>
            </template>

            <template x-if="visible().length > listLimit">
                <button @click="listLimit += 200" class="w-full py-2.5 text-xs text-primary hover:bg-surface-2 transition-colors">
                    Show more (<span x-text="visible().length - listLimit"></span> hidden)
                </button>
            </template>
        </div>
    </div>

    {{-- ── Search mode banner ─────────────────────────────────────────── --}}
    <div x-show="searchMode" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute top-4 left-1/2 -translate-x-1/2 z-[1000] w-[420px] max-w-[calc(100%-2rem)] rounded-2xl border border-primary/30 shadow-2xl shadow-black/40 p-4"
         style="background: color-mix(in srgb, var(--color-surface) 95%, transparent); backdrop-filter: blur(8px);"
         @click.stop>
        <template x-if="!pendingCenter">
            <p class="text-sm text-text text-center flex items-center justify-center gap-2">
                <x-icon name="map-pin" class="w-4 h-4 text-primary" />
                Click anywhere on the map to place a search center
            </p>
        </template>
        <template x-if="pendingCenter">
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="label mb-0">Search radius</span>
                    <span class="font-mono text-primary" x-text="`${radiusMiles} mi`"></span>
                </div>
                <input type="range" min="1" max="25" step="1" x-model.number="radiusMiles" class="w-full accent-[var(--color-primary)]">
                <p x-show="radiusMiles > 10" class="text-xs text-amber-400 flex items-center gap-1.5">
                    <x-icon name="warning" class="w-3.5 h-3.5 shrink-0" />
                    Large areas can take 30–60 seconds and return a lot of results.
                </p>
                <div class="flex gap-2">
                    <button @click="runSearch()" :disabled="searching" class="btn-primary btn-sm flex-1 justify-center">
                        <x-icon name="search" class="w-4 h-4" />
                        Search This Area
                    </button>
                    <button @click="cancelSearch()" class="btn-ghost btn-sm">Cancel</button>
                </div>
            </div>
        </template>
    </div>

    {{-- ── Detail drawer ──────────────────────────────────────────────── --}}
    <div x-show="selected" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-8"
         class="absolute top-4 right-4 bottom-4 w-96 max-w-[calc(100%-2rem)] z-[1000] flex flex-col rounded-2xl border border-border shadow-2xl shadow-black/40 overflow-hidden"
         style="background: color-mix(in srgb, var(--color-surface) 97%, transparent); backdrop-filter: blur(8px);"
         @click.stop>
        <template x-if="selected">
            <div class="flex flex-col h-full">
                {{-- Header --}}
                <div class="px-5 pt-4 pb-3 border-b border-border shrink-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-display font-semibold text-text leading-snug" x-text="selected.name"></h3>
                            <p class="text-xs text-muted mt-0.5" x-text="selected.category ?? 'Uncategorized'"></p>
                        </div>
                        <button @click="closeDetail()" class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center border border-border bg-surface-2 transition-all hover:border-primary/50 hover:scale-110">
                            <x-icon name="x" class="w-3.5 h-3.5 text-muted" />
                        </button>
                    </div>

                    {{-- Presence score meter --}}
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-muted">Online presence</span>
                            <span :class="{
                                      'text-primary': band(selected) === 'low',
                                      'text-amber-400': band(selected) === 'medium',
                                      'text-muted': band(selected) === 'high',
                                  }"
                                  x-text="`${bandLabel(selected)} (${selected.presence_score}/100)`"></span>
                        </div>
                        <div class="score-meter">
                            <div :style="`width: ${Math.max(selected.presence_score, 3)}%`"
                                 :class="{
                                     'bg-[var(--color-primary)]': band(selected) === 'low',
                                     'bg-amber-400': band(selected) === 'medium',
                                     'bg-[var(--color-muted)]': band(selected) === 'high',
                                 }"
                                 class="h-full rounded-full transition-all duration-500"></div>
                        </div>
                        <p x-show="band(selected) === 'low'" class="text-[11px] text-primary mt-1">
                            Little to no online presence — strong prospect.
                        </p>
                    </div>
                </div>

                {{-- Scrollable body --}}
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    {{-- Contact info --}}
                    <div class="space-y-1.5 text-sm">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="globe" class="w-4 h-4 shrink-0" ::class="selected.website ? 'text-muted' : 'text-primary'" />
                            <template x-if="selected.website">
                                <a :href="selected.website" target="_blank" rel="noopener" class="text-info hover:underline truncate" x-text="selected.website"></a>
                            </template>
                            <template x-if="!selected.website">
                                <span class="text-primary text-xs font-medium">No website — your opportunity</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <x-icon name="phone" class="w-4 h-4 shrink-0 text-muted" />
                            <template x-if="selected.phone">
                                <a :href="`tel:${selected.phone}`" class="text-text hover:text-primary transition-colors" x-text="selected.phone"></a>
                            </template>
                            <template x-if="!selected.phone">
                                <span class="text-muted text-xs">No phone listed</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2.5" x-show="selected.email">
                            <x-icon name="inbox" class="w-4 h-4 shrink-0 text-muted" />
                            <a :href="`mailto:${selected.email}`" class="text-text hover:text-primary transition-colors truncate" x-text="selected.email"></a>
                        </div>
                        <div class="flex items-center gap-2.5" x-show="selected.address">
                            <x-icon name="map-pin" class="w-4 h-4 shrink-0 text-muted" />
                            <span class="text-text" x-text="selected.address"></span>
                        </div>
                        <div class="flex items-center gap-2.5" x-show="selected.social && Object.keys(selected.social).length">
                            <x-icon name="users" class="w-4 h-4 shrink-0 text-muted" />
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="[network, url] in Object.entries(selected.social ?? {})" :key="network">
                                    <a :href="url.startsWith('http') ? url : `https://${url}`" target="_blank" rel="noopener"
                                       class="badge badge-muted hover:border-primary/40 transition-colors" x-text="network"></a>
                                </template>
                            </div>
                        </div>
                        <div class="pt-1">
                            <a :href="`https://www.google.com/search?q=${encodeURIComponent(selected.name + ' ' + (selected.address ?? 'Redlands CA'))}`"
                               target="_blank" rel="noopener"
                               class="text-xs text-info hover:underline flex items-center gap-1">
                                <x-icon name="external" class="w-3.5 h-3.5" />
                                Google this business
                            </a>
                        </div>
                    </div>

                    {{-- Found on website --}}
                    <div x-show="selected.website" class="rounded-xl border border-border bg-surface-2/40 p-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="label mb-0 flex items-center gap-1.5">
                                <x-icon name="search" class="w-3.5 h-3.5 text-primary" />
                                Found on website
                            </p>
                            <button @click="scanWebsite()" :disabled="scanning"
                                    class="text-[11px] text-muted hover:text-primary transition-colors flex items-center gap-1"
                                    :class="scanning ? 'opacity-50 cursor-wait' : ''">
                                <svg x-show="scanning" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="selected.scanned_at ? 'Re-scan' : 'Scan'"></span>
                            </button>
                        </div>

                        {{-- Scanning state --}}
                        <div x-show="scanning && !scanHasResults(selected.scan_data)" class="text-xs text-muted py-1">
                            Reading the website for contact details…
                        </div>

                        {{-- Results --}}
                        <template x-if="scanHasResults(selected.scan_data)">
                            <div class="space-y-2.5">
                                {{-- Emails --}}
                                <div x-show="selected.scan_data.emails?.length">
                                    <p class="text-[10px] uppercase tracking-wider text-muted mb-1">Emails</p>
                                    <div class="space-y-1">
                                        <template x-for="email in selected.scan_data.emails" :key="email">
                                            <a :href="`mailto:${email}`" class="flex items-center gap-2 text-sm text-info hover:underline">
                                                <x-icon name="inbox" class="w-3.5 h-3.5 shrink-0 text-muted" />
                                                <span class="truncate" x-text="email"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                                {{-- Phones --}}
                                <div x-show="selected.scan_data.phones?.length">
                                    <p class="text-[10px] uppercase tracking-wider text-muted mb-1">Phone numbers</p>
                                    <div class="space-y-1">
                                        <template x-for="phone in selected.scan_data.phones" :key="phone">
                                            <a :href="`tel:${phone}`" class="flex items-center gap-2 text-sm text-text hover:text-primary transition-colors">
                                                <x-icon name="phone" class="w-3.5 h-3.5 shrink-0 text-muted" />
                                                <span x-text="phone"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                                {{-- Names --}}
                                <div x-show="selected.scan_data.names?.length">
                                    <p class="text-[10px] uppercase tracking-wider text-muted mb-1">Names / contacts</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="name in selected.scan_data.names" :key="name">
                                            <span class="badge badge-green" x-text="name"></span>
                                        </template>
                                    </div>
                                </div>
                                {{-- Social --}}
                                <div x-show="selected.scan_data.social && Object.keys(selected.scan_data.social).length">
                                    <p class="text-[10px] uppercase tracking-wider text-muted mb-1">Social</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="[network, url] in Object.entries(selected.scan_data.social ?? {})" :key="network">
                                            <a :href="url" target="_blank" rel="noopener"
                                               class="badge badge-muted hover:border-primary/40 transition-colors" x-text="network"></a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Scanned, nothing found --}}
                        <p x-show="selected.scanned_at && !scanning && !scanHasResults(selected.scan_data)"
                           class="text-xs text-muted py-1">
                            No extra contact details found on the site.
                        </p>
                    </div>

                    {{-- Status pipeline --}}
                    <div>
                        <p class="label mb-2">Status</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($statuses as $status)
                            <button @click="setStatus('{{ $status }}')"
                                    :class="selected.status === '{{ $status }}'
                                        ? 'pipeline-btn-active pipeline-{{ $status }}'
                                        : 'border-border text-muted hover:border-primary/30 hover:text-text'"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border text-xs font-medium transition-all">
                                <span class="status-dot status-dot-{{ $status }}"></span>
                                <span x-text="statusLabels['{{ $status }}']"></span>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <p class="label mb-2">Notes</p>
                        <div class="flex gap-2 mb-3">
                            <textarea x-model="noteBody" rows="2" placeholder="Add a note from your investigation…"
                                      @keydown.ctrl.enter="addNote()"
                                      class="input text-sm resize-none flex-1"></textarea>
                        </div>
                        <button @click="addNote()" :disabled="!noteBody.trim() || savingNote"
                                class="btn-primary btn-sm w-full justify-center mb-3"
                                :class="(!noteBody.trim() || savingNote) ? 'opacity-50 cursor-not-allowed' : ''">
                            <x-icon name="plus" class="w-4 h-4" />
                            Add Note
                        </button>

                        <template x-if="loadingDetail">
                            <div class="space-y-2">
                                <div class="skeleton h-12 rounded-lg"></div>
                                <div class="skeleton h-12 rounded-lg"></div>
                            </div>
                        </template>

                        <div class="space-y-2">
                            <template x-for="note in (selected.notes ?? [])" :key="note.id">
                                <div class="px-3 py-2.5 rounded-lg bg-surface-2 border border-border">
                                    <p class="text-sm text-text whitespace-pre-wrap leading-snug" x-text="note.body"></p>
                                    <p class="text-[10px] text-muted mt-1.5 font-mono" x-text="formatDate(note.created_at)"></p>
                                </div>
                            </template>
                            <p x-show="!loadingDetail && (!selected.notes || selected.notes.length === 0)"
                               class="text-xs text-muted text-center py-2">No notes yet.</p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-border shrink-0">
                    <button @click="removeProspect()" class="btn-danger btn-sm w-full justify-center">
                        <x-icon name="trash" class="w-3.5 h-3.5" />
                        Remove from Prospects
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- ── Searching overlay ──────────────────────────────────────────── --}}
    <div x-show="searching" x-cloak
         x-transition.opacity
         class="absolute inset-0 z-[1100] flex items-center justify-center"
         style="background: rgba(13,17,23,0.7); backdrop-filter: blur(3px);">
        <div class="card flex flex-col items-center gap-4 px-10 py-8 border-primary/30">
            <svg class="animate-spin w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm text-text" x-text="searchingMessage"></p>
        </div>
    </div>

    {{-- ── Toast ──────────────────────────────────────────────────────── --}}
    <div x-show="toast" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute bottom-6 left-1/2 -translate-x-1/2 z-[1200]">
        <template x-if="toast">
            <div class="card flex items-center gap-3 px-4 py-3 shadow-2xl"
                 :class="toast.type === 'success' ? 'border-primary/40' : 'border-[var(--color-danger)]/50'">
                <x-icon name="check" class="w-5 h-5 text-primary shrink-0" x-show="toast.type === 'success'" />
                <x-icon name="warning" class="w-5 h-5 text-[var(--color-danger)] shrink-0" x-show="toast.type === 'error'" />
                <p class="text-sm text-text" x-text="toast.text"></p>
                <button @click="toast = null" class="text-muted hover:text-text transition-colors ml-2">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        </template>
    </div>
</div>
@endsection
