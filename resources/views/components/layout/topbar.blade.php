@props([
    'pageTitle' => 'BADIG',
])

<header
    class="badig-topbar fixed inset-x-0 top-0 z-40 flex h-17 items-center border-b px-4 transition-all duration-200 sm:px-6"
    :class="sidebarMini ? 'lg:left-20' : 'lg:left-72'"
>
    <div class="flex w-full items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="toggleSidebar()" class="badig-focus inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-line bg-surface text-muted shadow-sm transition hover:bg-surface-raised hover:text-ink" aria-label="Buka atau perkecil sidebar">
                <flux:icon name="bars-3" class="size-4.5" />
            </button>

            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="truncate text-sm font-semibold tracking-tight text-ink">{{ $pageTitle }}</h1>
                    <span class="hidden size-1.5 rounded-full bg-accent sm:inline-block"></span>
                </div>
                <p class="mt-0.5 hidden text-[10px] font-medium text-muted sm:block">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" data-action="reload-page" class="badig-focus hidden h-10 items-center gap-2 rounded-lg border border-line bg-surface px-3 text-xs font-semibold text-ink shadow-sm transition hover:bg-surface-raised md:inline-flex">
                <flux:icon name="arrow-path" class="size-4 text-muted" />
                Sync
            </button>

            <x-ui.theme-toggle />
        </div>
    </div>
</header>
