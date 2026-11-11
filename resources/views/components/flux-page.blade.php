@props(['title' => null, 'subtitle' => null])
<div class="mx-auto max-w-7xl space-y-6">
    @if($title || $subtitle)
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                @if($title)<flux:heading size="xl">{{ $title }}</flux:heading>@endif
                @if($subtitle)<flux:text class="mt-1">{{ $subtitle }}</flux:text>@endif
            </div>
            @isset($actions)<div class="flex flex-wrap gap-2">{{ $actions }}</div>@endisset
        </div>
    @endif
    {{ $slot }}
</div>
