@extends('layouts.portal')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['Customers',      $stats['customers'],      'users',    '/admin/users'],
        ['Staff Members',  $stats['staff'],           'user',     '/admin/users'],
        ['Showcase Items', $stats['showcase_items'],  'computer', '/admin/showcase'],
        ['New Inquiries',  $stats['new_inquiries'],   'inbox',    '/staff/inquiries'],
    ] as [$label, $value, $icon, $link])
    <a href="{{ $link }}" class="card card-hover flex items-center gap-4 group no-underline">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[var(--color-primary-glow)] border border-[var(--color-primary)]/30 group-hover:shadow-[var(--color-primary-glow)] group-hover:shadow-md transition-shadow">
            <x-icon name="{{ $icon }}" class="w-5 h-5 text-[var(--color-primary)]" />
        </div>
        <div>
            <p class="text-2xl font-display font-bold text-[var(--color-text)]">{{ $value }}</p>
            <p class="text-xs text-[var(--color-muted)]">{{ $label }}</p>
        </div>
    </a>
    @endforeach
</div>

<div class="grid md:grid-cols-3 gap-4">
    @foreach([
        ['Users',         '/admin/users',    'Manage accounts and roles',    'user'],
        ['Showcase',      '/admin/showcase', 'Manage demos and access',      'computer'],
        ['Site Content',  '/admin/content',  'Edit public-facing copy',      'document'],
    ] as [$title, $link, $desc, $icon])
    <a href="{{ $link }}" class="card card-hover flex gap-4 items-start group no-underline">
        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[var(--color-surface-2)] border border-[var(--color-border)] shrink-0 group-hover:border-[var(--color-primary)]/40 transition-colors">
            <x-icon name="{{ $icon }}" class="w-5 h-5 text-[var(--color-muted)] group-hover:text-[var(--color-primary)] transition-colors" />
        </div>
        <div>
            <p class="font-semibold text-[var(--color-text)] mb-0.5">{{ $title }}</p>
            <p class="text-sm text-[var(--color-muted)]">{{ $desc }}</p>
        </div>
        <x-icon name="chevron-right" class="w-4 h-4 text-[var(--color-border)] ml-auto mt-0.5 group-hover:text-[var(--color-primary)] transition-colors" />
    </a>
    @endforeach
</div>
@endsection
