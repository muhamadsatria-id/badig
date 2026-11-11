@extends('layouts.app')

@section('title', 'Master Unit')
@section('page_title', 'Master Unit')

@section('content')
<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">account_tree</span>
                    Struktur organisasi
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Master Unit
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Kelola daftar unit secara konsisten untuk relasi pengguna, data orang, dan pemisahan akses informasi.
                </p>
            </div>

            <div class="rounded-xl border border-line bg-surface px-3 py-2 text-xs text-muted">Struktur tetap: tepat empat unit.</div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm">
                <thead class="bg-surface-raised text-muted">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">ID</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Nama Unit</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Kode</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em]">Slug</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.24em]">User</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.24em]">Data Orang</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.24em]">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.24em]">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-surface">
                    @forelse($units as $unit)
                        <tr class="hover:bg-surface-raised">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-sm text-muted">{{ $unit->id }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-ink">{{ $unit->nama_unit }}</div>
                                <div class="mt-1 text-xs leading-5 text-muted">
                                    {{ \Illuminate\Support\Str::limit($unit->deskripsi, 80) }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-muted">{{ $unit->kode }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-muted">{{ $unit->slug }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-muted">{{ $unit->users_count }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-muted">{{ $unit->database_orang_count }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $unit->is_active ? 'bg-[var(--accent-soft)] text-accent' : 'bg-surface-raised text-muted' }}">
                                    {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a
                                        href="{{ route('super-admin.units.edit', $unit) }}"
                                        class="text-sm font-semibold text-muted transition hover:text-ink"
                                    >
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-muted">
                                Belum ada unit yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($units->hasPages())
            <div class="border-t border-line bg-surface-raised px-4 py-4">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
