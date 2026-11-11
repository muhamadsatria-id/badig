@extends('layouts.app')

@section('title', 'Super Admin')
@section('page_title', 'Super Admin')

@section('content')
<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">admin_panel_settings</span>
                    Ruang kendali sistem
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Ruang Kendali Super Admin
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Otoritas sistem level 5 untuk pengelolaan akun, audit, backup, dan akses lintas unit.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.24em] text-muted shadow-sm">
                <span class="material-symbols-outlined text-base text-muted">shield_person</span>
                Access Level 5
            </div>
        </div>
    </div>

    <div class="mt-6 min-w-0 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Pengguna', 'value' => $stats['users'], 'icon' => 'groups'],
            ['label' => 'Super Admin', 'value' => $stats['super_admins'], 'icon' => 'shield_person'],
            ['label' => 'Audit Internal', 'value' => $stats['audit'], 'icon' => 'receipt_long'],
            ['label' => 'Backup Sistem', 'value' => $stats['backups'], 'icon' => 'database'],
        ] as $card)
            <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm badig-card">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-faint">{{ $card['label'] }}</p>
                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-ink">{{ $card['value'] }}</h3>
                    </div>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-surface-raised text-muted">
                        <span class="material-symbols-outlined text-lg">{{ $card['icon'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- <div class="mt-6 min-w-0 grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="space-y-1">
                <h3 class="text-sm font-semibold text-ink">Akses Cepat Sistem</h3>
                <p class="text-sm text-muted">Navigasi utama untuk mengelola akun, audit, backup, dan file global.</p>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm font-semibold text-muted transition hover:border-accent/30 hover:bg-surface-raised">
                    <span class="material-symbols-outlined text-base text-faint">dashboard</span>
                    Dashboard Utama
                </a>
                <a href="{{ route('pimpinan.file-manager') }}" class="flex items-center gap-2 rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm font-semibold text-muted transition hover:border-accent/30 hover:bg-surface-raised">
                    <span class="material-symbols-outlined text-base text-faint">folder_managed</span>
                    File Manager Global
                </a>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-2 rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm font-semibold text-muted transition hover:border-accent/30 hover:bg-surface-raised">
                    <span class="material-symbols-outlined text-base text-faint">block</span>
                    Status Alur Lama
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-2 rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm font-semibold text-muted transition hover:border-accent/30 hover:bg-surface-raised">
                    <span class="material-symbols-outlined text-base text-faint">manage_accounts</span>
                    Manajemen Pengguna
                </a>
                <a href="{{ route('admin.audit') }}" class="flex items-center gap-2 rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm font-semibold text-muted transition hover:border-accent/30 hover:bg-surface-raised">
                    <span class="material-symbols-outlined text-base text-faint">receipt_long</span>
                    Log Audit Trail
                </a>
                <a href="{{ route('admin.backup') }}" class="flex items-center gap-2 rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm font-semibold text-muted transition hover:border-accent/30 hover:bg-surface-raised">
                    <span class="material-symbols-outlined text-base text-faint">database</span>
                    Pencadangan Sistem
                </a>
            </div>

            <div class="mt-6 min-w-0 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(['unit1' => 'Ideologi Politik', 'unit2' => 'Ekonomi', 'unit3' => 'Sosial Budaya', 'unit4' => 'Keamanan Negara'] as $key => $label)
                    <a href="{{ route('units.index', $key) }}" class="rounded-xl bg-accent px-3 py-3 text-center text-[#07140c] transition hover:bg-accent-strong">
                        <div class="text-[10px] font-semibold uppercase tracking-[0.24em] text-faint">{{ $label }}</div>
                        <div class="mt-1 text-base font-semibold">{{ $stats[$key] }}</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
            <div class="space-y-1">
                <h3 class="text-sm font-semibold text-ink">Aktivitas Privilege</h3>
                <p class="text-sm text-muted">Jejak log mutasi sistem internal level root.</p>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($superLogs as $log)
                    <div class="rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm">
                        <div class="flex items-center justify-between gap-3 text-[11px] text-faint">
                            <span>{{ $log->created_at?->format('d/m/Y H:i') }}</span>
                            <span class="truncate max-w-[120px]">{{ $log->user?->email }}</span>
                        </div>
                        <div class="mt-1 font-semibold text-ink">{{ $log->action }}</div>
                        <p class="mt-1 text-sm leading-6 text-muted">{{ $log->description }}</p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-line bg-surface px-3 py-8 text-center text-sm text-muted">
                        Belum ada aktivitas mutasi yang terekam.
                    </div>
                @endforelse
            </div>
        </div>
    </div> -->

    <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="border-b border-line bg-surface-raised px-5 py-4">
            <h3 class="text-sm font-semibold text-ink">Daftar Akun Sistem Komplit</h3>
            <p class="mt-1 text-sm text-muted">Menampilkan seluruh pengguna terdaftar yang memiliki akses sistem.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm">
                <thead class="bg-surface-raised text-muted">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Nama Personel</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Alamat Email</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Tingkat Otoritas</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Status Akses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-surface">
                    @foreach($latestUsers as $user)
                        <tr class="hover:bg-surface-raised">
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-ink">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm text-muted">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $user->isSuperAdmin() ? 'bg-accent text-[var(--accent-contrast)]' : 'bg-surface-raised text-muted' }}">
                                    {{ $user->roleName() }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $user->is_locked ? 'bg-[var(--danger-soft)] text-danger' : 'bg-[var(--accent-soft)] text-accent' }}">
                                    {{ $user->is_locked ? 'Suspended / Locked' : 'Active / Granted' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection