@extends('layouts.app')

@section('title', 'Backup Sistem')
@section('page_title', 'Backup Sistem')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between badig-card">
        <div class="space-y-1">
            <!-- <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">backup</span>
                Backup Sistem
            </div> -->
            <h2 class="text-xl font-semibold tracking-tight text-ink">Manajemen Backup Sistem</h2>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Backup disimpan di direktori sistem dan dapat diunduh langsung dari halaman ini dengan status yang terpantau jelas.
            </p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="{{ route('admin.backup.files') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-4 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary">
                <!-- <span class="material-symbols-outlined text-base">folder_open</span> -->
                Semua File Backup
            </a>
            <form method="POST" action="{{ route('admin.backup.trigger') }}">
                @csrf
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary">
                    <span class="material-symbols-outlined text-base">sync</span>
                    Buat Backup Baru
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
        <div class="flex items-start gap-3 border-b border-line pb-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                <span class="material-symbols-outlined text-lg">cloud_download</span>
            </div>
            <div class="space-y-1">
                <h3 class="text-sm font-semibold text-ink">Riwayat Backup Database</h3>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Setiap backup yang berhasil dibuat akan muncul sebagai rekaman log. Tombol unduh hanya aktif untuk file dengan format aman seperti .sql, .sql.gz, .gz, atau .zip.
                </p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:hidden">
            @forelse($backups as $backup)
                @php
                    $statusStr = strtoupper((string) $backup->status);
                    $isSuccess = str_contains($statusStr, 'SUCCESS') || str_contains($statusStr, 'BERHASIL') || str_contains($statusStr, 'DONE');
                    $isFailed = str_contains($statusStr, 'FAIL') || str_contains($statusStr, 'GAGAL') || str_contains($statusStr, 'ERROR');
                    $downloadName = basename((string) $backup->nama_file);
                    $canDownload = $isSuccess && preg_match('/^[A-Za-z0-9._-]+\.(sql|sql\.gz|gz|zip)$/', $downloadName);
                @endphp
                <div class="rounded-2xl border border-line bg-surface-raised p-4 shadow-sm badig-card">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-muted">
                            <span class="material-symbols-outlined text-sm">calendar_month</span>
                            {{ $backup->created_at?->format('d/m/Y H:i') ?? '-' }} WIB
                        </span>

                        @if($isSuccess)
                            <span class="inline-flex items-center gap-1 rounded-full border border-accent/20 bg-[var(--accent-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-accent badig-badge">
                                <span class="size-1.5 rounded-full bg-accent"></span>
                                Success
                            </span>
                        @elseif($isFailed)
                            <span class="inline-flex items-center gap-1 rounded-full border border-danger/20 bg-[var(--danger-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-danger badig-badge">
                                <span class="size-1.5 rounded-full bg-danger"></span>
                                Error
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full border border-warning/20 bg-[var(--warning-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-warning badig-badge">
                                <span class="size-1.5 rounded-full bg-warning"></span>
                                Pending
                            </span>
                        @endif
                    </div>

                    <div class="space-y-2 rounded-xl border border-line bg-surface p-3">
                        <div class="break-all text-[11px] font-mono font-semibold text-ink">
                            {{ $backup->nama_file }}
                        </div>
                        <div class="flex items-center gap-1 text-sm text-muted">
                            <span class="material-symbols-outlined text-sm text-faint">database</span>
                            Ukuran: <span class="ml-1 font-mono font-semibold text-ink">{{ number_format(((int) $backup->ukuran) / 1024, 2) }} KB</span>
                        </div>
                    </div>

                    @if($canDownload)
                        <a href="{{ route('admin.backup.download', $downloadName) }}" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-accent px-3 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary">
                            <span class="material-symbols-outlined text-base">download</span>
                            Unduh Backup
                        </a>
                    @else
                        <button type="button" disabled class="mt-3 inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg bg-surface-raised px-3 py-2.5 text-sm font-semibold text-faint badig-action badig-action-secondary">
                            <span class="material-symbols-outlined text-base">block</span>
                            File Tidak Tersedia
                        </button>
                    @endif
                </div>
            @empty
                <div class="col-span-1 rounded-2xl border border-dashed border-line bg-surface-raised px-4 py-10 text-center text-sm text-muted sm:col-span-2 badig-card">
                    Belum ada rekaman backup. Klik <span class="font-semibold text-muted">Buat Backup Baru</span> terlebih dahulu.
                </div>
            @endforelse
        </div>

        <div class="hidden rounded-2xl border border-line lg:block">
    <table class="w-full table-fixed divide-y divide-line text-sm">
        <thead class="bg-surface-raised">
            <tr>
                <th class="w-[18%] px-4 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Waktu Pembuatan</th>
                <th class="w-[40%] px-4 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Nama File Arsip SQL</th>
                <th class="w-[14%] px-4 py-3.5 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Ukuran</th>
                <th class="w-[14%] px-4 py-3.5 text-center text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Status</th>
                <th class="w-[14%] px-4 py-3.5 text-right text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line bg-surface text-muted">
            @forelse($backups as $backup)
                @php
                    $statusStr = strtoupper((string) $backup->status);
                    $isSuccess = str_contains($statusStr, 'SUCCESS') || str_contains($statusStr, 'BERHASIL') || str_contains($statusStr, 'DONE');
                    $isFailed = str_contains($statusStr, 'FAIL') || str_contains($statusStr, 'GAGAL') || str_contains($statusStr, 'ERROR');
                    $downloadName = basename((string) $backup->nama_file);
                    $canDownload = $isSuccess && preg_match('/^[A-Za-z0-9._-]+\.(sql|sql\.gz|gz|zip)$/', $downloadName);
                @endphp
                <tr class="transition-colors duration-150 hover:bg-surface-raised">
                    <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-muted">
                        {{ $backup->created_at?->format('d/m/Y H:i') ?? '-' }} WIB
                    </td>
                    
                    <td class="truncate px-4 py-3.5 font-mono text-xs font-semibold text-ink" title="{{ $backup->nama_file }}">
                        {{ $backup->nama_file }}
                    </td>
                    
                    <td class="whitespace-nowrap px-4 py-3.5 font-mono text-sm text-muted">
                        <span class="font-semibold text-ink">{{ number_format(((int) $backup->ukuran) / 1024, 2) }}</span> KB
                    </td>
                    <td class="whitespace-nowrap px-4 py-3.5 text-center">
                        @if($isSuccess)
                            <span class="inline-flex items-center gap-1 rounded-full border border-accent/20 bg-[var(--accent-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-accent badig-badge">
                                <span class="size-1.5 rounded-full bg-accent"></span>
                                Success
                            </span>
                        @elseif($isFailed)
                            <span class="inline-flex items-center gap-1 rounded-full border border-danger/20 bg-[var(--danger-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-danger badig-badge" title="{{ $backup->status }}">
                                <span class="size-1.5 rounded-full bg-danger"></span>
                                {{ $backup->status }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full border border-warning/20 bg-[var(--warning-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-warning badig-badge">
                                <span class="size-1.5 rounded-full bg-warning"></span>
                                Pending
                            </span>
                        @endif
                    </td>
                    
                    <td class="whitespace-nowrap px-4 py-3.5 text-right">
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

                                    @if($canDownload)
                                        <a href="{{ route('admin.backup.download', $downloadName) }}" role="menuitem" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-ink transition hover:bg-surface-raised">
                                            <flux:icon name="document-arrow-down" class="size-4 shrink-0 text-accent" />
                                            <span>Unduh</span>
                                        </a>
                                    @endif

                                    <form action="{{ route('admin.backup.delete', $downloadName) }}" method="POST" class="w-full" data-confirm="Hapus file backup ini?">
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
                    <td colspan="5" class="px-6 py-10 text-center text-sm text-muted">
                        Belum ada rekaman backup. Klik <span class="font-semibold text-muted">Buat Backup Baru</span> terlebih dahulu.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
    </div>
</div>
@endsection
