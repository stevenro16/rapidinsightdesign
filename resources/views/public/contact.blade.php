@extends('layouts.public')
@section('title', 'Contact')

@section('content')
<section class="wide py-24">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-12">
            <p class="label text-[var(--color-primary)] mb-2">Get In Touch</p>
            <h1 class="text-4xl md:text-5xl font-display font-bold text-[var(--color-text)] mb-4">
                Let's <span class="gradient-text">Talk</span>
            </h1>
            <p class="text-[var(--color-muted)]">
                Have a project in mind? We'd love to hear about it. Fill out the form and we'll get back to you within one business day.
            </p>
        </div>

        <div class="card">
            <form method="POST" action="/contact" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <div class="space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="label">Your Name</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="input {{ $errors->has('name') ? 'border-[var(--color-danger)]' : '' }}"
                                   placeholder="Jane Smith" required>
                            @error('name')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                                   class="input {{ $errors->has('email') ? 'border-[var(--color-danger)]' : '' }}"
                                   placeholder="you@example.com" required>
                            @error('email')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="label">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                               class="input {{ $errors->has('subject') ? 'border-[var(--color-danger)]' : '' }}"
                               placeholder="New project inquiry" required>
                        @error('subject')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Message</label>
                        <textarea name="message" rows="5"
                                  class="input resize-none {{ $errors->has('message') ? 'border-[var(--color-danger)]' : '' }}"
                                  placeholder="Tell us about your project, timeline, and goals…" required>{{ old('message') }}</textarea>
                        @error('message')<p class="text-xs text-[var(--color-danger)] mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center" :disabled="loading">
                        <span x-show="!loading">Send Message</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Sending…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
