@extends('layouts.app')

@section('title', 'Arsip ' . $module['title'])
@section('page_title', 'Arsip ' . $module['title'])

@section('content')
<div class="w-full min-w-0">
    <div
        class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <div
                    class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">inventory_2</span>
                    Arsip terintegrasi
                </div>
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-ink">Arsip Data: {{ $module['title'] }}</h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-muted">
                        Kelola basis data intelkam terintegrasi, kontrol berkas lampiran, dokumen PDF, dan cetak laporan
                        global.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('units.report-pdf', array_merge([$unit], request()->query())) }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-4 py-2 text-sm font-semibold text-muted shadow-sm transition hover:bg-surface-raised badig-action badig-action-secondary">
                    <span class="material-symbols-outlined text-base text-faint">picture_as_pdf</span>
                    Cetak PDF
                </a>
                @if(auth()->user()->canWriteUnitId((int) $module['id']))
                <a href="{{ route('units.create', $unit) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-[#07140c] shadow-sm transition hover:bg-accent-strong badig-action badig-action-primary">
                    <span class="material-symbols-outlined text-base">add</span>
                    Tambah Arsip
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success') || session('error') || $errors->any())
    <div class="mt-6 rounded-2xl border border-line bg-surface px-4 py-3 shadow-sm badig-card">
        @if(session('success'))
        <div class="flex items-center gap-2 text-sm font-medium text-accent">
            <span class="material-symbols-outlined text-base text-accent">check_circle</span>
            {{ session('success') }}
        </div>
        @elseif(session('error') || $errors->any())
        <div class="flex items-center gap-2 text-sm font-medium text-danger">
            <span class="material-symbols-outlined text-base text-danger">error</span>
            {{ session('error') ?: $errors->first() }}
        </div>
        @endif
    </div>
    @endif

    <div class="mt-6 rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
        <form method="GET" action="{{ route('units.index', $unit) }}"
            class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <div class="flex-1">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Pencarian Arsip
                </label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                    placeholder="Cari ID, nama entitas, pelapor, atau NRP..."
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
            </div>

            <div class="w-full lg:w-48">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Mulai Tanggal
                </label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
            </div>

            <div class="w-full lg:w-48">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Sampai
                </label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
            </div>

            <button
                class="inline-flex h-11 items-center justify-center rounded-lg bg-accent px-4 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong lg:min-w-[110px] badig-action badig-action-primary"
                type="submit">
                Filter
            </button>
        </form>

        <form id="bulkExportForm" method="POST" action="{{ route('units.export-selected-pdf', $unit) }}"
            target="_blank" rel="noopener noreferrer">
            @csrf
        </form>

        <div class="mt-6 overflow-x-auto overflow-y-visible rounded-2xl border border-line">
            <div
                class="flex flex-col gap-2 border-b border-line bg-surface-raised p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Arsip Terintegrasi</h3>
                    <p class="text-sm text-muted">Gunakan checkbox untuk memilih beberapa berkas sekaligus lalu cetak
                        gabungan.</p>
                </div>
                <button form="bulkExportForm" type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-danger/20 bg-[var(--danger-soft)] px-3 py-2 text-sm font-semibold text-danger transition hover:bg-[var(--danger-soft)] badig-action badig-action-danger">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                    Cetak Pilihan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line">
                    <thead class="bg-surface-raised">
                        <tr>
                            <th class="w-10 px-3 py-3 text-center">
                                <input type="checkbox" id="selectAllRows"
                                    class="rounded border-line text-ink focus:ring-accent/20">
                            </th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                ID</th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Tanggal</th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Kategori</th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama / Entitas Arsip</th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Pelapor</th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                NRP</th>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Wilayah</th>
                            <th
                                class="px-3 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Lampiran</th>
                            <th
                                class="px-3 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line bg-surface text-muted">
                        @forelse($items as $item)
                        <tr class="transition hover:bg-surface-raised">
                            <td class="px-3 py-3 text-center">
                                <input class="rowCheckbox rounded border-line text-ink focus:ring-accent/20"
                                    type="checkbox" name="ids[]" value="{{ $item->id }}" form="bulkExportForm">
                            </td>
                            <td class="px-3 py-3 font-mono text-sm text-muted">#{{ $item->id }}</td>
                            <td class="px-3 py-3 text-sm text-muted">
                                {{ optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-3 py-3">
                                <span
                                    class="inline-flex rounded-full border border-line bg-surface-raised px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-muted badig-badge">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-sm font-semibold text-ink">{{ $item->{$module['main']} }}</td>
                            <td class="px-3 py-3 text-sm text-muted">{{ $item->pelapor_nama ?? '-' }}</td>
                            <td class="px-3 py-3 font-mono text-sm text-muted">{{ $item->pelapor_nrp ?? '-' }}</td>
                            <td class="px-3 py-3 text-sm text-muted">{{ $item->village?->name ?? '-' }}, <span
                                    class="text-faint">{{ $item->district?->name ?? '-' }}</span></td>
                            <td class="px-3 py-3 text-center">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full border border-line bg-surface-raised px-2.5 py-1 text-[11px] font-semibold text-muted badig-badge">
                                    <span class="material-symbols-outlined text-sm text-faint">attach_file</span>
                                    {{ $attachmentCounts[$item->id] ?? 0 }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <div x-data="{
            open: false,
            top: 0,
            left: 0,
            menuWidth: 184,

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
                const menuHeight = menu?.offsetHeight || 140;

                // Default: tampil di bawah tombol.
                let calculatedTop = rect.bottom + gap;

                // Jika ruang bawah tidak cukup, tampilkan di atas tombol.
                if (
                    calculatedTop + menuHeight >
                    window.innerHeight - viewportPadding
                ) {
                    calculatedTop = rect.top - menuHeight - gap;
                }

                // Sejajarkan sisi kanan dropdown dengan tombol.
                let calculatedLeft = rect.right - this.menuWidth;

                // Jaga dropdown agar tidak keluar viewport.
                calculatedLeft = Math.max(
                    viewportPadding,
                    Math.min(
                        calculatedLeft,
                        window.innerWidth - this.menuWidth - viewportPadding
                    )
                );

                this.top = calculatedTop;
                this.left = calculatedLeft;
            }
        }" class="inline-flex" @keydown.escape.window="close()" @resize.window="open && updatePosition()"
                                    @scroll.window="open && updatePosition()">
                                    {{-- Tombol pemicu --}}
                                    <button x-ref="trigger" type="button" @click.stop="toggle()"
                                        class="badig-focus inline-flex h-9 items-center gap-2 rounded-lg border border-line bg-surface px-3 text-xs font-semibold text-ink shadow-sm transition hover:bg-surface-raised"
                                        :aria-expanded="open" aria-haspopup="menu">
                                        <flux:icon name="ellipsis-vertical" class="size-4 text-muted" />

                                        <span>Aksi</span>

                                        <span class="transition-transform duration-200" :class="{ 'rotate-180': open }">
                                            <flux:icon name="chevron-down" class="size-3.5 text-muted" />
                                        </span>
                                    </button>

                                    {{-- Dipindahkan ke body agar tidak terpotong frame tabel --}}
                                    <template x-teleport="body">
                                        <div x-cloak x-show="open" x-ref="menu" role="menu" @click.outside="close()"
                                            @click.stop x-transition:enter="transition duration-150 ease-out"
                                            x-transition:enter-start="translate-y-1 scale-95 opacity-0"
                                            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                            x-transition:leave="transition duration-100 ease-in"
                                            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                                            x-transition:leave-end="translate-y-1 scale-95 opacity-0" :style="`
                    top: ${top}px;
                    left: ${left}px;
                    width: ${menuWidth}px;
                `" class="fixed z-[9999] origin-top-right rounded-xl border border-line bg-surface p-1.5 text-left shadow-2xl">
                                            <a href="{{ route('units.show', [$unit, $item->id]) }}" role="menuitem"
                                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-ink transition hover:bg-surface-raised">
                                                <flux:icon name="eye" class="size-4 shrink-0 text-muted" />

                                                <span>Detail</span>
                                            </a>

                                            <a href="{{ route('units.pdf', [$unit, $item->id]) }}" target="_blank" rel="noopener noreferrer"
                                                rel="noopener noreferrer" role="menuitem"
                                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-ink transition hover:bg-surface-raised">
                                                <flux:icon name="document-arrow-down"
                                                    class="size-4 shrink-0 text-danger" />

                                                <span>Unduh PDF</span>
                                            </a>

                                            @if (auth()->user()->canWriteUnitId((int) $module['id']))
                                            <div class="my-1 border-t border-line"></div>

                                            <a href="{{ route('units.edit', [$unit, $item->id]) }}" role="menuitem"
                                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-ink transition hover:bg-surface-raised">
                                                <flux:icon name="pencil-square" class="size-4 shrink-0 text-warning" />

                                                <span>Edit Data</span>
                                            </a>

                                            <form action="{{ route('units.destroy', [$unit, $item->id]) }}" method="POST" class="w-full" data-confirm="Apakah Anda yakin ingin menghapus data ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" role="menuitem"
                                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-xs font-medium text-red-500 transition hover:bg-red-500/10">
                                                    <flux:icon name="trash" class="size-4 shrink-0 text-red-500" />

                                                    <span>Hapus Data</span>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-10 text-center text-sm text-muted">
                                Belum ada rekaman arsip data yang terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-line bg-surface-raised px-4 py-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script @cspNonce>
document.addEventListener('DOMContentLoaded', () => {
    const all = document.getElementById('selectAllRows');
    const rows = document.querySelectorAll('.rowCheckbox');

    all?.addEventListener('change', () => rows.forEach((checkbox) => {
        checkbox.checked = all.checked;
    }));
});
</script>
@endpush