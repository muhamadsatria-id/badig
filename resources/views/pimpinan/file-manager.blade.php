@extends('layouts.app')

@section('title', 'File Manager Semua Unit')
@section('page_title', 'File Manager Semua Unit')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <x-ui.badge color="success" dot>Repositori Global</x-ui.badge>
                <h2 class="text-xl font-semibold tracking-tight text-ink">File Manager Semua Unit</h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Repositori global lintas unit. Tiap arsip tampil sebagai satu master berkas; klik master untuk membuka sub-berkasnya.
                </p>
            </div>

            <a href="{{ route('pimpinan.file-manager.export', array_merge(request()->query(), ['type' => 'pdf'])) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-lg border border-danger/20 bg-[var(--danger-soft)] px-4 py-2.5 text-sm font-semibold text-danger transition hover:bg-[var(--danger-soft)] badig-action badig-action-danger">
                <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                Ekspor Laporan PDF
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('pimpinan.file-manager') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-line bg-surface p-4 shadow-sm sm:grid-cols-2 md:grid-cols-6 md:items-end badig-card">
        <div class="space-y-2 md:col-span-2">
            <label for="q" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Cari Berkas</label>
            <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama file, ekstensi, ID arsip..." class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field">
        </div>

        <div class="space-y-2">
            <label for="unit" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Unit Kerja</label>
            <select id="unit" name="unit" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                <option value="all">Semua Unit</option>
                @foreach($units as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['unit'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-2">
            <label for="date_from" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Dari Tanggal</label>
            <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
        </div>

        <div class="space-y-2">
            <label for="date_to" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Sampai</label>
            <div class="flex gap-2">
                <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                <x-ui.button type="submit" variant="primary" class="h-11 px-4 text-xs"><flux:icon name="funnel" class="size-4" />Terapkan</x-ui.button>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="border-b border-line bg-surface-raised p-4">
            <h3 class="text-sm font-semibold text-ink">Repositori Master Berkas Semua Unit</h3>
            <p class="text-sm text-muted">Klik master berkas untuk membuka daftar sub-berkas beserta aksi lihat, unduh, dan hapus.</p>
        </div>

        <div class="space-y-3 p-4">
            @forelse($groups as $group)
                @php $isUnit = in_array($group->unit, ['unit1', 'unit2', 'unit3', 'unit4'], true); @endphp
                <div x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-line bg-surface-raised badig-card">
                    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" @click="open = !open" class="badig-focus flex min-w-0 flex-1 items-center gap-3 rounded-lg px-1 py-1 text-left" :aria-expanded="open ? 'true' : 'false'">
                            <span class="material-symbols-outlined shrink-0 text-xl text-muted">folder</span>
                            <span class="min-w-0">
                                <span class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-line bg-surface px-2.5 py-1 text-[11px] font-semibold text-muted badig-badge">
                                        {{ $units[$group->unit] ?? strtoupper($group->unit) }}
                                    </span>
                                    <span class="font-mono text-[11px] font-semibold text-faint">#{{ $group->record_id }}</span>
                                    <span class="rounded-full border border-line bg-surface px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted">{{ $group->files_count }} sub-berkas</span>
                                </span>
                                <span class="mt-1.5 block truncate text-sm font-semibold text-ink">{{ $group->title !== '' ? $group->title : 'Arsip #' . $group->record_id }}</span>
                                <span class="mt-0.5 block text-[11px] text-muted">Unggahan terakhir {{ $group->latest_at?->format('d/m/Y H:i') ?? '-' }}</span>
                            </span>
                            <span class="material-symbols-outlined ml-auto shrink-0 text-base text-muted transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>

                        <div class="flex shrink-0 items-center gap-2 pl-8 sm:pl-0">
                            @if($isUnit)
                                <a href="{{ route('units.show', [$group->unit, $group->record_id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised badig-action badig-action-secondary">
                                    <span class="material-symbols-outlined text-base text-muted">visibility</span>
                                    Lihat Arsip
                                </a>
                                <a href="{{ route('units.edit', [$group->unit, $group->record_id]) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised badig-action badig-action-secondary">
                                    <span class="material-symbols-outlined text-base text-warning">edit</span>
                                    Edit
                                </a>
                            @else
                                <a href="{{ route('database-orang.show', $group->record_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised badig-action badig-action-secondary">
                                    <span class="material-symbols-outlined text-base text-muted">visibility</span>
                                    Lihat Arsip
                                </a>
                                <a href="{{ route('database-orang.edit', $group->record_id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised badig-action badig-action-secondary">
                                    <span class="material-symbols-outlined text-base text-warning">edit</span>
                                    Edit
                                </a>
                            @endif
                        </div>
                    </div>

                    <div x-show="open" x-cloak x-transition.opacity.duration.150ms class="border-t border-line bg-surface">
                        <ul class="divide-y divide-line">
                            @foreach($group->files as $file)
                                <li class="flex flex-col gap-2 p-3 pl-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="material-symbols-outlined text-base text-muted">{{ $file->is_image ? 'image' : ($file->extension === 'pdf' ? 'picture_as_pdf' : 'description') }}</span>
                                        <div class="min-w-0">
                                            <div class="break-all text-sm font-medium text-ink">{{ $file->original_name }}</div>
                                            <div class="mt-0.5 text-[10px] uppercase tracking-[0.2em] text-faint">{{ $file->extension }} &bull; {{ $file->human_file_size }} &bull; {{ $file->uploader?->name ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        @if($file->is_viewable)
                                            <a href="{{ route('pimpinan.file-manager.attachments.preview', $file->id) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-surface-raised">
                                                <span class="material-symbols-outlined text-base text-muted">visibility</span>
                                                Lihat
                                            </a>
                                        @endif
                                        <a href="{{ route('pimpinan.file-manager.attachments.download', $file->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-surface-raised">
                                            <span class="material-symbols-outlined text-base text-accent">download</span>
                                            Unduh
                                        </a>
                                        <form action="{{ route('pimpinan.file-manager.attachments.delete', $file->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus file ini?">
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
                    Tidak ditemukan riwayat berkas lampiran digital pada parameter repositori global sistem.
                </div>
            @endforelse
        </div>

        <div class="border-t border-line bg-surface-raised p-4">
            {{ $groups->links() }}
        </div>
    </div>
</div>
@endsection
