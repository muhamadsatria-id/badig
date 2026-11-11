@props([
    'label',
    'value',
    'suffix' => null,
    'icon' => 'chart-bar',
    'trend' => null,
    'trendDirection' => 'up',
])

<article {{ $attributes->class('badig-card group rounded-2xl p-5 transition duration-200 hover:-translate-y-0.5 hover:shadow-[var(--shadow-soft)]') }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="truncate text-[10px] font-semibold uppercase tracking-[0.18em] text-muted">{{ $label }}</p>
            <div class="mt-3 flex items-end gap-1.5">
                <strong class="text-3xl font-bold tracking-[-0.04em] text-ink">{{ $value }}</strong>
                @if($suffix)
                    <span class="pb-1 text-xs font-medium text-muted">{{ $suffix }}</span>
                @endif
            </div>
        </div>

        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-accent/15 bg-[var(--accent-soft)] text-accent transition group-hover:scale-105">
            <flux:icon :name="$icon" class="size-4.5" />
        </span>
    </div>

    @if($trend)
        <div class="mt-4 flex items-center gap-1.5 text-xs">
            <span @class([
                'font-semibold',
                'text-accent-strong dark:text-accent' => $trendDirection === 'up',
                'text-danger' => $trendDirection === 'down',
            ])>
                {{ $trendDirection === 'up' ? '▲' : '▼' }} {{ $trend }}
            </span>
            <span class="text-muted">periode ini</span>
        </div>
    @endif
</article>
