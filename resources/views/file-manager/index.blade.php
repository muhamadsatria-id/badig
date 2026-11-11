@extends('layouts.app')

@section('title', 'File Manager')
@section('page_title', 'File Manager')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-accent">folder_open</span>
                    Manajemen Dokumen
                </div>
                <h2 class="text-xl font-semibold tracking-tight text-ink">File Manager</h2>
                <p class="max-w-2xl text-sm leading-6 text-muted">
                    Kelola dokumen yang tersimpan untuk kebutuhan operasional dan pelaporan.
                </p>
            </div>

            <form method="GET" class="flex w-full max-w-md flex-col gap-3 sm:flex-row">
                <label for="search" class="sr-only">Cari file</label>
                <input id="search" name="search" value="{{ request('search') }}" placeholder="Cari file" class="h-11 flex-1 rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary">
                    <span class="material-symbols-outlined text-base">search</span>
                    Cari
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
        <form method="POST" action="{{ route('files.upload') }}" enctype="multipart/form-data" class="flex flex-col gap-3 rounded-2xl border border-line bg-surface-raised p-4 sm:flex-row sm:items-center sm:justify-between badig-card">
            @csrf

            <div class="flex-1">
                <label for="file" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Unggah Dokumen</label>
                <input id="file" type="file" name="file" class="mt-2 block w-full rounded-xl border border-line bg-surface px-3 py-2.5 text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-accent file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-[#07140c] hover:file:bg-accent-strong badig-field">
            </div>

            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary">
                <span class="material-symbols-outlined text-base">upload</span>
                Upload
            </button>
        </form>

        <div class="mt-6 overflow-hidden rounded-2xl border border-line">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line text-sm">
                    <thead class="bg-surface-raised">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Nama</th>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line bg-surface">
                        @forelse($files as $file)
                            <tr class="hover:bg-surface-raised">
                                <td class="px-4 py-3 text-muted">{{ $file->nama_file }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('files.download', $file->id) }}" class="inline-flex items-center gap-2 rounded-lg border border-line bg-surface px-3 py-1.5 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary">
                                        <span class="material-symbols-outlined text-base">download</span>
                                        Download
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-10 text-center text-sm text-muted">
                                    Belum ada file yang tersedia.
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
