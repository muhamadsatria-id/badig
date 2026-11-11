@props([
    'variant' => 'info',
    'timeout' => 5000,
])

@php
    $variants = [
        'success' => 'border-accent/20 bg-[var(--accent-soft)] text-accent-strong dark:text-accent',
        'error' => 'border-danger/20 bg-[var(--danger-soft)] text-danger',
        'warning' => 'border-warning/20 bg-[var(--warning-soft)] text-warning',
        'info' => 'border-line bg-surface-raised text-muted',
    ];

    $icons = [
        'success' => 'check_circle',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
    ];

    $tone = $variants[$variant] ?? $variants['info'];
    $icon = $icons[$variant] ?? $icons['info'];
@endphp

<div
    x-data="badigAlert({{ (int) $timeout }})"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    role="alert"
    {{ $attributes->merge(['class' => 'rounded-xl border px-4 py-3 text-sm font-medium ' . $tone]) }}
>
    <div class="flex items-start gap-2.5">
        <span class="material-symbols-outlined mt-0.5 shrink-0 text-base leading-none" aria-hidden="true">{{ $icon }}</span>
        <div class="min-w-0 flex-1">{{ $slot }}</div>
        <button
            type="button"
            @click="close()"
            class="badig-focus -mr-1 -mt-0.5 shrink-0 rounded-md p-1 opacity-70 transition hover:opacity-100"
            aria-label="Tutup notifikasi"
        >
            <span class="material-symbols-outlined text-base leading-none" aria-hidden="true">close</span>
        </button>
    </div>
</div>
