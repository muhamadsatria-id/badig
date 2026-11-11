@props(['label' => null, 'name', 'value' => null, 'rows' => 4, 'hint' => null])
<div class="space-y-2">
    @if($label)<label for="{{ $name }}" class="block text-xs font-semibold text-ink">{{ $label }}</label>@endif
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->class('badig-focus badig-field block w-full resize-y px-3.5 py-3 text-sm placeholder:text-faint') }}>{{ old($name, $value) }}</textarea>
    @error($name)<p class="text-xs text-danger">{{ $message }}</p>@else @if($hint)<p class="text-xs text-muted">{{ $hint }}</p>@endif @enderror
</div>
