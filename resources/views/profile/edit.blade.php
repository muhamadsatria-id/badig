@extends('layouts.app')

@section('title', 'Setting Profil')
@section('page_title', 'Setting Profil')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-accent">account_circle</span>
                    Pengaturan Akun
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Pengaturan Profil Pengguna
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Perbarui identitas, kontak, alamat, foto profil, email login, dan kata sandi dengan tampilan yang konsisten.
                </p>
            </div>

            <div class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full border border-line bg-surface px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.24em] text-muted shadow-sm">
                <span class="material-symbols-outlined text-base text-muted">verified_user</span>
                {{ $user->roleName() }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <aside class="h-fit rounded-2xl border border-line bg-surface p-5 shadow-sm">
            <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-accent text-xl font-bold text-[#07140c] shadow-sm">
                    @if(!empty($user->photo))
                        <img
                            src="{{ route('profile.photo') }}"
                            alt="Foto Profil"
                            class="h-full w-full object-cover"
                        >
                    @else
                        {{ strtoupper(substr($user->name ?: 'US', 0, 2)) }}
                    @endif
                </div>

                <div class="mt-4 w-full space-y-1">
                    <h3 class="truncate text-base font-semibold text-ink">
                        {{ $user->name }}
                    </h3>
                    <p class="truncate font-mono text-xs text-faint">
                        {{ $user->email }}
                    </p>
                    <p class="truncate text-xs text-muted">
                        NRP: {{ $user->nrp ?: '-' }}
                    </p>
                </div>

                <div class="mt-5 w-full space-y-3 border-t border-line pt-4 text-xs">
                    <div class="flex items-start justify-between gap-3">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-faint">
                            Tingkat Akses
                        </span>
                        <span class="text-right font-semibold text-ink">
                            {{ $user->roleName() }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-faint">
                            Unit Kerja
                        </span>
                        <span class="text-right font-semibold text-ink">
                            {{ $user->unitName() }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.24em] text-faint">
                            No HP
                        </span>
                        <span class="text-right font-semibold text-ink">
                            {{ $user->phone ?: '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>

        <form
            method="POST"
            action="{{ route('profile.update') }}"
            enctype="multipart/form-data"
            class="grid grid-cols-1 gap-4 rounded-2xl border border-line bg-surface p-5 shadow-sm sm:grid-cols-2 lg:col-span-2 badig-card"
        >
            @csrf
            @method('PATCH')

            <div class="sm:col-span-2 border-b border-line pb-3">
                <h4 class="text-sm font-semibold text-ink">
                    Informasi Identitas Personel
                </h4>
                <p class="mt-1 text-[11px] leading-5 text-muted">
                    Perubahan profil langsung aktif setelah disimpan.
                </p>
            </div>

            <div class="space-y-1.5">
                <label for="name" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Nama Lengkap Personel
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                        badge
                    </span>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    >
                </div>
                @error('name')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="email" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Email Login Resmi
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                        mail
                    </span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    >
                </div>
                @error('email')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="nrp" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    NRP
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                        pin
                    </span>
                    <input
                        id="nrp"
                        type="text"
                        name="nrp"
                        value="{{ old('nrp', $user->nrp) }}"
                        class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    >
                </div>
                @error('nrp')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="phone" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    No HP
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                        call
                    </span>
                    <input
                        id="phone"
                        type="text"
                        name="phone"
                        value="{{ old('phone', $user->phone) }}"
                        class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    >
                </div>
                @error('phone')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <label for="address" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Alamat
                </label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="w-full rounded-xl border border-line bg-surface-raised px-3 py-2.5 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field"
                >{{ old('address', $user->address) }}</textarea>
                @error('address')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <label for="photo" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Foto Profil
                </label>
                <input
                    id="photo"
                    type="file"
                    name="photo"
                    accept="image/jpeg,image/png,image/webp"
                    class="w-full rounded-xl border border-line bg-surface-raised p-2 text-sm text-muted outline-none transition file:mr-3 file:rounded-lg file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-[#07140c] hover:file:bg-accent-strong focus:border-accent focus:bg-surface badig-field"
                >
                <p class="text-[10px] text-faint">
                    Format JPG, PNG, atau WebP. Maksimal 2 MB.
                </p>
                @error('photo')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2 border-t border-line pt-4">
                <h4 class="text-sm font-semibold text-ink">
                    Pengamanan Kata Sandi
                </h4>
                <p class="mt-1 text-[11px] leading-5 text-muted">
                    Gunakan minimal 12 karakter dengan huruf besar, huruf kecil, dan angka. Kata sandi saat ini wajib saat email atau kata sandi diubah.
                </p>
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <label for="current_password" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Kata Sandi Saat Ini
                </label>
                <input
                    id="current_password"
                    type="password"
                    name="current_password"
                    autocomplete="current-password"
                    placeholder="Wajib saat mengubah email atau kata sandi"
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field"
                >
                @error('current_password')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Kata Sandi Baru
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                        lock
                    </span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Kosongkan jika tidak diganti"
                        class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field"
                    >
                </div>
                @error('password')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Konfirmasi Kata Sandi Baru
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                        lock_reset
                    </span>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field"
                    >
                </div>
                @error('password_confirmation')
                    <p class="text-[11px] font-medium text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2 border-t border-line pt-4 sm:col-span-2 sm:flex-row sm:items-center">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
                >
                    <span class="material-symbols-outlined text-base">save</span>
                    Simpan Perubahan Profil
                </button>

                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-5 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary"
                >
                    <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection