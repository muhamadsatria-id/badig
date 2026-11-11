@extends('layouts.app')

@section('title', 'Data Orang')
@section('page_title', 'Data Orang')

@section('content')
<div class="space-y-5">
    <section class="badig-card flex flex-col gap-5 p-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="max-w-3xl">
            <x-ui.badge color="success" dot>Database Orang Terintegrasi</x-ui.badge>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-ink">Data Orang</h2>
            <p class="mt-1 text-sm leading-6 text-muted">Kelola identitas, dokumen hukum, foto utama, lampiran pendukung, dan dokumen PDF orang dari satu halaman.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button :href="route('database-orang.export-pdf', request()->query())" class="h-10 px-3 text-xs whitespace-nowrap">
                <flux:icon name="document-arrow-down" class="size-4 text-danger" />Export
            </x-ui.button>
            
            <x-ui.button :href="route('database-orang.create')" variant="primary" class="h-10 px-3 text-xs whitespace-nowrap">
                <flux:icon name="plus" class="size-4" />Tambah
            </x-ui.button>
        </div>
    </section>

    <section class="badig-card p-4">
        <form method="GET" data-live-target="#badig-results" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,1fr)_220px_220px_auto]">
            <div class="relative">
                <flux:icon name="magnifying-glass" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted" />
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, NIK, nomor orang, perkara, atau alamat..." class="badig-field h-11 w-full pl-10 pr-3 text-sm">
            </div>
            <select name="unit_id" class="badig-field h-11 w-full px-3 text-sm">
                <option value="">Semua unit</option>
                @foreach($units as $unit)<option value="{{ $unit->id }}" @selected(($filters['unit_id'] ?? '') == $unit->id)>{{ $unit->nama_unit }}</option>@endforeach
            </select>
            <select name="kategori" class="badig-field h-11 w-full px-3 text-sm">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)<option value="{{ $category }}" @selected(($filters['kategori'] ?? '') === $category)>{{ $category }}</option>@endforeach
            </select>
            <x-ui.button type="submit" variant="primary" class="h-11 px-4 text-xs"><flux:icon name="funnel" class="size-4" />Terapkan</x-ui.button>
        </form>
    </section>

    <section id="badig-results" data-live-region class="badig-card overflow-visible">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="whitespace-nowrap">
                        <th class="text-left">Subjek</th>
                        <th class="text-left">Dokumen Orang</th>
                        <th class="text-left">Perkara</th>
                        <!-- <th class="text-left">Unit</th> -->
                        <th class="text-center">Lampiran</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($people as $orang)
                        <tr class="transition hover:bg-surface-raised">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="size-12 shrink-0 overflow-hidden rounded-lg border border-line bg-surface-raised">
                                        @if($orang->foto)
                                            <img src="{{ route('database-orang.photo', $orang) }}" loading="lazy" decoding="async" class="h-full w-full object-cover" alt="{{ $orang->nama }}">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-muted"><flux:icon name="user" class="size-5" /></div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-ink">{{ $orang->nama }}</p>
                                        <p class="mt-0.5 text-xs text-muted whitespace-nowrap">NIK {{ $orang->nik ?: '-' }} · {{ $orang->phone ?: '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <!-- <p class="text-sm font-semibold text-ink">{{ $orang->nomor_dpo ?: 'Belum bernomor' }}</p> -->
                                <p class="mt-0.5 text-xs text-muted">{{ optional($orang->tanggal_dikeluarkan)->format('d/m/Y') ?: '-' }} · {{ $orang->status_dpo ?: 'Aktif' }}</p>
                            </td>
                            <td>
                                <div class="max-w-[260px]">
                                    <p class="truncate text-sm text-ink">{{ $orang->kategori ?: '-' }}</p>
                                    <p class="mt-0.5 truncate text-xs text-muted">{{ $orang->dugaan_tindak_pidana ?: '-' }}</p>
                                </div>
                            </td>
                            <!-- <td><span class="text-xs font-semibold text-muted">{{ $orang->unit?->nama_unit ?: '-' }}</span></td> -->
                            <td class="text-center whitespace-nowrap"><x-ui.badge>{{ (int) $orang->attachments_count }} file</x-ui.badge></td>
                            <td class="text-right">
                                <div x-data="{ open:false, top:0, left:0, update(){ const r=this.$refs.trigger.getBoundingClientRect(); this.top=r.bottom+8; this.left=Math.max(12,r.right-184); if(this.top+170>window.innerHeight)this.top=r.top-178; } }" class="inline-flex" @keydown.escape.window="open=false" @scroll.window="open && update()" @resize.window="open && update()">
                                    <button x-ref="trigger" type="button" @click.stop="open=!open; if(open)$nextTick(()=>update())" class="badig-action badig-action-secondary h-9 px-3 text-xs"><flux:icon name="ellipsis-vertical" class="size-4" />Aksi</button>
                                    <template x-teleport="body">
                                        <div x-cloak x-show="open" @click.outside="open=false" :style="`top:${top}px;left:${left}px;width:184px`" class="fixed z-[9999] rounded-xl border border-line bg-surface p-1.5 text-left shadow-2xl">
                                            <a href="{{ route('database-orang.show', $orang) }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-xs font-medium text-ink hover:bg-surface-raised"><flux:icon name="eye" class="size-4 text-muted" />Detail</a>
                                            <a href="{{ route('database-orang.pdf', $orang) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-xs font-medium text-ink hover:bg-surface-raised"><flux:icon name="document-arrow-down" class="size-4 text-danger" />Dokumen PDF</a>
                                            @if(auth()->user()->canWriteUnitId((int) $orang->unit_id))
                                                <div class="my-1 border-t border-line"></div>
                                                <a href="{{ route('database-orang.edit', $orang) }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-xs font-medium text-ink hover:bg-surface-raised"><flux:icon name="pencil-square" class="size-4 text-warning" />Edit</a>
                                                <form method="POST" action="{{ route('database-orang.destroy', $orang) }}" data-confirm="Hapus Data dan seluruh lampirannya?">@csrf @method('DELETE')
                                                    <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-xs font-medium text-danger hover:bg-[var(--danger-soft)]" type="submit"><flux:icon name="trash" class="size-4" />Hapus</button>
                                                </form>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-16 text-center text-sm text-muted">Belum ada Data yang terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-line bg-surface-raised px-4 py-3">{{ $people->links() }}</div>
    </section>
</div>
@endsection
