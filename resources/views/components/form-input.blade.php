@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'icon' => null,
])
<flux:input
    name="{{ $name }}"
    type="{{ $type }}"
    :label="$label"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    :required="$required"
    :readonly="$readonly"
    :disabled="$disabled"
    @if($icon) icon="{{ $icon }}" @endif
    {{ $attributes }}
/>
