@extends('layouts.app')

@section('title', 'Hasil Pencarian')
@section('page_title', 'Hasil Pencarian')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">search</span>
                    Pencarian global
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Hasil Pencarian BADIG
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Temukan arsip, file dokumen, dan data personel yang sesuai dengan kata kunci pencarian Anda.
                </p>
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-4 py-2 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary"
            >
                <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                Kembali
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Arsip</h3>
                    <p class="text-xs text-muted">Data arsip yang cocok</p>
                </div>
                <span class="rounded-full bg-surface-raised px-3 py-1 text-xs font-semibold text-muted badig-badge">
                    {{ $archives->count() }}
                </span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($archives as $item)
                    <div class="rounded-xl border border-line bg-surface-raised px-4 py-3">
                        <p class="text-sm font-medium text-ink">
                            {{ $item->judul }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-line bg-surface px-4 py-6 text-center text-sm text-muted">
                        Tidak ada arsip yang cocok.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-ink">File</h3>
                    <p class="text-xs text-muted">Dokumen terunggah</p>
                </div>
                <span class="rounded-full bg-surface-raised px-3 py-1 text-xs font-semibold text-muted badig-badge">
                    {{ $files->count() }}
                </span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($files as $item)
                    <div class="rounded-xl border border-line bg-surface-raised px-4 py-3">
                        <p class="text-sm font-medium text-ink">
                            {{ $item->nama_file }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-line bg-surface px-4 py-6 text-center text-sm text-muted">
                        Tidak ada file yang cocok.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Data Orang</h3>
                    <p class="text-xs text-muted">Data orang terdaftar</p>
                </div>
                <span class="rounded-full bg-surface-raised px-3 py-1 text-xs font-semibold text-muted badig-badge">
                    {{ $people->count() }}
                </span>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($people as $item)
                    <div class="rounded-xl border border-line bg-surface-raised px-4 py-3">
                        <p class="text-sm font-medium text-ink">
                            {{ $item->nama }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-line bg-surface px-4 py-6 text-center text-sm text-muted">
                        Tidak ada data orang yang cocok.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
