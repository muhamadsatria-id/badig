@extends('layouts.app')

@section('title', 'Backup Database')
@section('page_title', 'Backup Database')

@section('content')
<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">backup</span>
                    Manajemen sistem
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Backup Database
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Lakukan ekspor cadangan database atau impor file backup secara terkontrol dari halaman ini.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6 min-w-0 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-accent text-[#07140c]">
                    <span class="material-symbols-outlined text-xl">download</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Ekspor Database</h3>
                    <p class="text-sm text-muted">Unduh salinan database dalam format SQL.</p>
                </div>
            </div>

            <div class="mt-5">
                <a
                    href="{{ route('backup.export') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
                >
                    <span class="material-symbols-outlined text-base text-faint">database</span>
                    Export Database SQL
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--warning-soft)] text-warning">
                    <span class="material-symbols-outlined text-xl">upload</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Impor Backup</h3>
                    <p class="text-sm text-muted">Unggah file backup SQL untuk mengembalikan data.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('backup.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="database_file" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                        File Backup
                    </label>
                    <input
                        id="database_file"
                        type="file"
                        name="database_file"
                        class="w-full rounded-xl border border-line bg-surface-raised p-2.5 text-sm text-muted outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-[#07140c] hover:file:bg-accent-strong focus:border-accent focus:bg-surface badig-field"
                    >
                </div>

                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-warning px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-warning badig-action badig-action-warning">
                    <span class="material-symbols-outlined text-base">restore</span>
                    Import Backup
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
