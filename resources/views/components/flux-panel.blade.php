@props(['title' => null, 'subtitle' => null])
<flux:card {{ $attributes->merge(['class' => 'space-y-5']) }}>
    @if($title || $subtitle)
        <div>
            @if($title)<flux:heading size="lg">{{ $title }}</flux:heading>@endif
            @if($subtitle)<flux:text class="mt-1">{{ $subtitle }}</flux:text>@endif
        </div>
        <flux:separator />
    @endif
    {{ $slot }}
</flux:card>
