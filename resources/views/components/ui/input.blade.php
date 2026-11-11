@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'error' => null,
    'revealable' => false,
])

<div class="space-y-2" @if($revealable) x-data="{ visible: false }" @endif>
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-semibold text-ink">{{ $label }}</label>
    @endif

    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            @if($revealable) :type="visible ? 'text' : 'password'" @else type="{{ $type }}" @endif
            value="{{ old($name, $value) }}"
            {{ $attributes->class([
                'badig-focus badig-field block h-11 w-full px-3.5 text-sm placeholder:text-faint',
                'border-danger' => $error,
                'pr-11' => $revealable,
            ]) }}
        >

        @if($revealable)
            <button type="button" @click="visible = !visible" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-muted transition hover:text-ink" aria-label="Tampilkan atau sembunyikan kata sandi">
                <flux:icon name="eye" class="size-4" x-show="!visible" />
                <flux:icon name="eye-slash" class="size-4" x-show="visible" x-cloak />
            </button>
        @endif
    </div>

    @if($error)
        <p class="text-xs text-danger">{{ $error }}</p>
    @elseif($hint)
        <p class="text-xs text-muted">{{ $hint }}</p>
    @endif
</div>
