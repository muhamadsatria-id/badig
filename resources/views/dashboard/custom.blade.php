@extends('layouts.app')

@section('title', 'Dashboard Khusus')
@section('page_title', 'Dashboard Khusus')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:flex-row sm:items-start sm:justify-between lg:items-center badig-card">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">analytics</span>
                Ringkasan Sistem
            </div>
            <h2 class="text-xl font-semibold tracking-tight text-ink">Dashboard Khusus</h2>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Ikhtisar cepat mengenai jumlah pengguna, unit, arsip, dan file yang terdaftar dalam sistem.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Total User</p>
            <div class="mt-3 flex items-end justify-between">
                <span class="text-3xl font-semibold tracking-tight text-ink">{{ $total_user }}</span>
                <span class="material-symbols-outlined text-lg text-faint">group</span>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Total Unit</p>
            <div class="mt-3 flex items-end justify-between">
                <span class="text-3xl font-semibold tracking-tight text-ink">{{ $total_unit }}</span>
                <span class="material-symbols-outlined text-lg text-faint">apartment</span>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Total Arsip</p>
            <div class="mt-3 flex items-end justify-between">
                <span class="text-3xl font-semibold tracking-tight text-ink">{{ $total_archive }}</span>
                <span class="material-symbols-outlined text-lg text-faint">folder_open</span>
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Total File</p>
            <div class="mt-3 flex items-end justify-between">
                <span class="text-3xl font-semibold tracking-tight text-ink">{{ $total_file }}</span>
                <span class="material-symbols-outlined text-lg text-faint">description</span>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="border-b border-line bg-surface-raised p-4">
            <h3 class="text-sm font-semibold text-ink">File Per Unit</h3>
            <p class="text-[11px] text-muted">Distribusi jumlah file berdasarkan unit yang terdaftar.</p>
        </div>

        @if($unit_files->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                    <span class="material-symbols-outlined text-2xl">inventory_2</span>
                </div>
                <h4 class="text-base font-semibold text-ink">Belum ada data per unit</h4>
                <p class="mt-1 max-w-md text-sm leading-6 text-muted">
                    Informasi distribusi file akan muncul di sini setelah data unit tersedia.
                </p>
            </div>
        @else
            <div class="divide-y divide-line">
                @foreach($unit_files as $item)
                    <div class="flex items-center justify-between px-4 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex size-9 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                                <span class="material-symbols-outlined text-base">apartment</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink">{{ $item->unit->nama_unit ?? '-' }}</p>
                                <p class="text-[11px] text-muted">Jumlah file terdaftar</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-surface-raised px-2.5 py-1 text-sm font-semibold text-muted badig-badge">
                            {{ $item->total }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection