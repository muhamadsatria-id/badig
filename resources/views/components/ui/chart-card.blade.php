@props([
    'title',
    'subtitle' => null,
    'icon' => 'chart-bar',
])

<section {{ $attributes->class('badig-card overflow-hidden rounded-2xl') }}>
    <header class="flex items-start justify-between gap-4 border-b border-line px-5 py-4">
        <div class="flex min-w-0 items-start gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-[var(--accent-soft)] text-accent">
                <flux:icon :name="$icon" class="size-4.5" />
            </span>
            <div class="min-w-0">
                <h3 class="truncate text-sm font-semibold tracking-tight text-ink">{{ $title }}</h3>
                @if($subtitle)
                    <p class="mt-1 text-xs leading-5 text-muted">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <button type="button" class="badig-focus inline-flex size-8 items-center justify-center rounded-lg text-muted transition hover:bg-surface-raised hover:text-ink" aria-label="Menu grafik">
            <flux:icon name="ellipsis-horizontal" class="size-4" />
        </button>
    </header>

    <div class="p-5">
        <div class="relative h-[270px] w-full">
            {{ $slot }}
        </div>
    </div>
</section>
