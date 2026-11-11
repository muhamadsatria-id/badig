@extends('layouts.guest')

@section('title', 'Gerbang Autentikasi')

@section('content')
@php($otpRequired = $otpRequired ?? false)

<div class="grid min-h-screen lg:grid-cols-2">
    <section class="relative hidden overflow-hidden border-r border-white/6 bg-[#0b0e0c] px-10 py-12 text-white lg:flex lg:items-center xl:px-16">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(57,255,136,.13),transparent_28rem),linear-gradient(135deg,#090c0a_0%,#121713_100%)]"></div>
        <div class="badig-grid-pattern absolute inset-0 opacity-[0.08]"></div>
        <div class="absolute -bottom-32 -right-20 size-[30rem] rounded-full border border-accent/10"></div>
        <div class="absolute -bottom-20 -right-8 size-[22rem] rounded-full border border-accent/10"></div>

        <div class="relative z-10 max-w-xl">
            <div class="mb-12 flex items-center gap-3">
                <!-- <span class="relative flex size-12 items-center justify-center rounded-2xl border border-white/10 bg-surface/6 text-base font-black tracking-tight">
                    
                    <span class="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-[#0b0e0c] bg-accent"></span>
                </span> -->
                <img
                            src="{{ asset('logo-badig.webp') }}"
                            alt="Logo BADIG"
                            class="size-16 object-contain"
                        >
                <div>
                    <p class="text-sm font-extrabold tracking-[0.16em]">BADIG</p>
                    <p class="mt-1 text-[9px] font-semibold uppercase tracking-[0.18em] text-white/40">Sat Intelkam</p>
                </div>
            </div>

            <x-ui.badge color="success" dot>Secure Intelligence Workspace</x-ui.badge>

            <h1 class="mt-6 max-w-lg text-4xl font-bold leading-tight tracking-[-0.045em] xl:text-5xl">
                Basis data intelijen yang terpusat dan terkendali.
            </h1>

            <p class="mt-6 max-w-lg text-sm leading-7 text-white/55">
                Kodifikasi data taktis, pengelolaan arsip, dan analisis sektoral dalam satu ruang kerja terintegrasi untuk mendukung deteksi dini serta pencegahan gangguan Kamtibmas.
            </p>
        </div>
    </section>

    <section class="relative flex min-h-screen items-center justify-center px-5 py-16 sm:px-8">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_70%_12%,var(--accent-soft),transparent_22rem)]"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-8 lg:hidden">
                <div class="flex items-center gap-3">
                    <!-- <span class="relative flex size-11 items-center justify-center rounded-xl border border-line bg-surface text-sm font-black text-ink shadow-sm">
                        B
                        <span class="absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-surface bg-accent"></span>
                    </span>
                    <div>
                        <p class="text-sm font-extrabold tracking-[0.14em] text-ink">BADIG</p>
                        <p class="mt-0.5 text-[8px] font-semibold uppercase tracking-[0.16em] text-muted">Sat Intelkam</p>
                    </div> -->
                </div>
            </div>

            <x-ui.card class="shadow-[var(--shadow-soft)]" :padding="false">
                <div class="p-6 sm:p-8">
                    <div>
                        <x-ui.badge color="success" dot>{{ $otpRequired ? 'OTP Verification' : 'Restricted Access' }}</x-ui.badge>
                        <h2 class="mt-5 text-2xl font-bold tracking-[-0.035em] text-ink">
                            {{ $otpRequired ? 'Verifikasi OTP' : 'Masuk ke BADIG' }}
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-muted">
                            {{ $otpRequired
                                ? 'Masukkan kode OTP enam digit untuk menyelesaikan autentikasi.'
                                : 'Gunakan akun internal yang telah terdaftar untuk mengakses sistem.' }}
                        </p>
                    </div>

                    @if(session('otp_notice'))
                        <x-ui.alert variant="warning" class="mt-6">{{ session('otp_notice') }}</x-ui.alert>
                    @endif

                    @if($errors->any())
                        <x-ui.alert variant="error" class="mt-6">{{ $errors->first() }}</x-ui.alert>
                    @endif

                    <form method="POST" action="{{ route('login.process') }}" class="mt-7 space-y-5">
                        @csrf

                        @if($otpRequired)
                            <x-ui.input
                                name="otp"
                                label="Kode OTP 6 Digit"
                                maxlength="6"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                placeholder="000000"
                                autocomplete="one-time-code"
                                class="text-center text-lg font-bold tracking-[0.42em]"
                                required
                                autofocus
                            />

                            <x-ui.button type="submit" variant="primary" class="w-full">
                                Verifikasi Akses
                                <flux:icon name="arrow-right" class="size-4" />
                            </x-ui.button>

                            <x-ui.button :href="route('login')" variant="ghost" class="w-full">
                                <flux:icon name="arrow-left" class="size-4" />
                                Kembali ke Login
                            </x-ui.button>
                        @else
                            <x-ui.input
                                name="email"
                                type="email"
                                label="Email"
                                :value="old('email')"
                                placeholder="nama@badig.local"
                                autocomplete="username"
                                required
                                autofocus
                            />

                            <x-ui.input
                                name="password"
                                type="password"
                                label="Kata Sandi"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                :revealable="true"
                                required
                            />

                            <x-ui.button
    type="submit"
    variant="primary"
    class="flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-accent-border bg-accent px-4 text-sm font-semibold text-accent-contrast shadow-sm transition hover:bg-accent-strong focus:outline-none focus:ring-2 focus:ring-accent/30 disabled:cursor-not-allowed disabled:opacity-50"
>
    <span>Lanjutkan Keamanan</span>

    <flux:icon
        name="arrow-right"
        class="size-4 shrink-0"
    />
</x-ui.button>
                        @endif
                    </form>
                </div>

                <div class="border-t border-line bg-surface-raised px-6 py-4 sm:px-8">
                    <p class="text-center text-[9px] font-medium uppercase leading-5 tracking-[0.12em] text-muted">
                        Akses terbatas. Seluruh aktivitas autentikasi dicatat untuk audit internal.
                    </p>
                </div>
            </x-ui.card>
        </div>
    </section>
</div>
@endsection
