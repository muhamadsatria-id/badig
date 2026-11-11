@extends('layouts.app')

@section('title', 'Arsip')
@section('page_title', 'Arsip')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between badig-card">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">folder_open</span>
                Arsip Dokumen
            </div>
            <h2 class="text-xl font-semibold tracking-tight text-ink">Daftar Arsip</h2>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Pilih arsip yang ingin diekspor menjadi PDF secara terpilih dan aman.
            </p>
        </div>

        @if($archives->isNotEmpty())
            <button
                type="submit"
                form="archive-export-form"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
            >
                <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                Export PDF Terpilih
            </button>
        @endif
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <form id="archive-export-form" method="POST" action="{{ route('archives.export') }}" onsubmit="return confirm('Export arsip terpilih ke PDF?')">
            @csrf

            <div class="overflow-x-auto">
                @if($archives->isEmpty())
                    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                        <div class="mb-3 flex size-14 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                            <span class="material-symbols-outlined text-2xl">inventory_2</span>
                        </div>
                        <h3 class="text-base font-semibold text-ink">Belum ada arsip</h3>
                        <p class="mt-1 max-w-md text-sm leading-6 text-muted">
                            Arsip yang tersedia akan muncul di sini setelah data masuk ke sistem.
                        </p>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-line text-sm">
                        <thead class="bg-surface-raised">
                            <tr>
                                <th class="w-16 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                    Pilih
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Judul</th>
                                <th class="w-56 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Tanggal File</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line bg-surface text-muted">
                            @foreach($archives as $item)
                                <tr class="transition-colors duration-150 hover:bg-surface-raised">
                                    <td class="px-4 py-3">
                                        <input
                                            id="archive-{{ $item->id }}"
                                            type="checkbox"
                                            name="ids[]"
                                            value="{{ $item->id }}"
                                            class="h-4 w-4 rounded border-line text-ink focus:ring-accent/20"
                                        >
                                    </td>
                                    <td class="px-4 py-3 font-medium text-ink">{{ $item->judul }}</td>
                                    <td class="px-4 py-3 text-sm text-muted">{{ $item->tanggal_pembuatan_file }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            @if($archives->isNotEmpty())
                <div class="border-t border-line bg-surface-raised px-4 py-4">
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
                        >
                            <span class="material-symbols-outlined text-base">picture_as_pdf</span>
                            Export PDF Terpilih
                        </button>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
