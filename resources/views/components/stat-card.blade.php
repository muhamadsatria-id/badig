@props([
    'title' => '',
    'value' => 0,
    'icon' => 'chart-bar',
    'color' => 'zinc',
    'subtitle' => null,
])
<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:text class="text-xs font-black uppercase tracking-[.14em] text-muted">{{ $title }}</flux:text>
            <flux:heading size="xl" class="mt-2">{{ $value }}</flux:heading>
            @if($subtitle)<flux:text class="mt-1 text-xs">{{ $subtitle }}</flux:text>@endif
        </div>
        <div class="rounded-2xl bg-surface-raised p-3 text-muted bg-surface-raised text-ink">
            <flux:icon name="{{ $icon }}" class="size-5" />
        </div>
    </div>
</flux:card>
