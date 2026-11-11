@props([
    'label' => null,
    'name',
    'value' => null,
    'placeholder' => null,
    'rows' => 4,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
])
<flux:textarea
    name="{{ $name }}"
    :label="$label"
    placeholder="{{ $placeholder }}"
    rows="{{ $rows }}"
    :required="$required"
    :readonly="$readonly"
    :disabled="$disabled"
    {{ $attributes }}
>{{ old($name, $value) }}</flux:textarea>
