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
    {{-- Cursive fonts for typed-signature samples on agreements --}}
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Great+Vibes&family=Pacifico&family=Satisfy&display=swap" rel="stylesheet">
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
                <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <x-icon name="chart" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Dashboard</span>
                </a>
                <a href="/agreements" class="sidebar-link relative {{ request()->is('agreements*') ? 'active' : '' }}">
                    <x-icon name="document" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Agreements</span>
                    @if($agreementActionCount > 0)
                        <span x-show="sidebarOpen" x-transition
                              class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $agreementActionCount }}</span>
                        <span x-show="!sidebarOpen"
                              class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $agreementActionCount }}</span>
                    @endif
                </a>
                <a href="/work-orders" class="sidebar-link relative {{ request()->is('work-orders*') ? 'active' : '' }}">
                    <x-icon name="bolt" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Work Orders</span>
                    @if($customerWoActionCount > 0)
                        <span x-show="sidebarOpen" x-transition class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $customerWoActionCount }}</span>
                        <span x-show="!sidebarOpen" class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $customerWoActionCount }}</span>
                    @endif
                </a>
                <a href="/billing" class="sidebar-link relative {{ request()->is('billing*') ? 'active' : '' }}">
                    <x-icon name="inbox" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Billing</span>
                    @if($customerBillingDueCount > 0)
                        <span x-show="sidebarOpen" x-transition class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-amber-500 text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $customerBillingDueCount }}</span>
                        <span x-show="!sidebarOpen" class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-amber-500 text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $customerBillingDueCount }}</span>
                    @endif
                </a>
                <a href="/showroom" class="sidebar-link {{ request()->is('showroom*') ? 'active' : '' }}">
                    <x-icon name="grid"  class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>ShowRoom</span>
                </a>
                <a href="/inquiries" class="sidebar-link {{ request()->is('inquiries*') ? 'active' : '' }}">
                    <x-icon name="chat" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Inquiries</span>
                </a>
                <a href="/profile" class="sidebar-link {{ request()->is('profile*') ? 'active' : '' }}">
                    <x-icon name="user" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>My Account</span>
                </a>
            @endif

            @if(auth()->user()->isStaffOrAdmin())
                <a href="{{ auth()->user()->isAdmin() ? '/admin/dashboard' : '/staff/dashboard' }}"
                   class="sidebar-link {{ request()->is(auth()->user()->isAdmin() ? 'admin/dashboard' : 'staff/dashboard') ? 'active' : '' }}">
                    <x-icon name="chart" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Dashboard</span>
                </a>
                <a href="/staff/work-orders" class="sidebar-link relative {{ request()->is('staff/work-orders*') ? 'active' : '' }}">
                    <x-icon name="bolt" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Work Orders</span>
                    @if($unreadMessagesCount > 0)
                        <span x-show="sidebarOpen" x-transition
                              class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $unreadMessagesCount }}</span>
                        <span x-show="!sidebarOpen"
                              class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $unreadMessagesCount }}</span>
                    @endif
                </a>
                <a href="/staff/agreements" class="sidebar-link relative {{ request()->is('staff/agreements*') ? 'active' : '' }}">
                    <x-icon name="document" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Agreements</span>
                    @if($pendingValidationCount > 0)
                        <span x-show="sidebarOpen" x-transition
                              class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $pendingValidationCount }}</span>
                        <span x-show="!sidebarOpen"
                              class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $pendingValidationCount }}</span>
                    @endif
                </a>
                <a href="/staff/inquiries" class="sidebar-link relative {{ request()->is('staff/inquiries*') ? 'active' : '' }}">
                    <x-icon name="inbox" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Inquiries</span>
                    @if($newInquiriesCount > 0)
                        {{-- expanded: count pill after the label --}}
                        <span x-show="sidebarOpen" x-transition
                              class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $newInquiriesCount }}</span>
                        {{-- collapsed: badge on the icon corner --}}
                        <span x-show="!sidebarOpen"
                              class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $newInquiriesCount }}</span>
                    @endif
                </a>
                <a href="/staff/invoices" class="sidebar-link {{ request()->is('staff/invoices*') ? 'active' : '' }}">
                    <x-icon name="document" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Invoices</span>
                </a>
                <a href="/showroom" class="sidebar-link {{ request()->is('showroom*') ? 'active' : '' }}">
                    <x-icon name="grid"  class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>ShowRoom</span>
                </a>
                {{-- Admin sees Prospects here; staff (no Admin section) keep Customers here --}}
                @if(auth()->user()->isAdmin())
                <a href="/admin/prospects" class="sidebar-link {{ request()->is('admin/prospects*') ? 'active' : '' }}">
                    <x-icon name="map-pin" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Prospects</span>
                </a>
                @else
                <a href="/staff/customers" class="sidebar-link {{ request()->is('staff/customers*') ? 'active' : '' }}">
                    <x-icon name="users" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Customers</span>
                </a>
                @endif
            @endif

            @if(auth()->user()->isAdmin())
                <div class="pt-2 pb-1">
                    <p x-show="sidebarOpen" class="label px-2">Admin</p>
                </div>
                <a href="/staff/customers" class="sidebar-link {{ request()->is('staff/customers*') ? 'active' : '' }}">
                    <x-icon name="users" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Customers</span>
                </a>
                <a href="/admin/users" class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <x-icon name="cog"  class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Users</span>
                </a>
                <a href="/admin/showcase" class="sidebar-link relative {{ request()->is('admin/showcase*') ? 'active' : '' }}">
                    <x-icon name="computer" class="w-5 h-5 shrink-0" />
                    <span x-show="sidebarOpen" x-transition>Showcase</span>
                    @if($pendingAccessCount > 0)
                        <span x-show="sidebarOpen" x-transition
                              class="ml-auto min-w-5 h-5 px-1.5 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[11px] font-bold flex items-center justify-center">{{ $pendingAccessCount }}</span>
                        <span x-show="!sidebarOpen"
                              class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 rounded-full bg-[var(--color-primary)] text-[var(--color-bg)] text-[10px] font-bold flex items-center justify-center">{{ $pendingAccessCount }}</span>
                    @endif
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
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
                <button type="button" @click="open = !open" @click.outside="open = false"
                        aria-haspopup="true" :aria-expanded="open"
                        class="flex items-center gap-3 rounded-full py-1 pl-3 pr-1.5 transition-colors hover:bg-[var(--color-surface-2)]"
                        :class="open ? 'bg-[var(--color-surface-2)]' : ''">
                    <div class="text-right hidden sm:block leading-tight">
                        <p class="text-sm font-medium text-[var(--color-text)]">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[var(--color-muted)]">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-[var(--color-primary-glow)] border border-[var(--color-primary)] flex items-center justify-center shrink-0">
                        <span class="text-sm font-semibold text-[var(--color-primary)]">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <span class="transition-transform duration-200" :class="open ? 'rotate-180' : ''">
                        <x-icon name="chevron-down" class="w-4 h-4 text-[var(--color-muted)]" />
                    </span>
                </button>

                {{-- Account menu --}}
                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-52 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] shadow-xl shadow-black/40 overflow-hidden z-50 py-1"
                     style="transform-origin: top right;">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-[var(--color-text)] hover:bg-[var(--color-surface-2)] transition-colors">
                        <x-icon name="user" class="w-4 h-4 text-[var(--color-muted)]" /> My Account
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-left text-[var(--color-text)] hover:bg-[var(--color-surface-2)] transition-colors">
                            <x-icon name="logout" class="w-4 h-4 text-[var(--color-muted)]" /> Log out
                        </button>
                    </form>
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
