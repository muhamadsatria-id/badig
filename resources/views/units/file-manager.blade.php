@extends('layouts.app')

@section('title', 'File Manager ' . $module['title'])
@section('page_title', 'File Manager ' . $module['title'])

@section('content')
<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">folder_open</span>
                    Direktori lampiran
                </div>
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-ink">
                        File Manager Berkas: {{ $module['title'] }}
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-muted">
                        Tiap arsip ditampilkan sebagai satu master berkas. Klik master untuk membuka daftar sub-berkas yang melekat padanya.
                    </p>
                </div>
            </div>

            <a href="{{ route('units.index', $unit) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-4 py-2 text-sm font-semibold text-muted shadow-sm transition hover:bg-surface-raised badig-action badig-action-secondary">
                <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                Kembali
            </a>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
        <form method="GET" action="{{ route('units.file-manager', $unit) }}" data-live-target="#badig-results" class="min-w-0 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Cari Nama Berkas
                </label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama berkas atau ID arsip..." class="mt-1 h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Dari Tanggal
                </label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
            </div>

            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Sampai
                </label>
                <div class="mt-1 flex gap-2">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                    <x-ui.button type="submit" variant="primary" class="h-11 px-4 text-xs"><flux:icon name="funnel" class="size-4" />Terapkan</x-ui.button>
                </div>
            </div>
        </form>

        <form id="bulkExportForm" method="POST" action="{{ route('units.export-selected-pdf', $unit) }}" target="_blank" rel="noopener noreferrer">
            @csrf
        </form>

        <div class="mt-6 flex flex-col gap-3 border-b border-line pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold text-ink">Direktori Master Berkas Unit</h3>
                <p class="text-sm text-muted">Klik master berkas untuk melihat sub-berkasnya, atau centang untuk mencetak gabungan.</p>
            </div>
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-muted">
                    <input type="checkbox" id="selectAllRows" class="rounded border-line text-ink focus:ring-accent/20">
                    Pilih semua
                </label>
                <button form="bulkExportForm" type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg border border-danger/20 bg-[var(--danger-soft)] px-3 py-2 text-sm font-semibold text-danger transition hover:bg-[var(--danger-soft)] badig-action badig-action-danger">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                    Cetak Pilihan
                </button>
            </div>
        </div>

        <div id="badig-results" data-live-region>
        <div class="mt-4 space-y-3">
            @forelse($groups as $group)
                <div x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-line bg-surface-raised badig-card">
                    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <input class="rowCheckbox mt-0.5 shrink-0 rounded border-line text-ink focus:ring-accent/20" type="checkbox" name="ids[]" value="{{ $group->record_id }}" form="bulkExportForm" aria-label="Pilih arsip {{ $group->record_id }}">

                            <button type="button" @click="open = !open" class="badig-focus flex min-w-0 flex-1 items-center gap-3 rounded-lg px-1 py-1 text-left" :aria-expanded="open ? 'true' : 'false'">
                                <span class="material-symbols-outlined shrink-0 text-xl text-muted">folder</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-ink">{{ $group->title !== '' ? $group->title : 'Arsip tanpa judul' }}</span>
                                    <span class="mt-0.5 block text-[11px] text-muted">Unggahan terakhir {{ $group->latest_at?->format('d/m/Y H:i') ?? '-' }}</span>
                                </span>
                                <span class="material-symbols-outlined ml-auto shrink-0 text-base text-muted transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>
                        </div>

                        <div class="flex shrink-0 items-center gap-2 pl-8 sm:pl-0">
                            <a href="{{ route('units.show', [$unit, $group->record_id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised badig-action badig-action-secondary">
                                <span class="material-symbols-outlined text-base text-muted">visibility</span>
                                Lihat Arsip
                            </a>
                            <a href="{{ route('units.edit', [$unit, $group->record_id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised badig-action badig-action-secondary">
                                <span class="material-symbols-outlined text-base text-warning">edit</span>
                                Edit
                            </a>
                        </div>
                    </div>

                    <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="border-t border-line bg-surface">
                        <ul class="divide-y divide-line">
                            @foreach($group->files as $file)
                                <li class="flex flex-col gap-2 p-3 pl-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="material-symbols-outlined text-base text-muted">{{ $file->is_image ? 'image' : ($file->extension === 'pdf' ? 'picture_as_pdf' : 'description') }}</span>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-medium text-ink">{{ $file->original_name }}</div>
                                            <div class="mt-0.5 text-[10px] uppercase tracking-[0.2em] text-faint">{{ $file->extension }} &bull; {{ $file->human_file_size }} &bull; {{ $file->uploader?->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        @if($file->is_viewable)
                                            <a href="{{ route('units.attachments.preview', [$unit, $file->id]) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-surface-raised">
                                                <span class="material-symbols-outlined text-base text-muted">visibility</span>
                                                Lihat
                                            </a>
                                        @endif
                                        <a href="{{ route('units.attachments.download', [$unit, $file->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-surface-raised">
                                            <span class="material-symbols-outlined text-base">download</span>
                                            Unduh
                                        </a>
                                        <form action="{{ route('units.attachments.delete', [$unit, $file->id]) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus file ini?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-danger/20 bg-[var(--danger-soft)] px-3 py-1.5 text-xs font-semibold text-danger transition hover:bg-[var(--danger-soft)] badig-action-danger">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-line bg-surface p-10 text-center text-sm text-muted">
                    Tidak ditemukan riwayat atau lampiran berkas fisik di dalam direktori unit ini.
                </div>
            @endforelse
        </div>

        <div class="mt-4 flex justify-end">
            {{ $groups->links() }}
        </div>
        </div>
    </div>
</div>
@endsection
