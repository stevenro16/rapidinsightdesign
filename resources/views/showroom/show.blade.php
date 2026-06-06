<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $showroomItem->title }} — RapidInsight ShowRoom</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex flex-col h-screen overflow-hidden">

    {{-- Top bar --}}
    <header class="h-12 flex items-center justify-between px-4 border-b border-[var(--color-border)] bg-[var(--color-surface)] shrink-0 z-10">
        <div class="flex items-center gap-3">
            <a href="/showroom" class="text-[var(--color-muted)] hover:text-[var(--color-primary)] transition-colors flex items-center gap-1 text-sm">
                <x-icon name="chevron-left" class="w-4 h-4" />
                Back
            </a>
            <span class="text-[var(--color-border)]">/</span>
            <span class="text-sm font-semibold text-[var(--color-text)]">{{ $showroomItem->title }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if($showroomItem->tech_tags)
            @foreach($showroomItem->techTagsArray() as $tag)
            <span class="badge badge-green hidden sm:inline-flex">{{ trim($tag) }}</span>
            @endforeach
            @endif
        </div>
    </header>

    {{-- Iframe --}}
    <main class="flex-1 relative" x-data="iframeEmbed('{{ $showroomItem->private_url ?? $showroomItem->embed_url }}')">
        {{-- Loading skeleton --}}
        <div x-show="!loaded" class="absolute inset-0 flex items-center justify-center bg-[var(--color-bg)]">
            <div class="text-center">
                <div class="skeleton w-16 h-16 rounded-full mx-auto mb-4"></div>
                <p class="text-sm text-[var(--color-muted)]">Loading {{ $showroomItem->title }}…</p>
            </div>
        </div>
        <iframe :src="url" @load="onLoad()"
                x-show="loaded"
                class="w-full h-full border-0"
                allow="fullscreen; clipboard-write"
                sandbox="allow-scripts allow-forms allow-same-origin allow-popups">
        </iframe>
    </main>

</body>
</html>
