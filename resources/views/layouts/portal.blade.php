<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') — RapidInsight Designs</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="portalShell()">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
    <aside class="flex-shrink-0 flex flex-col border-r border-[var(--color-border)] bg-[var(--color-surface)] transition-all duration-300 ease-in-out"
           :class="sidebarOpen ? 'w-60' : 'w-16'">

        {{-- Logo --}}
        <div class="h-16 flex items-center px-4 border-b border-[var(--color-border)]">
            <a href="/" class="flex items-center gap-3 min-w-0">
                <img src="/images/logo.png" alt="" class="h-7 w-7 shrink-0">
                <span x-show="sidebarOpen" x-transition:enter="transition duration-150" x-transition:enter-start="opacity-0"
                      class="font-display font-semibold text-sm truncate text-[var(--color-text)]">
                    RapidInsight<span class="text-[var(--color-primary)]">.</span>
                </span>
            </a>
        </div>

        {{-- Role badge --}}
        <div x-show="sidebarOpen" class="px-4 pt-4 pb-2">
            <span class="badge {{ auth()->user()->isAdmin() ? 'badge-green' : (auth()->user()->isStaff() ? 'badge-blue' : 'badge-muted') }}">
                {{ strtoupper(auth()->user()->role) }}
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5">
            @if(auth()->user()->isCustomer())
                <a href="/showroom" class="sidebar-link {{ request()->is('showroom*') ? 'active' : '' }}">
                    <x-icon name="grid"  class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>ShowRoom</span>
                </a>
            @endif

            @if(auth()->user()->isStaffOrAdmin())
                <a href="{{ auth()->user()->isAdmin() ? '/admin/dashboard' : '/staff/dashboard' }}"
                   class="sidebar-link {{ request()->is(auth()->user()->isAdmin() ? 'admin/dashboard' : 'staff/dashboard') ? 'active' : '' }}">
                    <x-icon name="chart" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Dashboard</span>
                </a>
                <a href="/showroom" class="sidebar-link {{ request()->is('showroom*') ? 'active' : '' }}">
                    <x-icon name="grid"  class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>ShowRoom</span>
                </a>
                <a href="/staff/customers" class="sidebar-link {{ request()->is('staff/customers*') ? 'active' : '' }}">
                    <x-icon name="users" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Customers</span>
                </a>
                <a href="/staff/inquiries" class="sidebar-link {{ request()->is('staff/inquiries*') ? 'active' : '' }}">
                    <x-icon name="inbox" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Inquiries</span>
                </a>
            @endif

            @if(auth()->user()->isAdmin())
                <div class="pt-2 pb-1">
                    <p x-show="sidebarOpen" class="label px-2">Admin</p>
                </div>
                <a href="/admin/prospects" class="sidebar-link {{ request()->is('admin/prospects*') ? 'active' : '' }}">
                    <x-icon name="map-pin" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Prospects</span>
                </a>
                <a href="/admin/users" class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <x-icon name="cog"  class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Users</span>
                </a>
                <a href="/admin/showcase" class="sidebar-link {{ request()->is('admin/showcase*') ? 'active' : '' }}">
                    <x-icon name="computer" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Showcase</span>
                </a>
                <a href="/admin/content" class="sidebar-link {{ request()->is('admin/content*') ? 'active' : '' }}">
                    <x-icon name="document" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Site Content</span>
                </a>
            @endif
        </nav>

        {{-- Bottom: collapse toggle --}}
        <div class="border-t border-[var(--color-border)] p-2 space-y-1">
            <button @click="toggleSidebar()"
                    class="sidebar-link w-full" title="Toggle sidebar">
                <span :style="sidebarOpen ? '' : 'transform: rotate(180deg)'"
                      class="inline-flex transition-transform duration-300">
                    <x-icon name="chevron-left" class="w-5 h-5 shrink-0" />
                </span>
                <span x-show="sidebarOpen" x-transition>Collapse</span>
            </button>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="sidebar-link w-full text-left">
                    <x-icon name="logout" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main area ───────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Top bar --}}
        <header class="h-16 flex items-center justify-between px-6 border-b border-[var(--color-border)] bg-[var(--color-surface)] shrink-0">
            <div>
                <h1 class="font-display font-semibold text-[var(--color-text)]">@yield('page-title', 'Portal')</h1>
                @hasSection('breadcrumb')
                <p class="text-xs text-[var(--color-muted)]">@yield('breadcrumb')</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-[var(--color-text)]">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[var(--color-muted)]">{{ auth()->user()->email }}</p>
                </div>
                <div class="h-9 w-9 rounded-full bg-[var(--color-primary-glow)] border border-[var(--color-primary)] flex items-center justify-center">
                    <span class="text-sm font-semibold text-[var(--color-primary)]">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
            </div>
        </header>

        {{-- Flash --}}
        @if(session('success') || session('error'))
        <div class="mx-6 mt-4" x-data="flash()">
            <div x-show="show" x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-end="opacity-0"
                 class="flex items-center gap-3 p-3 rounded-lg text-sm border
                         {{ session('success') ? 'bg-green-500/10 border-green-500/30 text-green-400' : 'bg-red-500/10 border-red-500/30 text-red-400' }}">
                <x-icon name="{{ session('error') ? 'warning' : 'check' }}" class="w-4 h-4 shrink-0" />
                {{ session('success') ?? session('error') }}
                <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100"><x-icon name="x" class="w-4 h-4" /></button>
            </div>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
