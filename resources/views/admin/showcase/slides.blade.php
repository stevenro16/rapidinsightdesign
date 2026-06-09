@extends('layouts.portal')
@section('title', 'Manage Slides — ' . $showroomItem->title)
@section('page-title', 'Slides')
@section('breadcrumb', $showroomItem->title)

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.showcase.index') }}" class="btn-ghost btn-sm gap-1.5">
            <x-icon name="chevron-left" class="w-4 h-4" />
            Back to Showcase
        </a>
        <span class="badge badge-muted">{{ $showroomItem->slides->count() }} slide(s)</span>
    </div>

    @if(session('success'))
    <div x-data="flash()" x-show="visible" x-transition
         class="p-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Existing slides --}}
    @if($showroomItem->slides->isNotEmpty())
    <div class="space-y-3">
        @foreach($showroomItem->slides as $slide)
        <div class="card" x-data="{ editing: false, removing: false }">

            {{-- Collapsed view --}}
            <div class="flex items-start gap-4">
                <div class="w-24 h-16 rounded-lg overflow-hidden bg-surface-2 shrink-0 flex items-center justify-center">
                    @if($slide->image_path)
                    <img src="{{ Storage::url($slide->image_path) }}" class="w-full h-full object-cover">
                    @else
                    <x-icon name="computer" class="w-6 h-6 text-border" />
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-xs font-mono text-primary">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <p class="font-semibold text-text truncate">{{ $slide->title }}</p>
                    </div>
                    @if($slide->headline)
                    <p class="text-sm text-muted truncate">{{ $slide->headline }}</p>
                    @endif
                    @if($slide->bullets)
                    <p class="text-xs text-muted mt-0.5">{{ count($slide->bullets) }} bullet(s)</p>
                    @endif
                </div>

                <div class="flex gap-2 shrink-0">
                    <button @click="editing = !editing" class="btn-ghost btn-sm">
                        <x-icon name="pencil" class="w-3.5 h-3.5" />
                    </button>
                    <form method="POST"
                          action="{{ route('admin.showcase.slides.destroy', [$showroomItem, $slide]) }}"
                          x-data="confirmDelete('Delete slide &quot;{{ addslashes($slide->title) }}&quot;?')">
                        @csrf @method('DELETE')
                        <button @click.prevent="confirm($el.closest('form'))" class="btn-danger btn-sm">
                            <x-icon name="trash" class="w-3.5 h-3.5" />
                        </button>
                    </form>
                </div>
            </div>

            {{-- Inline edit form --}}
            <div x-show="editing" x-transition class="mt-4 pt-4 border-t border-border">
                <form method="POST"
                      action="{{ route('admin.showcase.slides.update', [$showroomItem, $slide]) }}"
                      enctype="multipart/form-data"
                      class="space-y-4">
                    @csrf @method('PUT')

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Tab Title</label>
                            <input type="text" name="title" value="{{ old('title', $slide->title) }}" class="input" required>
                        </div>
                        <div>
                            <label class="label">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $slide->sort_order) }}" class="input" min="0">
                        </div>
                    </div>

                    <div>
                        <label class="label">Headline</label>
                        <input type="text" name="headline" value="{{ old('headline', $slide->headline) }}" class="input" placeholder="Bold text shown on the right">
                    </div>

                    <div>
                        <label class="label">Description</label>
                        <textarea name="description" rows="2" class="input resize-none">{{ old('description', $slide->description) }}</textarea>
                    </div>

                    <div>
                        <label class="label">Bullet Points <span class="text-muted font-normal">(one per line)</span></label>
                        <textarea name="bullets" rows="4" class="input resize-none font-mono text-sm">{{ old('bullets', $slide->bullets ? implode("\n", $slide->bullets) : '') }}</textarea>
                    </div>

                    {{-- Image --}}
                    <div>
                        <label class="label">Screenshot Image</label>
                        @if($slide->image_path)
                        <div class="flex items-start gap-3 mb-2">
                            <img src="{{ Storage::url($slide->image_path) }}" alt="Current screenshot"
                                 class="w-24 h-16 object-cover rounded-lg border border-border">
                            <label class="flex items-center gap-2 mt-1 cursor-pointer text-sm text-muted">
                                <input type="checkbox" name="remove_image" value="1" x-model="removing" class="rounded">
                                Remove current image
                            </label>
                        </div>
                        @endif
                        <input type="file" name="image" accept="image/*"
                               :disabled="removing"
                               class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30 disabled:opacity-40">
                        <p class="text-xs text-muted mt-1">JPG, PNG, GIF, WebP — max 4 MB</p>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary btn-sm">Save Changes</button>
                        <button type="button" @click="editing = false" class="btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
        @endforeach
    </div>
    @else
    <div class="card text-center py-10 text-muted">No slides yet. Add one below.</div>
    @endif

    {{-- Add slide form --}}
    <div class="card">
        <h3 class="font-semibold text-text mb-4">Add Slide</h3>
        <form method="POST"
              action="{{ route('admin.showcase.slides.store', $showroomItem) }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="label">Tab Title <span class="text-muted font-normal">(short, e.g. "Scheduling")</span></label>
                    <input type="text" name="title" class="input" placeholder="Scheduling" required>
                </div>
                <div>
                    <label class="label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ $showroomItem->slides->count() }}" class="input" min="0">
                </div>
            </div>

            <div>
                <label class="label">Headline <span class="text-muted font-normal">(large bold text)</span></label>
                <input type="text" name="headline" class="input" placeholder="Book the visit with the whole crew's day in view.">
            </div>

            <div>
                <label class="label">Description <span class="text-muted font-normal">(paragraph below headline)</span></label>
                <textarea name="description" rows="2" class="input resize-none" placeholder="Pick a date, time and duration and instantly see…"></textarea>
            </div>

            <div>
                <label class="label">Bullet Points <span class="text-muted font-normal">(one per line)</span></label>
                <textarea name="bullets" rows="4" class="input resize-none font-mono text-sm"
                          placeholder="No double-booking — conflicts visible before you commit&#10;One-tap durations from 30 minutes to 8 hours&#10;Confirm or request confirmation in the same step"></textarea>
            </div>

            <div>
                <label class="label">Screenshot Image</label>
                <input type="file" name="image" accept="image/*"
                       class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
                <p class="text-xs text-muted mt-1">JPG, PNG, GIF, WebP — max 4 MB</p>
            </div>

            <button type="submit" class="btn-primary">Add Slide</button>
        </form>
    </div>

</div>
@endsection
