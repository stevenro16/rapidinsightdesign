@extends('layouts.portal')
@section('title', 'My Account')
@section('page-title', 'My Account')
@section('breadcrumb', 'Update your details')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Profile / business details --}}
    <form method="POST" action="{{ route('profile.update') }}" class="card space-y-4">
        @csrf @method('PATCH')
        <div>
            <h3 class="font-semibold text-text">Your Details</h3>
            <p class="text-xs text-muted mt-0.5">Keep this up to date so our paperwork and invoices are accurate.</p>
        </div>

        @if($errors->hasAny(['name','email','company','website','billing_email','phone','address_line1','address_line2','city','state','postal_code']))
        <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="grid sm:grid-cols-2 gap-3">
            <div><label class="label">Full name</label><input type="text" name="name" value="{{ old('name', $user->name) }}" class="input" required></div>
            <div><label class="label">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="input" required></div>
            <div><label class="label">Company</label><input type="text" name="company" value="{{ old('company', $user->company) }}" class="input"></div>
            <div><label class="label">Phone</label><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="input"></div>
            <div><label class="label">Website</label><input type="text" name="website" value="{{ old('website', $user->website) }}" class="input" placeholder="https://…"></div>
            <div><label class="label">Billing email <span class="text-muted font-normal">(if different)</span></label><input type="email" name="billing_email" value="{{ old('billing_email', $user->billing_email) }}" class="input"></div>
        </div>

        <div>
            <p class="label">Mailing address</p>
            <div class="grid sm:grid-cols-2 gap-3 mt-1">
                <div class="sm:col-span-2"><input type="text" name="address_line1" value="{{ old('address_line1', $user->address_line1) }}" class="input" placeholder="Street address"></div>
                <div class="sm:col-span-2"><input type="text" name="address_line2" value="{{ old('address_line2', $user->address_line2) }}" class="input" placeholder="Apt, suite, unit (optional)"></div>
                <div><input type="text" name="city" value="{{ old('city', $user->city) }}" class="input" placeholder="City"></div>
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" class="input" placeholder="State">
                    <input type="text" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="input" placeholder="ZIP">
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-border">
            <p class="label">Communication preferences</p>
            <label class="flex items-start gap-2 mt-1 cursor-pointer">
                <input type="checkbox" name="email_notifications" value="1" class="rounded mt-0.5" {{ old('email_notifications', $user->email_notifications) ? 'checked' : '' }}>
                <span class="text-sm text-text">Email me notifications
                    <span class="block text-xs text-muted">Updates on agreements, work orders, invoices, and replies to your messages. Account &amp; security emails (like password resets) are always sent.</span>
                </span>
            </label>
        </div>

        <div><button class="btn-primary">Save Details</button></div>
    </form>

    {{-- Password --}}
    <form method="POST" action="{{ route('profile.password') }}" class="card space-y-4">
        @csrf @method('PATCH')
        <div>
            <h3 class="font-semibold text-text">Password</h3>
            <p class="text-xs text-muted mt-0.5">Choose a strong password you don't use elsewhere.</p>
        </div>

        @if($errors->hasAny(['current_password','password']))
        <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="grid sm:grid-cols-3 gap-3">
            <div><label class="label">Current password</label><input type="password" name="current_password" class="input" required></div>
            <div><label class="label">New password</label><input type="password" name="password" class="input" required minlength="8"></div>
            <div><label class="label">Confirm new</label><input type="password" name="password_confirmation" class="input" required minlength="8"></div>
        </div>

        <div><button class="btn-primary">Update Password</button></div>
    </form>
</div>
@endsection
