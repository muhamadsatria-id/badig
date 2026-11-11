@extends('layouts.guest')

@section('title', 'Kebijakan Privasi dan Penggunaan Data')
@section('meta_description', 'Informasi ringkas mengenai pemrosesan data, cookie esensial, keamanan akses, dan hak pengguna pada aplikasi internal BADIG.')

@section('content')
<main class="mx-auto min-h-screen max-w-3xl px-6 py-16 text-ink">
    <div class="rounded-2xl border border-line bg-surface p-6 shadow-sm sm:p-8">
        <h1 class="text-2xl font-semibold">Kebijakan Privasi dan Penggunaan Data</h1>
        <div class="mt-6 space-y-4 text-sm leading-7 text-muted">
            <p>BADIG merupakan aplikasi internal. Sistem memproses data akun, aktivitas akses, data operasional per-unit, dan dokumen yang diunggah sesuai kewenangan pengguna.</p>
            <p>Cookie sesi dan token keamanan digunakan untuk autentikasi, pencegahan pemalsuan permintaan, serta menjaga sesi pengguna. Cookie non-esensial tidak boleh diaktifkan tanpa dasar pemrosesan dan konfigurasi persetujuan yang sesuai.</p>
            <p>Akses data dibatasi berdasarkan role dan unit. Aktivitas penting dapat dicatat pada audit log untuk kebutuhan keamanan, penelusuran insiden, dan akuntabilitas.</p>
            <p>Permintaan koreksi, pembatasan, atau penghapusan data harus disampaikan kepada pengelola sistem yang ditunjuk oleh organisasi. Kebijakan ini perlu disesuaikan kembali dengan proses bisnis, dasar hukum, periode retensi, dan penanggung jawab perlindungan data organisasi.</p>
        </div>
        <a href="{{ route('login') }}" class="mt-8 inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-[#07140c]">Kembali ke login</a>
    </div>
</main>
@endsection
