@extends('layouts.app')

@section('title', 'Daftar File Backup Database')
@section('page_title', 'Daftar File Backup Database')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm md:flex md:items-center md:justify-between badig-card">
        <div class="space-y-1">
            <!-- <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">database</span>
                Backup Files
            </div> -->
            <h3 class="text-xl font-semibold text-ink">File Backup Database</h3>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Daftar ini membaca file fisik backup dari direktori penyimpanan sistem, termasuk folder internal dan publik.
            </p>
        </div>

        <a href="{{ route('admin.backup') }}" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface px-4 py-2 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="rounded-2xl border border-line">
    <table class="w-full table-fixed divide-y divide-line text-sm">
        <thead class="bg-surface-raised">
            <tr>
                <th class="w-[35%] px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Nama File</th>
                <th class="w-[25%] px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Lokasi</th>
                <th class="w-[15%] px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Ukuran</th>
                <th class="w-[17%] px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Waktu</th>
                <th class="w-[8%] px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line bg-surface text-muted">
            @forelse($files as $file)
                <tr class="transition-colors duration-150 hover:bg-surface-raised">
                    <td class="truncate px-5 py-3.5 font-mono text-xs font-semibold text-ink" title="{{ $file['name'] }}">
                        {{ $file['name'] }}
                    </td>
                    <td class="truncate px-5 py-3.5 text-xs text-muted" title="{{ $file['path_label'] }}">
                        {{ $file['path_label'] }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-muted">
                        {{ $file['size'] }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-muted">
                        {{ $file['modified_at'] }}
                    </td>
                    
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <div x-data="{
                            open: false,
                            top: 0,
                            left: 0,
                            menuWidth: 160,

                            toggle() {
                                this.open = !this.open;
                                if (this.open) {
                                    this.$nextTick(() => this.updatePosition());
                                }
                            },

                            close() {
                                this.open = false;
                            },

                            updatePosition() {
                                const trigger = this.$refs.trigger;
                                const menu = this.$refs.menu;
                                if (!trigger) return;
                                const rect = trigger.getBoundingClientRect();
                                const gap = 8;
                                const viewportPadding = 12;
                                const menuHeight = menu?.offsetHeight || 120;

                                let calculatedTop = rect.bottom + gap;
                                if (calculatedTop + menuHeight > window.innerHeight - viewportPadding) {
                                    calculatedTop = rect.top - menuHeight - gap;
                                }

                                let calculatedLeft = rect.right - this.menuWidth;
                                calculatedLeft = Math.max(viewportPadding, Math.min(calculatedLeft, window.innerWidth - this.menuWidth - viewportPadding));

                                this.top = calculatedTop;
                                this.left = calculatedLeft;
                            }
                        }" class="inline-flex" @keydown.escape.window="close()" @resize.window="open && updatePosition()" @scroll.window="open && updatePosition()">
                            <button x-ref="trigger" type="button" @click.stop="toggle()" class="badig-focus inline-flex h-9 items-center gap-2 rounded-lg border border-line bg-surface px-3 text-xs font-semibold text-ink shadow-sm transition hover:bg-surface-raised" :aria-expanded="open" aria-haspopup="menu">
                                <flux:icon name="ellipsis-vertical" class="size-4 text-muted" />
                                <span>Aksi</span>
                                <span class="transition-transform duration-200" :class="{ 'rotate-180': open }">
                                    <flux:icon name="chevron-down" class="size-3.5 text-muted" />
                                </span>
                            </button>

                            <template x-teleport="body">
                                <div x-cloak x-show="open" x-ref="menu" role="menu" @click.outside="close()" @click.stop
                                    x-transition:enter="transition duration-150 ease-out"
                                    x-transition:enter-start="translate-y-1 scale-95 opacity-0"
                                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                    x-transition:leave="transition duration-100 ease-in"
                                    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                                    x-transition:leave-end="translate-y-1 scale-95 opacity-0"
                                    :style="`top: ${top}px; left: ${left}px; width: ${menuWidth}px;`"
                                    class="fixed z-[9999] origin-top-right rounded-xl border border-line bg-surface p-1.5 text-left shadow-2xl space-y-0.5">

                                    <a href="{{ route('admin.backup.download', $file['name']) }}" role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-ink transition hover:bg-surface-raised">
                                        <flux:icon name="document-arrow-down" class="size-4 shrink-0 text-accent" />
                                        <span>Unduh</span>
                                    </a>

                                    <form action="{{ route('admin.backup.delete', $file['name']) }}" method="POST" class="w-full" data-confirm="Hapus file backup ini?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-red-500 transition hover:bg-red-500/10">
                                            <flux:icon name="trash" class="size-4 shrink-0 text-red-500" />
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <div class="mx-auto flex max-w-md flex-col items-center justify-center gap-2">
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                                <span class="material-symbols-outlined text-2xl">inventory_2</span>
                            </div>
                            <h4 class="text-base font-semibold text-ink">Belum ada file backup fisik</h4>
                            <p class="text-sm leading-6 text-muted">
                                Buat backup terlebih dahulu dari menu Backup untuk menampilkan daftar file di sini.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
    </div>
</div>
@endsection
