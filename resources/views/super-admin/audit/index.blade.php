@extends('layouts.app')

@section('title', 'Audit Log')
@section('page_title', 'Audit Log')

@section('content')
<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">monitoring</span>
                    Aktivitas sistem
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Audit Log BADIG
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Rekam jejak aktivitas pengguna dan perubahan penting dalam sistem tercatat di sini.
                </p>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.24em] text-muted shadow-sm">
                <span class="material-symbols-outlined text-base text-muted">storage</span>
                Total {{ $logs->total() }}
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line">
                <thead class="bg-surface-raised">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            User
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            Aksi
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            Modul
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            IP
                        </th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            Waktu
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-line bg-surface">
                    @forelse($logs as $log)
                        <tr class="hover:bg-surface-raised">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-ink">
                                {{ $log->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-surface-raised px-2.5 py-1 text-[11px] font-semibold text-muted badig-badge">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted">
                                {{ data_get($log, 'module', '-') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">
                                {{ optional($log->created_at)->format('d M Y, H:i') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-muted">
                                Belum ada data audit log yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="border-t border-line bg-surface-raised px-4 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
