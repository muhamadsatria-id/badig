@extends('layouts.app')

@section('title', 'Detail Data')
@section('page_title', 'Detail Data')

@section('content')
@php
$sections = [
    'A. Metadata Dokumen' => [
        'Instansi' => $orang->instansi, 'Sifat Dokumen' => $orang->sifat_dokumen, 'Jenis Dokumen' => $orang->jenis_dokumen,
        'Nomor Dokumen' => $orang->nomor_dpo, 'Tanggal Dikeluarkan' => optional($orang->tanggal_dikeluarkan)->format('d/m/Y'), 'Tempat Dikeluarkan' => $orang->tempat_dikeluarkan,
    ],
    'B. Data Identitas Subjek' => [
        'Nama Lengkap' => $orang->nama, 'NIK' => $orang->nik, 'Kewarganegaraan' => $orang->kewarganegaraan, 'Jenis Kelamin' => $orang->jenis_kelamin,
        'Tempat/Tanggal Lahir' => trim(($orang->tempat_lahir ?: '-').' / '.(optional($orang->tanggal_lahir)->format('d/m/Y') ?: '-')), 'Pekerjaan Terakhir' => $orang->pekerjaan,
        'Nomor HP' => $orang->phone, 'Catatan Hukum' => $orang->catatan_hukum,
    ],
    'C. Data Alamat Tempat Tinggal' => ['Alamat 1' => $orang->alamat, 'Alamat 2 (Alternatif)' => $orang->alamat_alternatif],
    'D. Ciri-Ciri Khusus (Fisik)' => [
        'Tinggi Badan' => $orang->tinggi_badan, 'Berat Badan' => $orang->berat_badan, 'Rambut' => $orang->rambut, 'Bentuk Tubuh' => $orang->bentuk_tubuh,
        'Warna Kulit' => $orang->warna_kulit, 'Warna Mata' => $orang->warna_mata, 'Hidung' => $orang->hidung, 'Bibir' => $orang->bibir, 'Ciri Lainnya' => $orang->ciri_lainnya,
    ],
    'E. Data Perkara & Hukum' => [
        'Dugaan Indikasi / Permasalahan' => $orang->dugaan_tindak_pidana, 'Melanggar Pasal' => $orang->melanggar_pasal, 'No. Reg Kejahatan' => $orang->no_reg_kejahatan,
        'Keterangan Lain' => $orang->keterangan_lain, 'Analisis / Deskripsi' => $orang->analisis_deskripsi ?: $orang->keterangan,
    ],
    'F. Informasi Agen & Kontak Pelaporan' => [
        'Nama Agen' => $orang->nama_penyidik, 'Jabatan Agen' => $orang->jabatan_penyidik, 'No. HP Agen' => $orang->no_hp_penyidik,
        'Pejabat Penandatangan' => $orang->pejabat_penandatangan, 'NRP Penandatangan' => $orang->nrp_penandatangan,
    ],
];
@endphp

<div class="space-y-5">
    <section class="badig-card overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)]">
            <div class="border-b border-line bg-surface-raised p-5 lg:border-b-0 lg:border-r">
                <div class="aspect-[3/4] overflow-hidden rounded-xl border border-line bg-surface">
                    @if($orang->foto)
                        <img src="{{ route('database-orang.photo', $orang) }}" class="h-full w-full object-cover" alt="Foto {{ $orang->nama }}">
                    @else
                        <div class="flex h-full items-center justify-center text-muted"><flux:icon name="user" class="size-16" /></div>
                    @endif
                </div>
            </div>
            <div class="p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap gap-2"><x-ui.badge color="success" dot>{{ $orang->status_dpo ?: 'Aktif' }}</x-ui.badge><x-ui.badge>{{ $orang->kategori ?: 'Tanpa kategori' }}</x-ui.badge></div>
                        <h2 class="mt-4 text-2xl font-bold tracking-tight text-ink">{{ $orang->nama }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ $orang->nomor_dpo ?: 'Nomor Orang belum ditetapkan' }} · {{ $orang->unit?->nama_unit ?: '-' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.button :href="route('database-orang.pdf', $orang)" target="_blank" rel="noopener noreferrer" class="h-10 px-3 text-xs"><flux:icon name="document-arrow-down" class="size-4 text-danger" />PDF Orang</x-ui.button>
                        @if(auth()->user()->canWriteUnitId((int) $orang->unit_id))<x-ui.button :href="route('database-orang.edit', $orang)" variant="primary" class="h-10 px-3 text-xs"><flux:icon name="pencil-square" class="size-4" />Edit</x-ui.button>@endif
                        <x-ui.button :href="route('database-orang.index')" class="h-10 px-3 text-xs"><flux:icon name="arrow-left" class="size-4" />Kembali</x-ui.button>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach(['NIK' => $orang->nik, 'No. HP' => $orang->phone, 'Tanggal Terbit' => optional($orang->tanggal_dikeluarkan)->format('d/m/Y'), 'Lampiran' => $attachments->count().' file'] as $label => $value)
                        <div class="rounded-xl border border-line bg-surface-raised p-3"><p class="text-[10px] font-bold uppercase tracking-wider text-muted">{{ $label }}</p><p class="mt-1 truncate text-sm font-semibold text-ink">{{ $value ?: '-' }}</p></div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @foreach($sections as $title => $rows)
        <x-ui.card :title="$title">
            <dl class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @foreach($rows as $label => $value)
                    <div class="rounded-xl border border-line bg-surface-raised p-3 {{ strlen((string)$value) > 120 ? 'md:col-span-2' : '' }}">
                        <dt class="text-[10px] font-bold uppercase tracking-[0.16em] text-muted">{{ $label }}</dt>
                        <dd class="mt-1 whitespace-pre-line text-sm leading-6 text-ink">{{ $value ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>
    @endforeach

    <x-ui.card title="Lampiran Pendukung" subtitle="Gambar ditampilkan sebagai pratinjau; dokumen dapat diunduh dari kartu yang sama." icon="paper-clip">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($attachments as $attachment)
                <article class="overflow-hidden rounded-xl border border-line bg-surface-raised">
                    @if($attachment->is_image)
                        <img src="{{ route('database-orang.attachments.preview', [$orang, $attachment->id]) }}" class="h-40 w-full object-cover" alt="{{ $attachment->original_name }}">
                    @else
                        <div class="flex h-28 items-center justify-center bg-surface"><flux:icon name="document" class="size-9 text-muted" /></div>
                    @endif
                    <div class="p-3"><p class="truncate text-xs font-semibold text-ink">{{ $attachment->original_name }}</p><p class="mt-1 text-[10px] uppercase tracking-wider text-muted">{{ strtoupper($attachment->extension) }} · {{ $attachment->human_file_size }}</p>
                        <div class="mt-3 flex gap-2"><a href="{{ route('database-orang.attachments.download', [$orang, $attachment->id]) }}" class="badig-action badig-action-secondary flex-1 px-3 py-2 text-center text-xs">Unduh</a>
                            @if(auth()->user()->canWriteUnitId((int)$orang->unit_id))<form method="POST" action="{{ route('database-orang.attachments.delete', [$orang, $attachment->id]) }}" data-confirm="Hapus lampiran ini?">@csrf @method('DELETE')<button class="badig-action badig-action-danger px-3 py-2 text-xs" type="submit">Hapus</button></form>@endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-line p-8 text-center text-sm text-muted">Belum ada lampiran pendukung.</div>
            @endforelse
        </div>
    </x-ui.card>
</div>
@endsection
