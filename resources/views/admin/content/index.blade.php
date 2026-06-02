@extends('layouts.portal')
@section('title', 'Site Content')
@section('page-title', 'Site Content')
@section('breadcrumb', 'Edit public-facing page copy')

@section('content')
<div class="max-w-2xl">
    <div class="card">
        <form method="POST" action="/admin/content">
            @csrf
            <div class="space-y-6">
                @php
                $fields = [
                    ['hero_headline',    'Hero Headline',          'Smart Design, Optimized Workflows built for Efficiency.', 'text'],
                    ['hero_subheadline', 'Hero Sub-headline',      'We craft high-performance web applications...', 'textarea'],
                    ['about_text',       'About Paragraph',        'RapidInsight Designs specializes in...', 'textarea'],
                    ['contact_intro',    'Contact Page Intro',     'Have a project in mind? We\'d love to hear about it.', 'textarea'],
                ];
                @endphp

                @foreach($fields as [$key, $label, $placeholder, $type])
                <div>
                    <label class="label">{{ $label }}</label>
                    @if($type === 'textarea')
                    <textarea name="content[{{ $key }}]" rows="3"
                              class="input resize-y">{{ $contents[$key]->value ?? $placeholder }}</textarea>
                    @else
                    <input type="text" name="content[{{ $key }}]"
                           value="{{ $contents[$key]->value ?? $placeholder }}"
                           class="input">
                    @endif
                    <p class="text-xs text-[var(--color-muted)] mt-1">Key: <code class="text-[var(--color-primary)]">{{ $key }}</code></p>
                </div>
                @endforeach

                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="w-4 h-4" />
                    Save All Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
