@extends('layouts.portal')
@section('title', 'ShowRoom')
@section('page-title', 'ShowRoom')
@section('breadcrumb', 'Your accessible demos and applications')

@section('content')

@if($items->isEmpty())
<div class="flex flex-col items-center justify-center h-64 text-center">
    <x-icon name="grid" class="w-12 h-12 text-[var(--color-border)] mb-4" />
    <p class="font-semibold text-[var(--color-text)] mb-1">No demos available yet</p>
    <p class="text-sm text-[var(--color-muted)]">
        @if(auth()->user()->isCustomer())
            Contact us to get access to our products.
        @else
            Add showcase items in the admin panel.
        @endif
    </p>
</div>
@else
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($items as $item)
    <div class="card card-hover group flex flex-col">
        {{-- Thumbnail --}}
        <div class="h-36 rounded-lg mb-4 overflow-hidden bg-[var(--color-surface-2)] flex items-center justify-center">
            @if($item->thumbnail_path)
            <img src="{{ Storage::url($item->thumbnail_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
            @else
            <x-icon name="computer" class="w-10 h-10 text-[var(--color-border)]" />
            @endif
        </div>

        <div class="flex-1">
            <div class="flex items-start justify-between gap-2 mb-1">
                <h3 class="font-display font-semibold text-[var(--color-text)]">{{ $item->title }}</h3>
                @if(! $item->is_active)
                <span class="badge badge-muted shrink-0">Inactive</span>
                @endif
            </div>
            @if($item->description)
            <p class="text-sm text-[var(--color-muted)] mb-3 line-clamp-2">{{ $item->description }}</p>
            @endif
            @if($item->tech_tags)
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($item->techTagsArray() as $tag)
                <span class="badge badge-green text-xs">{{ trim($tag) }}</span>
                @endforeach
            </div>
            @endif
        </div>

        <a href="/showroom/{{ $item->id }}" class="btn-primary w-full justify-center mt-2">
            Launch
            <x-icon name="external" class="w-4 h-4" />
        </a>
    </div>
    @endforeach
</div>
@endif
@endsection
