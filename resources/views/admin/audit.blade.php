@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page_title', 'Audit Trail')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm md:flex-row md:items-center md:justify-between badig-card">
        <div class="space-y-1">
            <!-- <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">security</span>
                Audit Trail
            </div> -->
            <h2 class="text-2xl font-semibold tracking-tight text-ink">Log Keamanan Sistem</h2>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Menampilkan jejak aktivitas pengguna dan perubahan penting dalam sistem secara terstruktur dan mudah diaudit.
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-danger/20 bg-[var(--danger-soft)] px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-danger">
            <span class="size-2.5 rounded-full bg-danger animate-pulse"></span>
            Mode Audit Aktif
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="flex flex-col gap-3 border-b border-line px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <!-- <div class="flex size-9 items-center justify-center rounded-xl bg-surface-raised text-muted">
                    <span class="material-symbols-outlined text-lg">history_toggle_off</span>
                </div> -->
                <div>
                    <h3 class="text-base font-semibold text-ink">Jejak Aktivitas Terenkripsi</h3>
                    <p class="text-sm text-muted">Riwayat operasi pengguna yang tersimpan secara aman.</p>
                </div>
            </div>
            <!-- <div class="inline-flex items-center gap-2 rounded-full border border-line bg-surface-raised px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.22em] text-muted">
                <span class="material-symbols-outlined text-sm">search</span>
                Pencarian Otomatis
            </div> -->
        </div>

        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                    <span class="material-symbols-outlined text-2xl">inventory_2</span>
                </div>
                <h4 class="text-base font-semibold text-ink">Belum ada data audit</h4>
                <p class="mt-1 max-w-md text-sm text-muted">
                    Aktivitas sistem akan muncul di sini ketika ada log yang tercatat.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line text-sm">
                    <thead class="bg-surface-raised">
                        <tr>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Waktu</th>
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Pengguna</th>
                            
                            <th class="whitespace-nowrap px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Alamat IP</th>
                            
                            <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Aksi Operasi</th>
                            
                            <th class="w-full px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Deskripsi Log</th>
                            <!-- <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Deskripsi Log</th> -->
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line bg-surface text-muted">
                        @foreach($logs as $log)
                            @php
                                $isAlert = str_contains($log->action, 'FAILED') || str_contains($log->action, 'ILLEGAL');
                            @endphp
                            <tr class="transition-colors duration-150 {{ $isAlert ? 'bg-[var(--danger-soft)] hover:bg-[var(--danger-soft)]/70' : 'hover:bg-surface-raised' }}">
                                <td class="whitespace-nowrap px-5 py-4 text-xs font-mono {{ $isAlert ? 'font-semibold text-danger' : 'text-muted' }}">
                                    {{ $log->created_at?->format('d/m/Y H:i:s') }}
                                </td>

                                <td class="px-5 py-4">
                                    @if($log->user?->name)
                                        <div class="flex items-center gap-2.5">
                                            <!-- <div class="flex size-8 items-center justify-center rounded-xl bg-surface-raised text-xs font-semibold text-muted">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div> -->
                                            <span class="font-medium text-ink">{{ $log->user->name }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex size-8 items-center justify-center rounded-xl bg-[var(--accent-soft)] text-accent">
                                                <span class="material-symbols-outlined text-sm">smart_toy</span>
                                            </div>
                                            <span class="text-sm font-medium uppercase tracking-[0.16em] text-accent">Sistem</span>
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-6 text-xs font-mono text-muted">
                                    {{ $log->ip_address }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if($isAlert)
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-danger">
                                            <span class="material-symbols-outlined text-sm">warning</span>
                                            {{ $log->action }}
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-black">
                                            <!-- <span class="material-symbols-outlined text-sm">check_circle</span> -->
                                            {{ $log->action }}
                                        </div>
                                    @endif
                                </td>

                                <td class="w-full min-w-[300px] px-5 py-3.5 text-sm leading-6 {{ $isAlert ? 'font-medium text-danger' : 'text-muted' }}">
                                    {{ $log->description }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($logs->hasPages())
            <div class="border-t border-line bg-surface-raised px-5 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
