<div x-data="themeSwitcher">
    <button
        type="button"
        @click="toggle()"
        class="badig-focus badig-icon-button"
        :aria-label="theme === 'dark' ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
        :title="theme === 'dark' ? 'Mode terang' : 'Mode gelap'"
    >
        <flux:icon name="sun" class="size-4.5" x-show="theme === 'dark'" />
        <flux:icon name="moon" class="size-4.5" x-show="theme === 'light'" x-cloak />
    </button>
</div>
