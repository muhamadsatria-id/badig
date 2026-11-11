@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'padding' => true,
])

<section {{ $attributes->class('badig-card overflow-hidden') }}>
    @if($title || $subtitle || $icon || isset($actions))
        <header class="flex items-start justify-between gap-4 border-b border-line px-5 py-4">
            <div class="flex min-w-0 items-start gap-3">
                @if($icon)
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[var(--accent-soft)] text-accent">
                        <flux:icon :name="$icon" class="size-4.5" />
                    </span>
                @endif

                <div class="min-w-0">
                    @if($title)
                        <h3 class="truncate text-sm font-semibold tracking-tight text-ink">{{ $title }}</h3>
                    @endif

                    @if($subtitle)
                        <p class="mt-1 text-xs leading-5 text-muted">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @else
                <button type="button" class="badig-focus badig-icon-button !size-8 !min-h-8 !h-8 !w-8 !flex-[0_0_2rem]" aria-label="Menu kartu">
                    <flux:icon name="ellipsis-horizontal" class="size-4" />
                </button>
            @endisset
        </header>
    @endif

    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
