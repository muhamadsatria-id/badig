@extends('layouts.app')

@section('title', 'Detail ' . $module['title'])

@section('content')
<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div class="max-w-2xl">
        <div class="inline-flex items-center rounded-full border border-line bg-surface px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-muted shadow-sm">
            Detail Rekam Arsip
        </div>
        <h2 class="mt-3 text-xl font-semibold tracking-tight text-ink">Ringkasan dokumen intelijen</h2>
        <p class="mt-1 text-sm text-muted">Peninjauan menyeluruh data arsip, wilayah operasional, dan berkas lampiran yang terkait.</p>
    </div>

    <a href="{{ route('units.pdf', [$unit, $item->id]) }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-danger/20 bg-[var(--danger-soft)] px-4 py-2 text-xs font-semibold text-danger shadow-sm transition-colors hover:bg-[var(--danger-soft)] sm:w-auto badig-action badig-action-danger">
        <span class="material-symbols-outlined text-base">picture_as_pdf</span>
        Buka Dokumen PDF
    </a>
</div>

<div class="mt-6 min-w-0 grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_320px]">
    <div class="rounded-2xl border border-line bg-surface p-5 shadow-sm badig-card">
        <div class="flex flex-col gap-4 border-b border-line pb-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-faint">Subjek Utama</p>
                <h3 class="mt-1 text-lg font-semibold leading-snug text-ink">{{ $item->{$module['main']} }}</h3>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-line bg-surface-raised px-2.5 py-1 text-[11px] font-semibold text-muted badig-badge">{{ $item->kategori }}</span>
                <span class="rounded-full border border-line bg-surface px-2.5 py-1 text-[11px] font-semibold text-muted badig-badge">{{ optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-' }}</span>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-3.5 sm:grid-cols-2">
            @foreach($module['fields'] as $field)
                @php
                    $isFullWidth = str_contains($field, 'catatan') || str_contains($field, 'modus') || str_contains($field, 'data') || str_contains($field, 'alamat');
                @endphp

                <div class="rounded-xl border border-line bg-surface-raised p-3.5 {{ $isFullWidth ? 'sm:col-span-2' : '' }}">
                    <dt class="mb-1 text-[10px] font-bold uppercase tracking-[0.22em] text-faint">{{ ucwords(str_replace('_', ' ', $field)) }}</dt>
                    <dd class="whitespace-pre-line break-words text-xs font-semibold leading-relaxed text-ink">
                        @if($field === 'district_id')
                            {{ $item->district?->name ?? '-' }}
                        @elseif($field === 'village_id')
                            {{ $item->village?->name ?? '-' }}
                        @elseif($field === 'tanggal_pembuatan')
                            <span class="font-mono">{{ optional($item->tanggal_pembuatan)->format('d/m/Y') ?? '-' }}</span>
                        @else
                            {{ $item->{$field} ?? '-' }}
                        @endif
                    </dd>
                </div>
            @endforeach

            <div class="rounded-xl border border-line bg-surface-raised p-3.5">
                <dt class="mb-1 text-[10px] font-bold uppercase tracking-[0.22em] text-faint">Operator Penanggung Jawab</dt>
                <dd class="text-xs font-semibold text-ink">{{ $item->operator?->name ?? '-' }}</dd>
            </div>
        </div>

        <!-- Lampiran Dokumen Pendukung Table Section -->
        <div class="mt-6 border-t border-line pt-5">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-accent">attach_file</span>
                    <h4 class="text-xs font-bold uppercase tracking-[0.2em] text-ink">Lampiran Dokumen Pendukung ({{ $attachments->count() }})</h4>
                </div>
            </div>

            @if($attachments->isNotEmpty())
                <div class="overflow-x-auto rounded-xl border border-line bg-surface">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-line bg-surface-raised text-[10px] font-bold uppercase tracking-[0.2em] text-faint">
                            <tr>
                                <th class="px-3 py-2.5 text-center w-10">No</th>
                                <th class="px-3 py-2.5">Pratinjau / Tipe</th>
                                <th class="px-3 py-2.5">Nama Berkas Lampiran</th>
                                <th class="px-3 py-2.5 text-center">Ukuran</th>
                                <th class="px-3 py-2.5 text-center">Waktu Unggah</th>
                                <th class="px-3 py-2.5 text-right">Aksi Download</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-ink">
                            @foreach($attachments as $attachment)
                                <tr class="hover:bg-surface-raised/50 transition-colors">
                                    <td class="px-3 py-2.5 text-center font-mono text-faint text-[11px]">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2.5">
                                        @if($attachment->is_image)
                                            <div class="h-10 w-10 overflow-hidden rounded-lg border border-line bg-surface-raised">
                                                <img src="{{ route('units.attachments.preview', [$unit, $attachment->id]) }}" alt="{{ $attachment->original_name }}" class="h-full w-full object-cover">
                                            </div>
                                        @else
                                            <span class="inline-flex items-center justify-center rounded-lg border border-line bg-surface-raised px-2 py-1 font-mono text-[10px] font-bold uppercase text-accent">
                                                {{ $attachment->extension ?: 'FILE' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 font-semibold">
                                        <div class="truncate max-w-xs" title="{{ $attachment->original_name }}">
                                            {{ $attachment->original_name }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono text-faint text-[11px]">
                                        {{ $attachment->human_file_size }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-mono text-faint text-[11px]">
                                        {{ optional($attachment->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                        <a href="{{ route('units.attachments.download', [$unit, $attachment->id]) }}" class="inline-flex items-center gap-1 rounded-lg border border-line bg-surface px-2.5 py-1.5 text-[11px] font-semibold text-ink shadow-sm transition-colors hover:bg-surface-raised hover:text-accent">
                                            <span class="material-symbols-outlined text-sm">download</span>
                                            Unduh Berkas
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-line bg-surface-raised px-4 py-6 text-center text-xs text-muted">
                    Belum ada lampiran dokumen fisik yang diunggah untuk data ini.
                </div>
            @endif
        </div>

        <div class="mt-5 flex flex-col gap-2 border-t border-line pt-5 sm:flex-row">
            <a href="{{ route('units.index', $unit) }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-line bg-surface px-4 py-2 text-xs font-semibold text-muted shadow-sm transition-colors hover:bg-surface-raised sm:w-auto badig-action badig-action-secondary">
                <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                Kembali Ke Daftar
            </a>

            @if(auth()->user()->canWriteUnitId((int) $module['id']))
                <a href="{{ route('units.edit', [$unit, $item->id]) }}" class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-xs font-semibold text-[#07140c] shadow-sm transition-colors hover:bg-accent-strong sm:w-auto badig-action badig-action-primary">
                    <span class="material-symbols-outlined text-base">edit</span>
                    Ubah Atribut Berkas
                </a>
            @endif
        </div>
    </div>

    <aside class="xl:sticky xl:top-20">
        <div class="rounded-2xl border border-line bg-surface p-4 shadow-sm badig-card">
            <div class="flex items-center gap-2 border-b border-line pb-3">
                <span class="material-symbols-outlined text-base text-faint">attach_file</span>
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted">Lampiran Dokumen</h3>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($attachments as $attachment)
                    <div class="rounded-xl border border-line bg-surface-raised p-3">
                        @if($attachment->is_image)
                            <div class="mb-2 h-28 w-full overflow-hidden rounded-lg border border-line bg-surface-raised">
                                <img src="{{ route('units.attachments.preview', [$unit, $attachment->id]) }}" alt="{{ $attachment->original_name }}" class="h-full w-full object-cover transition-transform duration-300 hover:scale-105">
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-ink" title="{{ $attachment->original_name }}">
                                {{ $attachment->original_name }}
                            </div>
                            <div class="mt-1 text-[10px] font-medium uppercase tracking-[0.2em] text-faint">
                                {{ $attachment->extension }} • {{ $attachment->human_file_size }}
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 border-t border-line pt-2">
                            <a href="{{ route('units.attachments.download', [$unit, $attachment->id]) }}" class="flex-1 rounded-md border border-line bg-surface px-2.5 py-1.5 text-center text-[11px] font-semibold text-muted transition-colors hover:bg-surface-raised">
                                Unduh berkas
                            </a>

                            @if(auth()->user()->canWriteUnitId((int) $module['id']))
                                <form method="POST" action="{{ route('units.attachments.delete', [$unit, $attachment->id]) }}" data-confirm="Apakah Anda yakin ingin melenyapkan lampiran fisik berkas ini?">
                                    @csrf @method('DELETE')
                                    <button class="rounded-md bg-[var(--danger-soft)] px-2.5 py-1.5 text-[11px] font-semibold text-danger transition-colors hover:bg-[var(--danger-soft)] badig-action badig-action-danger" type="submit">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-line bg-surface-raised px-4 py-6 text-center text-sm text-muted">
                        Belum ada lampiran berkas yang terunggah untuk dokumentasi ini.
                    </div>
                @endforelse
            </div>
        </div>
    </aside>
</div>
@endsection