@props([
    'levels' => [],
    'columns' => 12,
])

@php
    $normalized = collect($levels)->map(fn ($level) => max(0, min(4, (int) $level)));
@endphp

<div class="space-y-3">
    <div class="grid gap-1.5" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
        @forelse($normalized as $index => $level)
            <span
                @class([
                    'aspect-square min-h-3 rounded-[4px] border border-line transition hover:scale-110',
                    'bg-surface-raised' => $level === 0,
                    'bg-accent/20' => $level === 1,
                    'bg-accent/40' => $level === 2,
                    'bg-accent/65' => $level === 3,
                    'bg-accent' => $level === 4,
                ])
                title="Aktivitas level {{ $level }}"
            ></span>
        @empty
            @for($i = 0; $i < 84; $i++)
                <span class="aspect-square min-h-3 rounded-[4px] border border-line bg-surface-raised"></span>
            @endfor
        @endforelse
    </div>

    <div class="flex items-center justify-end gap-1.5 text-[10px] text-muted">
        <span>Rendah</span>
        <span class="size-2.5 rounded-[3px] border border-line bg-surface-raised"></span>
        <span class="size-2.5 rounded-[3px] bg-accent/25"></span>
        <span class="size-2.5 rounded-[3px] bg-accent/50"></span>
        <span class="size-2.5 rounded-[3px] bg-accent/75"></span>
        <span class="size-2.5 rounded-[3px] bg-accent"></span>
        <span>Tinggi</span>
    </div>
</div>
