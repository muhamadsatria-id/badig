@props([
    'color' => 'neutral',
    'dot' => false,
])

@php
    $classes = match ($color) {
        'success' => 'border-accent/20 bg-[var(--accent-soft)] text-accent-strong dark:text-accent',
        'warning' => 'border-warning/20 bg-[var(--warning-soft)] text-warning',
        'danger' => 'border-danger/20 bg-[var(--danger-soft)] text-danger',
        default => 'border-line bg-surface-raised text-muted',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] {$classes}") }}>
    @if($dot)
        <span @class([
            'size-1.5 rounded-full',
            'bg-accent' => $color === 'success',
            'bg-warning' => $color === 'warning',
            'bg-danger' => $color === 'danger',
            'bg-faint' => $color === 'neutral',
        ])></span>
    @endif
    {{ $slot }}
</span>
