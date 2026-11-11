@props(['label' => null, 'name', 'hint' => null])
<div class="space-y-2">
    @if($label)<label for="{{ $name }}" class="block text-xs font-semibold text-ink">{{ $label }}</label>@endif
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->class('badig-focus badig-field block h-11 w-full px-3.5 text-sm') }}>{{ $slot }}</select>
    @error($name)<p class="text-xs text-danger">{{ $message }}</p>@else @if($hint)<p class="text-xs text-muted">{{ $hint }}</p>@endif @enderror
</div>
