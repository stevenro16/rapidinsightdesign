@extends('layouts.portal')
@section('title', 'Showcase Management')
@section('page-title', 'Showcase')

@section('content')
<div x-data="tabs('items')" class="space-y-6">

    {{-- Tab nav --}}
    <div class="flex gap-1 border-b border-[var(--color-border)]">
        <button @click="setTab('items')"
                :class="isActive('items') ? 'text-[var(--color-primary)] border-b-2 border-[var(--color-primary)]' : 'text-[var(--color-muted)]'"
                class="px-4 py-2 text-sm font-medium transition-colors -mb-px">
            Showcase Items ({{ $items->count() }})
        </button>
        <button @click="setTab('access')"
                :class="isActive('access') ? 'text-[var(--color-primary)] border-b-2 border-[var(--color-primary)]' : 'text-[var(--color-muted)]'"
                class="px-4 py-2 text-sm font-medium transition-colors -mb-px">
            Customer Access
        </button>
        <button @click="setTab('add')"
                :class="isActive('add') ? 'text-[var(--color-primary)] border-b-2 border-[var(--color-primary)]' : 'text-[var(--color-muted)]'"
                class="px-4 py-2 text-sm font-medium transition-colors -mb-px">
            Add Item
        </button>
    </div>

    {{-- Items tab --}}
    <div x-show="isActive('items')">
        @if($items->isEmpty())
        <div class="card text-center py-10 text-[var(--color-muted)]">No showcase items yet. Click "Add Item" to create one.</div>
        @else
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-muted flex items-center gap-1.5">
                <x-icon name="menu" class="w-3.5 h-3.5" />
                Drag a card by its handle to reorder how items appear in the Showcase.
            </p>
        </div>

        <div x-data="sortable('{{ route('admin.showcase.reorder') }}')"
             class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <span x-show="saving" class="col-span-full text-xs text-primary">Saving order…</span>

            @foreach($items as $item)
            <div data-sortable-item data-id="{{ $item->id }}"
                 x-data="{ editing: false }"
                 class="relative rounded-xl overflow-hidden border border-border group h-[420px] bg-surface-2 transition-colors hover:border-primary/40">

                {{-- Full-bleed thumbnail --}}
                @if($item->thumbnail_path)
                <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                @else
                <div class="absolute inset-0 flex items-center justify-center">
                    <x-icon name="computer" class="w-12 h-12 text-border" />
                </div>
                @endif

                {{-- Translucent gradient banner — lighter so the image shows through --}}
                <div class="absolute inset-0 pointer-events-none"
                     style="background: linear-gradient(to top, rgba(0,0,0,0.78) 0%, rgba(0,0,0,0.35) 50%, transparent 100%);"></div>

                {{-- Top bar: drag handle + status (overlaid) --}}
                <div class="absolute top-0 inset-x-0 flex items-center justify-between px-3 py-2 z-10">
                    <span class="drag-handle cursor-grab active:cursor-grabbing text-white/70 hover:text-white p-1 -ml-1" title="Drag to reorder">
                        <svg class="w-4 h-4 drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/>
                            <circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/>
                            <circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/>
                        </svg>
                    </span>
                    <span class="badge {{ $item->is_active ? 'badge-green' : 'badge-muted' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                </div>

                {{-- Bottom content overlaid on the image --}}
                <div class="absolute inset-x-0 bottom-0 p-4 z-10 flex flex-col gap-2">
                    <h3 class="font-semibold text-white leading-snug drop-shadow">{{ $item->title }}</h3>
                    @if($item->description)
                    <p class="text-xs text-white/70 line-clamp-2 leading-snug">{{ $item->description }}</p>
                    @endif
                    @if($item->tech_tags)
                    <div class="flex flex-wrap gap-1">
                        @foreach($item->techTagsArray() as $tag)
                        <span class="badge badge-muted text-[10px]">{{ trim($tag) }}</span>
                        @endforeach
                    </div>
                    @endif

                    {{-- Meta chips --}}
                    <div class="flex flex-wrap items-center gap-1.5 pt-1 text-[11px] text-white/70">
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="grid" class="w-3 h-3" />{{ $item->slides->count() }} {{ $item->slides->count() === 1 ? 'slide' : 'slides' }}
                        </span>
                        <span>·</span>
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="users" class="w-3 h-3" />{{ $item->customers->count() }}
                        </span>
                        @if($item->hasPreview())
                        <span class="badge badge-blue text-[10px]">{{ $item->previewMode() === 'window' ? '↗ New window' : '▢ Frame' }} preview</span>
                        @else
                        <span class="badge badge-muted text-[10px]">No preview</span>
                        @endif
                        @if($item->private_url)
                        <span class="inline-flex items-center gap-1"><x-icon name="lock" class="w-3 h-3" />Private URL</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-2">
                        <a href="{{ route('admin.showcase.slides.index', $item) }}"
                           class="btn-ghost btn-sm gap-1 flex-1 justify-center bg-black/30 backdrop-blur-sm">
                            <x-icon name="grid" class="w-3.5 h-3.5" />
                            <span class="text-xs">Slides</span>
                        </a>
                        <button @click="editing = true" class="btn-ghost btn-sm bg-black/30 backdrop-blur-sm" title="Edit">
                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                        </button>
                        <form method="POST" action="/admin/showcase/{{ $item->id }}" x-data="confirmDelete('Delete {{ addslashes($item->title) }}?')">
                            @csrf @method('DELETE')
                            <button @click.prevent="confirm($el.closest('form'))" class="btn-danger btn-sm" title="Delete">
                                <x-icon name="trash" class="w-3.5 h-3.5" />
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Edit modal --}}
                <div x-show="editing" x-cloak
                     @keydown.escape.window="editing = false"
                     @click.self="editing = false"
                     class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
                     style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
                    <div x-show="editing"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="card w-full max-w-2xl my-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-text">Edit Showcase Item</h3>
                            <button @click="editing = false" class="btn-ghost btn-sm"><x-icon name="x" class="w-4 h-4" /></button>
                        </div>
                        <form method="POST" action="{{ route('admin.showcase.update', $item) }}" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            @if($errors->any())
                            <div class="mb-3 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                </ul>
                            </div>
                            @endif
                            <div class="grid sm:grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="label">Title</label>
                                    <input type="text" name="title" value="{{ old('title', $item->title) }}" class="input" required>
                                </div>
                                <div>
                                    <label class="label">Sort Order <span class="text-muted font-normal">(or drag cards)</span></label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order) }}" class="input" min="0">
                                </div>
                                <div>
                                    <label class="label">Private URL <span class="text-muted font-normal">(logged-in users only)</span></label>
                                    <input type="url" name="private_url" value="{{ old('private_url', $item->private_url) }}" class="input" placeholder="https://full-demo.example.com">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="label">Tech Tags (comma-separated)</label>
                                    <input type="text" name="tech_tags" value="{{ old('tech_tags', $item->tech_tags) }}" class="input" placeholder="Laravel, Alpine.js">
                                </div>
                            </div>
                            <div><label class="label">Description</label><textarea name="description" rows="2" class="input resize-none mb-3">{{ old('description', $item->description) }}</textarea></div>

                            {{-- Demo login — revealed to a customer once their access is approved --}}
                            <div class="mb-3 p-3 rounded-lg border border-border bg-surface-2/40">
                                <label class="label">Demo Login <span class="text-muted font-normal">(shown to approved customers alongside the Private URL)</span></label>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <input type="text" name="demo_username" value="{{ old('demo_username', $item->demo_username) }}" class="input" placeholder="Username / email">
                                    <input type="text" name="demo_password" value="{{ old('demo_password', $item->demo_password) }}" class="input" placeholder="Password">
                                </div>
                                <textarea name="access_notes" rows="2" class="input resize-none mt-2" placeholder="Optional instructions shown with the login">{{ old('access_notes', $item->access_notes) }}</textarea>
                            </div>

                            {{-- Thumbnail --}}
                            <div class="mb-3" x-data="{ removing: false }">
                                <label class="label">Preview Image</label>
                                @if($item->thumbnail_path)
                                <div class="flex items-start gap-3 mb-2">
                                    <img src="{{ Storage::url($item->thumbnail_path) }}" alt="Current thumbnail"
                                         class="w-24 h-16 object-cover rounded-lg border border-border">
                                    <label class="flex items-center gap-2 mt-1 cursor-pointer text-sm text-muted">
                                        <input type="checkbox" name="remove_thumbnail" value="1" x-model="removing" class="rounded">
                                        Remove current image
                                    </label>
                                </div>
                                @endif
                                <input type="file" name="thumbnail" accept="image/*"
                                       :disabled="removing"
                                       class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30 disabled:opacity-40">
                                <p class="text-xs text-muted mt-1">JPG, PNG, GIF, WebP — max 4 MB</p>
                            </div>

                            {{-- Public Preview (URL or HTML file, shown when card is clicked) --}}
                            <div class="mb-3 p-3 rounded-lg border border-border bg-surface-2/40" x-data="{ removing: false }">
                                <label class="label">Public Preview <span class="text-muted font-normal">(shown when card is clicked)</span></label>

                                <input type="url" name="preview_url" value="{{ old('preview_url', $item->preview_url) }}"
                                       class="input mb-2" placeholder="https://demo.example.com">

                                <p class="text-xs text-muted mb-1">…or upload an HTML file:</p>
                                @if($item->preview_html_path)
                                <div class="flex items-center gap-3 mb-2 p-2 rounded-lg bg-surface-2 border border-border">
                                    <x-icon name="document" class="w-4 h-4 text-primary shrink-0" />
                                    <span class="text-xs text-muted truncate flex-1">{{ basename($item->preview_html_path) }}</span>
                                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-muted shrink-0">
                                        <input type="checkbox" name="remove_preview_html" value="1" x-model="removing" class="rounded">
                                        Remove
                                    </label>
                                </div>
                                @endif
                                <input type="file" name="preview_html" accept=".html,.htm"
                                       :disabled="removing"
                                       class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30 disabled:opacity-40">
                                <p class="text-xs text-muted mt-1">.html or .htm — max 2 MB. If a URL is set above, it takes precedence over the file.</p>

                                <label class="label mt-3">Display in</label>
                                <select name="preview_mode" class="select">
                                    <option value="frame" {{ old('preview_mode', $item->preview_mode) === 'frame' ? 'selected' : '' }}>Preview frame (embedded)</option>
                                    <option value="window" {{ old('preview_mode', $item->preview_mode) === 'window' ? 'selected' : '' }}>New window / tab</option>
                                </select>
                            </div>

                            <label class="flex items-center gap-2 mb-3 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }} class="rounded">
                                <span class="text-sm text-[var(--color-text)]">Active (visible in ShowRoom)</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary btn-sm">Save</button>
                                <button type="button" @click="editing = false" class="btn-ghost btn-sm">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Access tab --}}
    <div x-show="isActive('access')">
        @if($items->isEmpty())
        <p class="text-[var(--color-muted)] text-sm">Add showcase items first.</p>
        @else

        {{-- How it works --}}
        <div class="card mb-5 border border-dashed border-[var(--color-border)] bg-surface-2/30">
            <p class="font-semibold text-[var(--color-text)] mb-3 flex items-center gap-2">
                <x-icon name="info" class="w-4 h-4 text-primary" /> How demo access works
            </p>
            <div class="grid sm:grid-cols-3 gap-3 text-xs text-[var(--color-muted)]">
                <div class="flex gap-2">
                    <span class="shrink-0 w-5 h-5 rounded-full bg-primary/20 text-primary font-bold flex items-center justify-center">1</span>
                    <span>A customer opens the <strong class="text-[var(--color-text)]">ShowRoom</strong> and taps <strong class="text-[var(--color-text)]">Request Access</strong> on a demo.</span>
                </div>
                <div class="flex gap-2">
                    <span class="shrink-0 w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center">2</span>
                    <span>It shows here as <strong class="text-amber-400">Pending</strong> (and you're emailed). Click <strong class="text-[var(--color-text)]">Approve</strong> — or grant access directly anytime.</span>
                </div>
                <div class="flex gap-2">
                    <span class="shrink-0 w-5 h-5 rounded-full bg-primary/20 text-primary font-bold flex items-center justify-center">3</span>
                    <span>The customer is notified and gets a <strong class="text-[var(--color-text)]">Launch</strong> button plus the demo login on their card.</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            @foreach($items as $item)
            @php
                $pending  = $item->customers->filter(fn($c) => ($c->pivot->status ?? 'approved') === 'pending');
                $approved = $item->customers->filter(fn($c) => ($c->pivot->status ?? 'approved') === 'approved');
            @endphp
            <div class="card {{ $pending->isNotEmpty() ? 'border border-amber-500/40' : '' }}">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-4 gap-3 flex-wrap">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-surface-2 shrink-0 flex items-center justify-center border border-border">
                            @if($item->thumbnail_path)
                            <img src="{{ Storage::url($item->thumbnail_path) }}" alt="" class="w-full h-full object-cover">
                            @else
                            <x-icon name="computer" class="w-5 h-5 text-[var(--color-border)]" />
                            @endif
                        </div>
                        <h3 class="font-semibold text-[var(--color-text)] truncate">{{ $item->title }}</h3>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($pending->isNotEmpty())
                        <span class="badge badge-amber">{{ $pending->count() }} pending</span>
                        @endif
                        <span class="badge badge-muted">{{ $approved->count() }} with access</span>
                    </div>
                </div>

                {{-- Pending access requests --}}
                @if($pending->isNotEmpty())
                <div class="mb-4">
                    <p class="label text-amber-400 mb-1.5 flex items-center gap-1"><x-icon name="warning" class="w-3.5 h-3.5" /> Awaiting your approval</p>
                    <div class="space-y-1.5">
                        @foreach($pending as $customer)
                        <div class="flex items-center justify-between gap-2 rounded-lg px-3 py-2 bg-amber-500/10 border border-amber-500/30">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[var(--color-text)] truncate">{{ $customer->name }}</p>
                                <p class="text-xs text-[var(--color-muted)] truncate">{{ $customer->email }} · requested {{ $customer->pivot->requested_at?->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form method="POST" action="/admin/showcase/{{ $item->id }}/approve/{{ $customer->id }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm gap-1"><x-icon name="check" class="w-3.5 h-3.5" /> Approve</button>
                                </form>
                                <form method="POST" action="/admin/showcase/{{ $item->id }}/revoke/{{ $customer->id }}"
                                      x-data="confirmDelete('Deny {{ addslashes($customer->name) }}\'s request?')">
                                    @csrf @method('DELETE')
                                    <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-[var(--color-danger)]">Deny</button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Grant access directly --}}
                <p class="label mb-1.5">Grant access directly</p>
                <form method="POST" class="flex gap-2 mb-4" id="grant-{{ $item->id }}">
                    @csrf
                    <select name="user_id" class="select flex-1">
                        <option value="">— Select a customer —</option>
                        @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            @click.prevent="
                                const sel = $el.closest('form').querySelector('select');
                                if (sel.value) {
                                    $el.closest('form').action = '/admin/showcase/{{ $item->id }}/grant/' + sel.value;
                                    $el.closest('form').submit();
                                }
                            "
                            class="btn-primary btn-sm whitespace-nowrap">
                        Grant Access
                    </button>
                </form>

                {{-- Approved access list --}}
                <p class="label mb-1.5">Customers with access</p>
                @if($approved->isNotEmpty())
                <div class="space-y-1.5">
                    @foreach($approved as $customer)
                    <div class="flex items-center justify-between bg-surface-2 rounded-lg px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[var(--color-text)] truncate">{{ $customer->name }}</p>
                            <p class="text-xs text-[var(--color-muted)] truncate">{{ $customer->email }}</p>
                        </div>
                        <form method="POST" action="/admin/showcase/{{ $item->id }}/revoke/{{ $customer->id }}"
                              x-data="confirmDelete('Remove {{ addslashes($customer->name) }}\'s access?')">
                            @csrf @method('DELETE')
                            <button @click.prevent="confirm($el.closest('form'))" class="text-xs text-[var(--color-danger)] hover:underline shrink-0">Revoke</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-[var(--color-muted)]">No customers have access yet.</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Add item tab --}}
    <div x-show="isActive('add')">
        <div class="card max-w-lg">
            <h3 class="font-semibold text-[var(--color-text)] mb-4">New Showcase Item</h3>
            <form method="POST" action="{{ route('admin.showcase.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div><label class="label">Title</label><input type="text" name="title" class="input" required></div>
                    <div>
                        <label class="label">Private URL <span class="text-muted font-normal">(logged-in users only)</span></label>
                        <input type="url" name="private_url" value="{{ old('private_url') }}" class="input" placeholder="https://full-demo.example.com">
                    </div>
                    <div class="p-3 rounded-lg border border-border bg-surface-2/40">
                        <label class="label">Demo Login <span class="text-muted font-normal">(shown to approved customers)</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" name="demo_username" value="{{ old('demo_username') }}" class="input" placeholder="Username / email">
                            <input type="text" name="demo_password" value="{{ old('demo_password') }}" class="input" placeholder="Password">
                        </div>
                        <textarea name="access_notes" rows="2" class="input resize-none mt-2" placeholder="Optional access instructions">{{ old('access_notes') }}</textarea>
                    </div>
                    <div><label class="label">Description</label><textarea name="description" rows="3" class="input resize-none"></textarea></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="label">Tech Tags (comma-sep)</label><input type="text" name="tech_tags" class="input" placeholder="Laravel, Alpine.js"></div>
                        <div><label class="label">Sort Order</label><input type="number" name="sort_order" value="0" class="input" min="0"></div>
                    </div>
                    <div>
                        <label class="label">Preview Image</label>
                        <input type="file" name="thumbnail" accept="image/*"
                               class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
                        <p class="text-xs text-muted mt-1">JPG, PNG, GIF, WebP — max 4 MB</p>
                    </div>
                    <div class="p-3 rounded-lg border border-border bg-surface-2/40">
                        <label class="label">Public Preview <span class="text-muted font-normal">(shown when card is clicked)</span></label>
                        <input type="url" name="preview_url" value="{{ old('preview_url') }}"
                               class="input mb-2" placeholder="https://demo.example.com">
                        <p class="text-xs text-muted mb-1">…or upload an HTML file:</p>
                        <input type="file" name="preview_html" accept=".html,.htm"
                               class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
                        <p class="text-xs text-muted mt-1">.html or .htm — max 2 MB. If a URL is set above, it takes precedence over the file.</p>

                        <label class="label mt-3">Display in</label>
                        <select name="preview_mode" class="select">
                            <option value="frame" {{ old('preview_mode', 'frame') === 'frame' ? 'selected' : '' }}>Preview frame (embedded)</option>
                            <option value="window" {{ old('preview_mode') === 'window' ? 'selected' : '' }}>New window / tab</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded">
                        <span class="text-sm text-text">Active (immediately visible)</span>
                    </label>
                    <button type="submit" class="btn-primary">Add to Showcase</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
