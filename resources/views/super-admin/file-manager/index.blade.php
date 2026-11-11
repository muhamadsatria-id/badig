@extends('layouts.app')

@section('title', 'Master File Manager')
@section('page_title', 'Master File Manager')

@section('content')
<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">folder_open</span>
                    File keseluruhan
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">
                    Master File Manager
                </h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Lihat dan filter berkas yang tersimpan berdasarkan unit kerja untuk keperluan pemantauan dan administrasi.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-xs">
                <label for="unit_id" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    Filter Unit
                </label>
                <select
                    id="unit_id"
                    name="unit_id"
                    class="mt-1 h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field"
                >
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->nama_unit }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
            >
                <span class="material-symbols-outlined text-base text-faint">filter_alt</span>
                Filter
            </button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-line">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line">
                    <thead class="bg-surface-raised">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                File
                            </th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Unit
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line bg-surface">
                        @forelse($files as $file)
                            <tr class="hover:bg-surface-raised">
                                <td class="px-4 py-3 text-sm font-medium text-ink">
                                    {{ $file->nama_file }}
                                </td>
                                <td class="px-4 py-3 text-sm text-muted">
                                    {{ $file->unit->nama_unit ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-10 text-center text-sm text-muted">
                                    Belum ada file yang tersedia untuk filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
