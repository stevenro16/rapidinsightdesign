@extends('layouts.public')
@section('title', 'Email Preferences')

@section('content')
<section class="wide py-24">
    <div class="max-w-lg mx-auto card">
        <p class="label text-primary mb-1">Communication preferences</p>
        <h1 class="text-2xl font-display font-bold text-text mb-1">Email Notifications</h1>
        <p class="text-sm text-muted mb-5">Settings for <strong class="text-text">{{ $user->email }}</strong>.</p>

        @if($saved)
        <div class="mb-5 p-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-2">
            <x-icon name="check" class="w-4 h-4 shrink-0" />
            Your preferences have been saved.
        </div>
        @endif

        <form method="POST" action="{{ url()->signedRoute('email.preferences.update', ['user' => $user->id]) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <label class="flex items-start gap-3 p-4 rounded-lg border border-border bg-surface-2 cursor-pointer">
                <input type="checkbox" name="email_notifications" value="1" class="rounded mt-0.5" {{ $user->email_notifications ? 'checked' : '' }}>
                <span class="text-sm text-text">
                    Send me email notifications
                    <span class="block text-xs text-muted mt-0.5">Updates on agreements, work orders, invoices, and replies to your messages. Uncheck to stop receiving these.</span>
                </span>
            </label>

            <p class="text-xs text-muted">Account &amp; security emails (such as password resets) are always sent regardless of this setting.</p>

            <button type="submit" class="btn-primary gap-1.5">
                <x-icon name="check" class="w-4 h-4" /> Save preferences
            </button>
        </form>
    </div>
</section>
@endsection
