@extends('layouts.portal')
@section('title', 'ShowRoom')
@section('page-title', 'ShowRoom')
@section('breadcrumb', 'Explore our live demos and request access')

@section('content')

@if($items->isEmpty())
<div class="flex flex-col items-center justify-center h-64 text-center">
    <x-icon name="grid" class="w-12 h-12 text-[var(--color-border)] mb-4" />
    <p class="font-semibold text-[var(--color-text)] mb-1">No demos available yet</p>
    <p class="text-sm text-[var(--color-muted)]">Check back soon — new demos are on the way.</p>
</div>
@else
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($items as $item)
    @php $status = $access[$item->id] ?? null; @endphp
    <div class="card card-hover group flex flex-col relative"
         @if($status === 'approved') style="border-color: var(--color-primary); box-shadow: 0 0 18px var(--color-primary-glow);" @endif>
        @if($status === 'approved')
        <span class="absolute top-3 right-3 z-10 w-7 h-7 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] flex items-center justify-center shadow-lg" title="You have access">
            <x-icon name="check" class="w-4 h-4" />
        </span>
        @endif
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
                @if($status === 'approved')
                <span class="badge badge-green shrink-0">Access granted</span>
                @elseif($status === 'pending')
                <span class="badge badge-amber shrink-0">Pending</span>
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

            {{-- Login details (revealed only once access is approved) --}}
            @if($status === 'approved' && $item->hasDemoLogin())
            <div class="mt-1 mb-3 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface-2)] p-3 text-xs space-y-1">
                <p class="label mb-1">Login details</p>
                @if($item->demo_username)
                <p><span class="text-[var(--color-muted)]">Username:</span> <span class="text-[var(--color-text)] font-mono select-all">{{ $item->demo_username }}</span></p>
                @endif
                @if($item->demo_password)
                <p><span class="text-[var(--color-muted)]">Password:</span> <span class="text-[var(--color-text)] font-mono select-all">{{ $item->demo_password }}</span></p>
                @endif
                @if($item->access_notes)
                <p class="text-[var(--color-muted)] leading-snug pt-1 whitespace-pre-line">{{ $item->access_notes }}</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="mt-2">
            @if($status === 'approved')
                @if($item->launchUrl())
                <a href="{{ $item->launchUrl() }}" target="_blank" rel="noopener" class="btn-primary w-full justify-center">
                    Launch <x-icon name="external" class="w-4 h-4" />
                </a>
                @else
                <a href="/showroom/{{ $item->id }}" class="btn-primary w-full justify-center">
                    Launch <x-icon name="external" class="w-4 h-4" />
                </a>
                @endif
            @elseif($status === 'pending')
                <button type="button" disabled
                        class="btn-ghost w-full justify-center opacity-60 cursor-not-allowed">
                    <x-icon name="lock" class="w-4 h-4" /> Awaiting approval
                </button>
            @else
                <form method="POST" action="{{ route('showroom.request', $item) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center">
                        <x-icon name="lock" class="w-4 h-4" /> Request Access
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
