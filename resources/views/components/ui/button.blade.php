@props([
    'href' => null,
    'variant' => 'secondary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $variantClass = match ($variant) {
        'primary' => 'badig-action-primary',
        'danger' => 'badig-action-danger',
        'warning' => 'badig-action-warning',
        default => 'badig-action-secondary',
    };

    $classes = "badig-focus badig-action {$variantClass}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
