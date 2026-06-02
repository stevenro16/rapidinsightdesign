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
        <div class="space-y-3">
            @foreach($items as $item)
            <div class="card" x-data="{ editing: false }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-semibold text-[var(--color-text)]">{{ $item->title }}</h3>
                            <span class="badge {{ $item->is_active ? 'badge-green' : 'badge-muted' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                        <p class="text-sm text-[var(--color-muted)] mb-1">{{ $item->embed_url }}</p>
                        @if($item->tech_tags)
                        <div class="flex flex-wrap gap-1">
                            @foreach($item->techTagsArray() as $tag)
                            <span class="badge badge-muted text-xs">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button @click="editing = !editing" class="btn-ghost btn-sm">
                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                        </button>
                        <form method="POST" action="/admin/showcase/{{ $item->id }}" x-data="confirmDelete('Delete {{ addslashes($item->title) }}?')">
                            @csrf @method('DELETE')
                            <button @click.prevent="confirm($el.closest('form'))" class="btn-danger btn-sm">
                                <x-icon name="trash" class="w-3.5 h-3.5" />
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Inline edit form --}}
                <div x-show="editing" x-transition class="mt-4 pt-4 border-t border-[var(--color-border)]">
                    <form method="POST" action="/admin/showcase/{{ $item->id }}">
                        @csrf @method('PUT')
                        <div class="grid sm:grid-cols-2 gap-3 mb-3">
                            <div><label class="label">Title</label><input type="text" name="title" value="{{ $item->title }}" class="input" required></div>
                            <div><label class="label">Embed URL</label><input type="url" name="embed_url" value="{{ $item->embed_url }}" class="input" required></div>
                            <div><label class="label">Tech Tags (comma-separated)</label><input type="text" name="tech_tags" value="{{ $item->tech_tags }}" class="input" placeholder="Laravel, Alpine.js"></div>
                            <div><label class="label">Sort Order</label><input type="number" name="sort_order" value="{{ $item->sort_order }}" class="input" min="0"></div>
                        </div>
                        <div><label class="label">Description</label><textarea name="description" rows="2" class="input resize-none mb-3">{{ $item->description }}</textarea></div>
                        <label class="flex items-center gap-2 mb-3 cursor-pointer">
                            <input type="checkbox" name="is_active" {{ $item->is_active ? 'checked' : '' }} class="rounded">
                            <span class="text-sm text-[var(--color-text)]">Active (visible in ShowRoom)</span>
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary btn-sm">Save</button>
                            <button type="button" @click="editing = false" class="btn-ghost btn-sm">Cancel</button>
                        </div>
                    </form>
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
        <div class="space-y-4">
            @foreach($items as $item)
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-[var(--color-text)]">{{ $item->title }}</h3>
                    <span class="badge badge-muted">{{ $item->customers->count() }} customers</span>
                </div>

                {{-- Grant access form --}}
                <form method="POST" class="flex gap-2 mb-4" id="grant-{{ $item->id }}">
                    @csrf
                    <select name="user_id" class="select flex-1">
                        <option value="">— Select customer —</option>
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

                {{-- Current access list --}}
                @if($item->customers->isNotEmpty())
                <div class="space-y-1.5">
                    @foreach($item->customers as $customer)
                    <div class="flex items-center justify-between bg-[var(--color-surface-2)] rounded-lg px-3 py-2">
                        <div>
                            <p class="text-sm font-medium text-[var(--color-text)]">{{ $customer->name }}</p>
                            <p class="text-xs text-[var(--color-muted)]">{{ $customer->email }}</p>
                        </div>
                        <form method="POST" action="/admin/showcase/{{ $item->id }}/revoke/{{ $customer->id }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-[var(--color-danger)] hover:underline">Revoke</button>
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
            <form method="POST" action="/admin/showcase">
                @csrf
                <div class="space-y-4">
                    <div><label class="label">Title</label><input type="text" name="title" class="input" required></div>
                    <div><label class="label">Embed URL</label><input type="url" name="embed_url" class="input" placeholder="https://your-app.com" required></div>
                    <div><label class="label">Description</label><textarea name="description" rows="3" class="input resize-none"></textarea></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="label">Tech Tags (comma-sep)</label><input type="text" name="tech_tags" class="input" placeholder="Laravel, Alpine.js"></div>
                        <div><label class="label">Sort Order</label><input type="number" name="sort_order" value="0" class="input" min="0"></div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" checked class="rounded">
                        <span class="text-sm text-[var(--color-text)]">Active (immediately visible)</span>
                    </label>
                    <button type="submit" class="btn-primary">Add to Showcase</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
