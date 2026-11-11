@extends('layouts.app')

@section('title', 'Flux Conversion')
@section('page_title', 'Flux Conversion')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">auto_awesome</span>
                Status Implementasi
            </div>
            <h2 class="text-xl font-semibold tracking-tight text-ink">BADIG sudah dikonversi ke Flux</h2>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Layout, login, reusable form, stat card, asset build, dan tema global sudah memakai Flux UI dengan tampilan yang lebih konsisten.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-accent text-[#07140c]">
                <span class="material-symbols-outlined text-base">widgets</span>
            </div>
            <h3 class="mt-4 text-base font-semibold text-ink">Flux UI</h3>
            <p class="mt-2 text-sm leading-6 text-muted">
                Komponen Blade Flux aktif melalui dukungan appearance dan script global.
            </p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-accent text-[#07140c]">
                <span class="material-symbols-outlined text-base">palette</span>
            </div>
            <h3 class="mt-4 text-base font-semibold text-ink">Tailwind v4</h3>
            <p class="mt-2 text-sm leading-6 text-muted">
                File stylesheet utama sudah mengimpor Tailwind dan vendor Flux dengan pendekatan yang lebih bersih.
            </p>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-accent text-[#07140c]">
                <span class="material-symbols-outlined text-base">shield_lock</span>
            </div>
            <h3 class="mt-4 text-base font-semibold text-ink">Backup Aman</h3>
            <p class="mt-2 text-sm leading-6 text-muted">
                File lama disimpan sebagai backup sebelum ditimpa untuk menjaga kestabilan proyek.
            </p>
        </div>
    </div>
</div>
@endsection
