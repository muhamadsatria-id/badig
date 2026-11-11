<div {{ $attributes->class('badig-card overflow-hidden') }}>
    <div class="badig-scrollbar overflow-x-auto">
        <table class="min-w-full text-sm">
            {{ $slot }}
        </table>
    </div>
</div>
