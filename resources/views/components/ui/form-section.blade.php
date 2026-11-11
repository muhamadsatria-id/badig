@props(['letter' => null, 'title', 'description' => null])

<section {{ $attributes->class('badig-card overflow-hidden') }}>
    <header class="flex items-start gap-3 border-b border-line px-5 py-4">
        @if($letter)
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[var(--accent-soft)] text-sm font-extrabold text-accent">{{ $letter }}</span>
        @endif
        <div>
            <h3 class="text-sm font-semibold text-ink">{{ $title }}</h3>
            @if($description)
                <p class="mt-1 text-xs leading-5 text-muted">{{ $description }}</p>
            @endif
        </div>
    </header>
    <div class="p-5">{{ $slot }}</div>
</section>
