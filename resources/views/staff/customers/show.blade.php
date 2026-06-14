@extends('layouts.portal')
@section('title', $user->name)
@section('page-title', $user->name)
@section('breadcrumb', 'Customer Management')

@section('content')
<div class="space-y-6" x-data="{ editOpen: false, pwOpen: false }">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="card flex flex-col md:flex-row md:items-center gap-4">
        <div class="w-16 h-16 shrink-0 rounded-full bg-[var(--color-primary-glow)] border border-primary flex items-center justify-center text-2xl font-display font-bold text-primary">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="font-display font-semibold text-lg text-text">{{ $user->name }}</h2>
                <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">{{ $user->is_active ? 'Active login' : 'Deactivated' }}</span>
            </div>
            <p class="text-sm text-muted">{{ $user->email }}</p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-xs text-muted">
                @if($user->company)<span class="inline-flex items-center gap-1"><x-icon name="users" class="w-3 h-3" />{{ $user->company }}</span>@endif
                @if($user->phone)<span class="inline-flex items-center gap-1"><x-icon name="phone" class="w-3 h-3" />{{ $user->phone }}</span>@endif
                <span>Joined {{ $user->created_at->format('M j, Y') }}</span>
                <span>Last login {{ $user->last_login_at?->diffForHumans() ?? 'never' }}</span>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <button @click="editOpen = true" class="btn-ghost btn-sm gap-1.5"><x-icon name="pencil" class="w-3.5 h-3.5" />Edit</button>
            <button @click="pwOpen = true" class="btn-ghost btn-sm gap-1.5"><x-icon name="lock" class="w-3.5 h-3.5" />Reset Password</button>
            <form method="POST" action="{{ route('staff.customers.toggle', $user) }}"
                  x-data="confirmDelete('{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} this login?')">
                @csrf @method('PATCH')
                <button @click.prevent="confirm($el.closest('form'))"
                        class="btn-sm gap-1.5 {{ $user->is_active ? 'btn-danger' : 'btn-primary' }}">
                    <x-icon name="{{ $user->is_active ? 'lock' : 'check' }}" class="w-3.5 h-3.5" />
                    {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                </button>
            </form>
        </div>
    </div>

    {{-- ── Stat tiles ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <p class="label">Billed</p>
            <p class="text-2xl font-display font-bold text-text">${{ number_format($billed, 2) }}</p>
        </div>
        <div class="card">
            <p class="label">Paid</p>
            <p class="text-2xl font-display font-bold text-primary">${{ number_format($paid, 2) }}</p>
        </div>
        <div class="card">
            <p class="label">Outstanding</p>
            <p class="text-2xl font-display font-bold {{ $outstanding > 0 ? 'text-amber-400' : 'text-text' }}">${{ number_format($outstanding, 2) }}</p>
        </div>
        <div class="card">
            <p class="label">Activity</p>
            <p class="text-sm text-text mt-1">{{ $user->files->count() }} files · {{ $user->customerNotes->count() }} notes</p>
            <p class="text-sm text-muted">{{ $user->showroomItems->count() }} demos · {{ $user->inquiries->count() }} inquiries</p>
        </div>
    </div>

    {{-- ── Tabs ───────────────────────────────────────────────────────────── --}}
    <div x-data="tabs('overview')">
        <div class="flex gap-1 border-b border-border overflow-x-auto">
            @php $tabs = ['overview' => 'Overview', 'agreements' => 'Agreements', 'work_orders' => 'Work Orders', 'notes' => 'Notes', 'files' => 'Files', 'invoices' => 'Invoices', 'access' => 'Access & Inquiries']; @endphp
            @foreach($tabs as $key => $label)
            <button @click="setTab('{{ $key }}')"
                    :class="isActive('{{ $key }}') ? 'text-primary border-b-2 border-primary' : 'text-muted hover:text-text'"
                    class="px-4 py-2 text-sm font-medium transition-colors -mb-px whitespace-nowrap">{{ $label }}</button>
            @endforeach
        </div>

        <div class="pt-6">

            {{-- ── Overview ──────────────────────────────────────────────── --}}
            <div x-show="isActive('overview')" class="grid lg:grid-cols-2 gap-6">
                <div class="card space-y-3">
                    <h3 class="font-semibold text-text">Contact Details</h3>
                    <dl class="text-sm divide-y divide-border">
                        <div class="flex justify-between py-2"><dt class="text-muted">Name</dt><dd class="text-text">{{ $user->name }}</dd></div>
                        <div class="flex justify-between py-2"><dt class="text-muted">Email</dt><dd class="text-text">{{ $user->email }}</dd></div>
                        <div class="flex justify-between py-2"><dt class="text-muted">Company</dt><dd class="text-text">{{ $user->company ?? '—' }}</dd></div>
                        <div class="flex justify-between py-2"><dt class="text-muted">Phone</dt><dd class="text-text">{{ $user->phone ?? '—' }}</dd></div>
                        @if($user->website)<div class="flex justify-between py-2 gap-4"><dt class="text-muted shrink-0">Website</dt><dd class="text-text truncate">{{ $user->website }}</dd></div>@endif
                        @if($user->billing_email)<div class="flex justify-between py-2"><dt class="text-muted">Billing email</dt><dd class="text-text">{{ $user->billing_email }}</dd></div>@endif
                        @if($user->fullAddress())<div class="flex justify-between py-2 gap-4"><dt class="text-muted shrink-0">Address</dt><dd class="text-text text-right">{{ $user->fullAddress() }}</dd></div>@endif
                        <div class="flex justify-between py-2"><dt class="text-muted">Login status</dt><dd class="text-text">{{ $user->is_active ? 'Active' : 'Deactivated' }}</dd></div>
                    </dl>
                </div>
                <div class="card space-y-3">
                    <h3 class="font-semibold text-text">Recent Notes</h3>
                    @forelse($user->customerNotes->take(3) as $note)
                    <div class="text-sm border-l-2 border-primary/40 pl-3">
                        <p class="text-text">{{ Str::limit($note->body, 140) }}</p>
                        <p class="text-xs text-muted mt-0.5">{{ $note->author?->name ?? 'System' }} · {{ $note->created_at->diffForHumans() }}</p>
                    </div>
                    @empty
                    <p class="text-sm text-muted">No notes yet — add one in the Notes tab.</p>
                    @endforelse
                </div>
            </div>

            {{-- ── Agreements ────────────────────────────────────────────── --}}
            <div x-show="isActive('agreements')" class="space-y-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted">{{ $user->agreements->count() }} {{ $user->agreements->count() === 1 ? 'agreement' : 'agreements' }}</p>
                    <form method="POST" action="{{ route('staff.customers.agreements.store', $user) }}">
                        @csrf
                        <button type="submit" class="btn-primary btn-sm gap-1.5"><x-icon name="plus" class="w-3.5 h-3.5" />New Agreement</button>
                    </form>
                </div>

                @if($user->agreements->isEmpty())
                <p class="text-sm text-muted">No agreements yet. Create one to send a statement of work for signature and payment.</p>
                @else
                <div class="card p-0 overflow-hidden">
                    <table class="data-table">
                        <thead><tr><th>Title</th><th>Status</th><th>Total</th><th>Paid</th><th>Balance</th><th>Created</th><th></th></tr></thead>
                        <tbody>
                            @foreach($user->agreements as $agreement)
                            <tr>
                                <td class="text-text font-medium">{{ $agreement->title }}</td>
                                <td>
                                    <span class="badge {{ $agreement->statusBadgeClass() }}">{{ $agreement->statusLabel() }}</span>
                                    @if($agreement->actionNeededForAdmin())<span class="badge badge-amber text-[10px]">validate</span>@endif
                                </td>
                                <td class="text-text">{{ $agreement->has_cost ? '$'.number_format($agreement->total_amount, 2) : '—' }}</td>
                                <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->amountPaid(), 2) : '—' }}</td>
                                <td class="text-muted">{{ $agreement->has_cost ? '$'.number_format($agreement->balance(), 2) : '—' }}</td>
                                <td class="text-muted text-xs">{{ $agreement->created_at->format('M j, Y') }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('staff.customers.agreements.edit', [$user, $agreement]) }}" class="btn-ghost btn-sm" title="Open / edit"><x-icon name="pencil" class="w-3.5 h-3.5" /></a>
                                    <a href="{{ route('staff.customers.agreements.pdf', [$user, $agreement]) }}" target="_blank" class="btn-ghost btn-sm" title="PDF"><x-icon name="document" class="w-3.5 h-3.5" /></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- ── Work Orders ───────────────────────────────────────────── --}}
            <div x-show="isActive('work_orders')" class="space-y-4">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <p class="text-sm text-muted">{{ $user->workOrders->count() }} {{ $user->workOrders->count() === 1 ? 'work order' : 'work orders' }}</p>
                    <form method="POST" action="{{ route('staff.customers.work-orders.store', $user) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="title" class="input py-1.5 text-sm w-48" placeholder="New work order title" required>
                        <button class="btn-primary btn-sm gap-1.5 whitespace-nowrap"><x-icon name="plus" class="w-3.5 h-3.5" />Create</button>
                    </form>
                </div>
                @if($user->workOrders->isEmpty())
                <p class="text-sm text-muted">No work orders yet. Create one here, or convert an agreement into a work order.</p>
                @else
                <div class="card p-0 overflow-hidden">
                    <table class="data-table">
                        <thead><tr><th>Title</th><th>Status</th><th>Created</th><th></th></tr></thead>
                        <tbody>
                            @foreach($user->workOrders as $wo)
                            <tr>
                                <td class="text-text font-medium">{{ $wo->title }}</td>
                                <td><span class="badge {{ $wo->statusBadgeClass() }}">{{ $wo->statusLabel() }}</span></td>
                                <td class="text-muted text-xs">{{ $wo->created_at->format('M j, Y') }}</td>
                                <td class="text-right"><a href="{{ route('staff.work-orders.edit', $wo) }}" class="btn-ghost btn-sm">Open</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- ── Notes ─────────────────────────────────────────────────── --}}
            <div x-show="isActive('notes')" class="space-y-4">
                <form method="POST" action="{{ route('staff.customers.notes.store', $user) }}" class="card space-y-3">
                    @csrf
                    <label class="label">Add a note</label>
                    <textarea name="body" rows="3" required class="input resize-none" placeholder="Call summary, requirements, internal reminder…"></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-muted cursor-pointer">
                            <input type="checkbox" name="pinned" value="1" class="rounded"> Pin to top
                        </label>
                        <button type="submit" class="btn-primary btn-sm">Save Note</button>
                    </div>
                </form>

                @forelse($user->customerNotes->sortByDesc('pinned') as $note)
                <div class="card flex items-start justify-between gap-3 {{ $note->pinned ? 'border-primary/40' : '' }}">
                    <div class="min-w-0">
                        @if($note->pinned)<span class="badge badge-green text-[10px] mb-1">Pinned</span>@endif
                        <p class="text-sm text-text whitespace-pre-line">{{ $note->body }}</p>
                        <p class="text-xs text-muted mt-1">{{ $note->author?->name ?? 'System' }} · {{ $note->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <form method="POST" action="{{ route('staff.customers.notes.destroy', [$user, $note]) }}" x-data="confirmDelete('Delete this note?')">
                        @csrf @method('DELETE')
                        <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-red-400 shrink-0"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-muted">No notes recorded for this customer.</p>
                @endforelse
            </div>

            {{-- ── Files ─────────────────────────────────────────────────── --}}
            <div x-show="isActive('files')" class="space-y-4">
                <form method="POST" action="{{ route('staff.customers.files.store', $user) }}" enctype="multipart/form-data" class="card space-y-3">
                    @csrf
                    <label class="label">Upload a file <span class="text-muted font-normal">(contracts, designs, assets — max 20 MB)</span></label>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <input type="file" name="file" required
                               class="block w-full text-sm text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-primary/20 file:text-primary hover:file:bg-primary/30">
                        <input type="text" name="label" class="input" placeholder="Optional label (e.g. Signed contract)">
                    </div>
                    <div class="flex justify-end"><button type="submit" class="btn-primary btn-sm">Upload</button></div>
                </form>

                @if($user->files->isEmpty())
                <p class="text-sm text-muted">No files uploaded for this customer.</p>
                @else
                <div class="card p-0 overflow-hidden">
                    <table class="data-table">
                        <thead><tr><th>File</th><th>Label</th><th>Size</th><th>Uploaded</th><th></th></tr></thead>
                        <tbody>
                            @foreach($user->files as $file)
                            <tr>
                                <td class="text-text">
                                    <span class="inline-flex items-center gap-2"><x-icon name="document" class="w-4 h-4 text-primary" />{{ $file->name }}</span>
                                </td>
                                <td class="text-muted">{{ $file->label ?? '—' }}</td>
                                <td class="text-muted text-xs">{{ $file->humanSize() }}</td>
                                <td class="text-muted text-xs">{{ $file->created_at->format('M j, Y') }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('staff.customers.files.download', [$user, $file]) }}" class="btn-ghost btn-sm gap-1"><x-icon name="download" class="w-3.5 h-3.5" /></a>
                                    <form method="POST" action="{{ route('staff.customers.files.destroy', [$user, $file]) }}" class="inline" x-data="confirmDelete('Delete {{ addslashes($file->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-red-400"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- ── Invoices ──────────────────────────────────────────────── --}}
            <div x-show="isActive('invoices')" class="space-y-4" x-data="{ addOpen: false }">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted">
                        {{ $user->invoices->count() }} {{ $user->invoices->count() === 1 ? 'invoice' : 'invoices' }} ·
                        <span class="text-amber-400">${{ number_format($outstanding, 2) }} outstanding</span>
                    </p>
                    <button @click="addOpen = true" class="btn-primary btn-sm gap-1.5"><x-icon name="plus" class="w-3.5 h-3.5" />New Invoice</button>
                </div>

                @if($user->invoices->isEmpty())
                <p class="text-sm text-muted">No invoices yet.</p>
                @else
                <div class="card p-0 overflow-hidden">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>Description</th><th>Amount</th><th>Status</th><th>Issued</th><th>Due</th><th></th></tr></thead>
                        <tbody>
                            @foreach($user->invoices as $invoice)
                            <tr>
                                <td class="text-text font-medium">{{ $invoice->number }}</td>
                                <td class="text-muted">{{ $invoice->description ?? '—' }}</td>
                                <td class="text-text">${{ number_format($invoice->amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $invoice->statusBadgeClass() }}">{{ $invoice->status }}</span>
                                    @if($invoice->visible_to_customer)
                                    <span class="badge badge-blue text-[10px]" title="Visible to customer"><x-icon name="eye" class="w-3 h-3 inline" /> shared</span>
                                    @endif
                                </td>
                                <td class="text-muted text-xs">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="text-xs {{ $invoice->isOverdue() ? 'text-red-400' : 'text-muted' }}">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('staff.customers.invoices.pdf', [$user, $invoice]) }}" target="_blank" class="btn-ghost btn-sm gap-1" title="Generate / view PDF"><x-icon name="document" class="w-3.5 h-3.5" /></a>
                                    @if($invoice->file_path)
                                    <a href="{{ Storage::url($invoice->file_path) }}" target="_blank" class="btn-ghost btn-sm gap-1" title="View attached file"><x-icon name="download" class="w-3.5 h-3.5" /></a>
                                    @endif
                                    <a href="{{ route('staff.customers.invoices.edit', [$user, $invoice]) }}" class="btn-ghost btn-sm" title="Open invoice editor"><x-icon name="pencil" class="w-3.5 h-3.5" /></a>
                                    <form method="POST" action="{{ route('staff.customers.invoices.destroy', [$user, $invoice]) }}" class="inline" x-data="confirmDelete('Delete invoice {{ addslashes($invoice->number) }}?')">
                                        @csrf @method('DELETE')
                                        <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-red-400"><x-icon name="trash" class="w-3.5 h-3.5" /></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Add invoice modal --}}
                <div x-show="addOpen" x-cloak @keydown.escape.window="addOpen = false" @click.self="addOpen = false"
                     class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
                     style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
                    <div class="card w-full max-w-lg my-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-text">New Invoice</h3>
                            <button @click="addOpen = false" class="btn-ghost btn-sm"><x-icon name="x" class="w-4 h-4" /></button>
                        </div>
                        <form method="POST" action="{{ route('staff.customers.invoices.store', $user) }}" enctype="multipart/form-data">
                            @csrf
                            @include('staff.customers._invoice_fields', ['invoice' => null])
                            <div class="flex gap-2 mt-4"><button class="btn-primary btn-sm">Create Invoice</button><button type="button" @click="addOpen = false" class="btn-ghost btn-sm">Cancel</button></div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Access & Inquiries ────────────────────────────────────── --}}
            <div x-show="isActive('access')" class="grid lg:grid-cols-2 gap-6">
                <div class="card p-0">
                    <div class="p-4 border-b border-border"><h3 class="font-semibold text-text">ShowRoom Access</h3></div>
                    @if($user->showroomItems->isEmpty())
                    <p class="p-4 text-sm text-muted">No demo access granted. Grant access from the <a href="{{ route('admin.showcase.index') }}" class="text-primary hover:underline">Showcase</a> admin.</p>
                    @else
                    <div class="divide-y divide-border">
                        @foreach($user->showroomItems as $item)
                        @php $pending = ($item->pivot->status ?? 'approved') === 'pending'; @endphp
                        <div class="p-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-text truncate">{{ $item->title }}</p>
                                <p class="text-xs text-muted">
                                    {{ $pending
                                        ? 'Requested ' . ($item->pivot->requested_at?->diffForHumans() ?? '')
                                        : 'Granted ' . ($item->pivot->granted_at?->diffForHumans() ?? '') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="badge {{ $pending ? 'badge-amber' : 'badge-green' }}">{{ $pending ? 'Pending' : 'Approved' }}</span>
                                <form method="POST" action="{{ route('staff.customers.access.revoke', [$user, $item]) }}"
                                      x-data="confirmDelete('{{ $pending ? 'Deny' : 'Remove' }} {{ addslashes($user->name) }}\'s access to {{ addslashes($item->title) }}?')">
                                    @csrf @method('DELETE')
                                    <button @click.prevent="confirm($el.closest('form'))" class="btn-ghost btn-sm text-[var(--color-danger)]" title="Remove access">
                                        <x-icon name="trash" class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="card p-0">
                    <div class="p-4 border-b border-border"><h3 class="font-semibold text-text">Inquiries</h3></div>
                    @if($user->inquiries->isEmpty())
                    <p class="p-4 text-sm text-muted">No inquiries from this customer.</p>
                    @else
                    <table class="data-table">
                        <thead><tr><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead>
                        <tbody>
                            @foreach($user->inquiries as $inquiry)
                            <tr>
                                <td class="text-text">{{ Str::limit($inquiry->subject, 40) }}</td>
                                <td><span class="badge {{ $inquiry->statusBadgeClass() }}">{{ $inquiry->status }}</span></td>
                                <td class="text-muted text-xs">{{ $inquiry->created_at->format('M j, Y') }}</td>
                                <td><a href="/staff/inquiries/{{ $inquiry->id }}" class="text-sm text-primary hover:underline">View</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- ── Edit profile modal ─────────────────────────────────────────────── --}}
    <div x-show="editOpen" x-cloak @keydown.escape.window="editOpen = false" @click.self="editOpen = false"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
         style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
        <div class="card w-full max-w-2xl my-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-text">Edit Customer</h3>
                <button @click="editOpen = false" class="btn-ghost btn-sm"><x-icon name="x" class="w-4 h-4" /></button>
            </div>
            <form method="POST" action="{{ route('staff.customers.update', $user) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div class="grid sm:grid-cols-2 gap-3">
                    <div><label class="label">Name</label><input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required></div>
                    <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required></div>
                    <div><label class="label">Company</label><input type="text" name="company" value="{{ old('company', $user->company) }}" class="input"></div>
                    <div><label class="label">Phone</label><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="input"></div>
                    <div><label class="label">Website</label><input type="text" name="website" value="{{ old('website', $user->website) }}" class="input"></div>
                    <div><label class="label">Billing email</label><input type="email" name="billing_email" value="{{ old('billing_email', $user->billing_email) }}" class="input"></div>
                </div>
                <p class="label">Mailing address</p>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2"><input type="text" name="address_line1" value="{{ old('address_line1', $user->address_line1) }}" class="input" placeholder="Street address"></div>
                    <div class="sm:col-span-2"><input type="text" name="address_line2" value="{{ old('address_line2', $user->address_line2) }}" class="input" placeholder="Apt, suite (optional)"></div>
                    <div><input type="text" name="city" value="{{ old('city', $user->city) }}" class="input" placeholder="City"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" name="state" value="{{ old('state', $user->state) }}" class="input" placeholder="State">
                        <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="input" placeholder="ZIP">
                    </div>
                </div>
                <div class="flex gap-2 pt-1"><button class="btn-primary btn-sm">Save</button><button type="button" @click="editOpen = false" class="btn-ghost btn-sm">Cancel</button></div>
            </form>
        </div>
    </div>

    {{-- ── Reset password modal ───────────────────────────────────────────── --}}
    <div x-show="pwOpen" x-cloak @keydown.escape.window="pwOpen = false" @click.self="pwOpen = false"
         class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
         style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
        <div class="card w-full max-w-md my-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-text">Reset Password</h3>
                <button @click="pwOpen = false" class="btn-ghost btn-sm"><x-icon name="x" class="w-4 h-4" /></button>
            </div>
            <form method="POST" action="{{ route('staff.customers.password', $user) }}" class="space-y-3">
                @csrf @method('PATCH')
                <p class="text-sm text-muted">Set a new password for <span class="text-text">{{ $user->email }}</span>.</p>
                <div><label class="label">New password</label><input type="password" name="password" class="input" required minlength="8"></div>
                <div><label class="label">Confirm password</label><input type="password" name="password_confirmation" class="input" required minlength="8"></div>
                <div class="flex gap-2 pt-1"><button class="btn-primary btn-sm">Update Password</button><button type="button" @click="pwOpen = false" class="btn-ghost btn-sm">Cancel</button></div>
            </form>
        </div>
    </div>

</div>
@endsection
