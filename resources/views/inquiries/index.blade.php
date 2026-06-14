@extends('layouts.portal')
@section('title', 'Inquiries')
@section('page-title', 'Inquiries')
@section('breadcrumb', 'Start a conversation with our team')

@php
    $activeCount   = $inquiries->where('status', '!=', 'resolved')->count();
    $inactiveCount = $inquiries->count() - $activeCount;
@endphp

@section('content')
<div class="grid lg:grid-cols-2 gap-6">

    {{-- New inquiry --}}
    <form method="POST" action="{{ route('inquiries.store') }}" class="card space-y-4 h-fit">
        @csrf
        <div>
            <h3 class="font-semibold text-text">Ready to start a project?</h3>
            <p class="text-xs text-muted mt-0.5">Tell us what you have in mind and we'll get right back to you.</p>
        </div>

        @if($errors->any())
        <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <div>
            <label class="label">Subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" class="input" placeholder="New website, redesign, feature…" required>
        </div>
        <div>
            <label class="label">Message</label>
            <textarea name="message" rows="6" class="input resize-y" placeholder="Describe your project, goals, timeline, or questions…" required>{{ old('message') }}</textarea>
        </div>
        <button class="btn-primary gap-1.5"><x-icon name="chat" class="w-4 h-4" /> Send Inquiry</button>
    </form>

    {{-- Track inquiries --}}
    <div class="card p-0" x-data="{ filter: 'active' }">
        <div class="p-4 border-b border-border flex items-center justify-between gap-3 flex-wrap">
            <h3 class="font-semibold text-text">Your Inquiries</h3>
            @if($inquiries->isNotEmpty())
            <div class="inline-flex rounded-full border border-border bg-surface-2 p-0.5 text-xs shrink-0">
                <button type="button" @click="filter='active'" :class="filter==='active' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Active ({{ $activeCount }})</button>
                <button type="button" @click="filter='inactive'" :class="filter==='inactive' ? 'bg-primary text-bg font-semibold' : 'text-muted'" class="px-3 py-1 rounded-full">Inactive ({{ $inactiveCount }})</button>
            </div>
            @endif
        </div>
        @if($inquiries->isEmpty())
        <div class="p-8 text-center">
            <x-icon name="chat" class="w-10 h-10 text-border mx-auto mb-3" />
            <p class="text-sm text-muted">You haven't sent any inquiries yet.</p>
        </div>
        @else
        <div class="divide-y divide-border">
            @foreach($inquiries as $inquiry)
            @php $active = $inquiry->status !== 'resolved'; @endphp
            <a href="{{ route('inquiries.show', $inquiry) }}" x-cloak x-show="filter === '{{ $active ? 'active' : 'inactive' }}'"
               class="block p-4 hover:bg-surface-2 transition-colors">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-sm font-medium text-text">{{ $inquiry->subject }}</p>
                    <span class="badge {{ $inquiry->statusBadgeClass() }} shrink-0">{{ $inquiry->statusLabel() }}</span>
                </div>
                <p class="text-sm text-muted mt-1 line-clamp-2">{{ $inquiry->message }}</p>
                <p class="text-xs text-primary mt-1.5">View conversation →</p>
            </a>
            @endforeach
            @if($activeCount === 0)<p x-cloak x-show="filter==='active'" class="p-6 text-center text-sm text-muted">No active inquiries.</p>@endif
            @if($inactiveCount === 0)<p x-cloak x-show="filter==='inactive'" class="p-6 text-center text-sm text-muted">No resolved inquiries.</p>@endif
        </div>
        @endif
    </div>
</div>
@endsection
