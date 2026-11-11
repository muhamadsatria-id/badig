@extends('layouts.app')

@section('title', 'Alur Laporan Dinonaktifkan')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="border-b border-line bg-gradient-to-br from-surface-raised via-surface to-surface px-6 py-6 sm:px-8">
            <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-start sm:text-left">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-warning/20 bg-[var(--warning-soft)] text-warning shadow-sm">
                    <span class="material-symbols-outlined text-2xl">gpp_maybe</span>
                </div>

                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-warning/20 bg-surface px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-warning">
                        <span class="material-symbols-outlined text-base">info</span>
                        Informasi sistem
                    </div>
                    <h2 class="text-xl font-semibold tracking-tight text-ink">
                        Alur Laporan Lama Telah Dinonaktifkan
                    </h2>
                    <p class="text-sm leading-6 text-muted">
                        Tahapan penanganan berkas berbasis <span class="font-medium italic text-muted">Draft, Diajukan, Disetujui, dan Ditolak</span> tidak lagi digunakan. Mekanisme pencatatan basis data arsip kini diwajibkan menggunakan parameter input langsung identitas personel pelapor.
                    </p>
                </div>
            </div>
        </div>

        <div class="px-6 py-6 sm:px-8">
            <div class="rounded-2xl border border-line bg-surface-raised p-4 badig-card">
                <p class="text-sm leading-7 text-muted">
                    Pengisian data harus menggunakan informasi resmi pelapor, terutama <span class="font-semibold text-ink">Nama resmi & NRP</span>, agar pencatatan arsip tetap konsisten dan akurat.
                </p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted">
                    Kembali ke dashboard untuk melanjutkan pekerjaan Anda.
                </p>

                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
                >
                    <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection