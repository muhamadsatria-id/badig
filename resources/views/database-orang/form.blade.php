@extends('layouts.app')

@section('title', $orang->exists ? 'Edit Data' : 'Tambah Data')
@section('page_title', $orang->exists ? 'Edit Data' : 'Tambah Data')

@section('content')
<div class="space-y-5">
    <section class="badig-card flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <x-ui.badge color="success" dot>{{ $orang->exists ? 'Perbarui Rekam Data' : 'Registrasi Rekam Data Baru' }}</x-ui.badge>
            <h2 class="mt-3 text-2xl font-bold tracking-tight text-ink">{{ $orang->exists ? 'Edit Data' : 'Tambah Data' }}</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-muted">Lengkapi metadata dokumen, identitas, ciri fisik, perkara, agen, foto utama, serta seluruh lampiran pendukung.</p>
        </div>
        <x-ui.button :href="route('database-orang.index')" class="h-10 px-3 text-xs"><flux:icon name="arrow-left" class="size-4" />Kembali</x-ui.button>
    </section>

    @if($errors->any())
        <div class="rounded-xl border border-danger/25 bg-[var(--danger-soft)] p-4 text-sm text-danger">
            <p class="font-semibold">Form belum dapat disimpan.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $orang->exists ? route('database-orang.update', $orang) : route('database-orang.store') }}" class="space-y-5">
        @csrf
        @if($orang->exists) @method('PUT') @endif

        <x-ui.form-section letter="A" title="Metadata Dokumen" description="Informasi administratif dan klasifikasi resmi dokumen.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <x-ui.select label="Unit Penanggung Jawab" name="unit_id" required>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id', $orang->unit_id) == $unit->id)>{{ $unit->nama_unit ?? ('Unit '.$unit->id) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input label="Instansi" name="instansi" :value="$orang->instansi" placeholder="Contoh: Satuan Intelkam" />
                <x-ui.input label="Sifat Dokumen" name="sifat_dokumen" :value="$orang->sifat_dokumen" placeholder="TERBATAS / RAHASIA" />
                <x-ui.input label="Jenis Dokumen" name="jenis_dokumen" :value="$orang->jenis_dokumen" />
                <x-ui.input label="Nomor Dokumen" name="nomor_dpo" :value="$orang->nomor_dpo" placeholder="Nomor dokumen" />
                <x-ui.input label="Kategori" name="kategori" :value="$orang->kategori" placeholder="Kategori subjek / data" />
                <x-ui.select label="Status" name="status_dpo">
                    @foreach(['Aktif','Tertangkap','Dicabut','Tidak Aktif'] as $status)<option value="{{ $status }}" @selected(old('status_dpo', $orang->status_dpo) === $status)>{{ $status }}</option>@endforeach
                </x-ui.select>
                <x-ui.input label="Tanggal Dikeluarkan" name="tanggal_dikeluarkan" type="date" :value="optional($orang->tanggal_dikeluarkan)->format('Y-m-d')" />
                <x-ui.input label="Tempat Dikeluarkan" name="tempat_dikeluarkan" :value="$orang->tempat_dikeluarkan" />
            </div>
        </x-ui.form-section>

        <x-ui.form-section letter="B" title="Data Identitas Subjek" description="Identitas utama yang tercantum dalam rekam data.">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <x-ui.input label="Nama Lengkap" name="nama" :value="$orang->nama" required />
                <x-ui.input label="Nomor Identitas (NIK)" name="nik" :value="$orang->nik" />
                <x-ui.input label="Kewarganegaraan" name="kewarganegaraan" :value="$orang->kewarganegaraan" />
                <x-ui.select label="Jenis Kelamin" name="jenis_kelamin">
                    <option value="">Pilih jenis kelamin</option>
                    @foreach(['Laki-laki','Perempuan'] as $gender)<option value="{{ $gender }}" @selected(old('jenis_kelamin', $orang->jenis_kelamin) === $gender)>{{ $gender }}</option>@endforeach
                </x-ui.select>
                <x-ui.input label="Tempat Lahir" name="tempat_lahir" :value="$orang->tempat_lahir" />
                <x-ui.input label="Tanggal Lahir" name="tanggal_lahir" type="date" :value="optional($orang->tanggal_lahir)->format('Y-m-d')" />
                <x-ui.input label="Pekerjaan Terakhir" name="pekerjaan" :value="$orang->pekerjaan" />
                <x-ui.input label="Nomor HP" name="phone" :value="$orang->phone" />
                <div class="md:col-span-2 xl:col-span-3"><x-ui.textarea label="Catatan Hukum" name="catatan_hukum" :value="$orang->catatan_hukum" rows="3" /></div>
            </div>
        </x-ui.form-section>

        <x-ui.form-section letter="C" title="Data Alamat Tempat Tinggal">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <x-ui.textarea label="Alamat 1" name="alamat" :value="$orang->alamat" rows="4" />
                <x-ui.textarea label="Alamat 2 (Alternatif)" name="alamat_alternatif" :value="$orang->alamat_alternatif" rows="4" />
            </div>
        </x-ui.form-section>

        <x-ui.form-section letter="D" title="Ciri-Ciri Khusus (Fisik)">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <x-ui.input label="Tinggi Badan" name="tinggi_badan" :value="$orang->tinggi_badan" placeholder="Contoh: 170 cm" />
                <x-ui.input label="Berat Badan" name="berat_badan" :value="$orang->berat_badan" placeholder="Contoh: 65 kg" />
                <x-ui.input label="Rambut" name="rambut" :value="$orang->rambut" />
                <x-ui.input label="Bentuk Tubuh" name="bentuk_tubuh" :value="$orang->bentuk_tubuh" />
                <x-ui.input label="Warna Kulit" name="warna_kulit" :value="$orang->warna_kulit" />
                <x-ui.input label="Warna Mata" name="warna_mata" :value="$orang->warna_mata" />
                <x-ui.input label="Hidung" name="hidung" :value="$orang->hidung" />
                <x-ui.input label="Bibir" name="bibir" :value="$orang->bibir" />
                <div class="md:col-span-2 xl:col-span-4"><x-ui.textarea label="Ciri Lainnya" name="ciri_lainnya" :value="$orang->ciri_lainnya" rows="3" /></div>
            </div>
        </x-ui.form-section>

        <x-ui.form-section letter="E" title="Data Perkara & Hukum">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <x-ui.textarea label="Dugaan Indikasi / Permasalahan" name="dugaan_tindak_pidana" :value="$orang->dugaan_tindak_pidana" rows="4" />
                <x-ui.textarea label="Melanggar Pasal" name="melanggar_pasal" :value="$orang->melanggar_pasal" rows="4" />
                <x-ui.input label="No. Reg Kejahatan" name="no_reg_kejahatan" :value="$orang->no_reg_kejahatan" />
                <x-ui.textarea label="Keterangan Lain" name="keterangan_lain" :value="$orang->keterangan_lain" rows="3" />
                <div class="lg:col-span-2"><x-ui.textarea label="Analisis / Deskripsi" name="analisis_deskripsi" :value="$orang->analisis_deskripsi ?: $orang->keterangan" rows="6" /></div>
            </div>
        </x-ui.form-section>

        <x-ui.form-section letter="F" title="Informasi Agen & Kontak Pelaporan">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <x-ui.input label="Nama Agen" name="nama_penyidik" :value="$orang->nama_penyidik" />
                <x-ui.input label="Jabatan Agen" name="jabatan_penyidik" :value="$orang->jabatan_penyidik" />
                <x-ui.input label="No. HP Agen" name="no_hp_penyidik" :value="$orang->no_hp_penyidik" />
                <x-ui.input label="Pejabat Penandatangan" name="pejabat_penandatangan" :value="$orang->pejabat_penandatangan" />
                <x-ui.input label="NRP Penandatangan" name="nrp_penandatangan" :value="$orang->nrp_penandatangan" />
            </div>
        </x-ui.form-section>

        <x-ui.form-section letter="G" title="Foto & Lampiran Pendukung" description="Foto utama akan tampil pada dokumen PDF. Lampiran gambar juga akan ditanam dalam PDF; dokumen lainnya dirangkum dengan jelas.">
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-[280px_minmax(0,1fr)]">
                <div>
                    <label class="mb-2 block text-xs font-semibold text-ink">Foto Utama Orang</label>
                    <div class="overflow-hidden rounded-xl border border-dashed border-line bg-surface-raised p-3">
                        @if($orang->exists && $orang->foto)
                            <img src="{{ route('database-orang.photo', $orang) }}" class="mb-3 h-56 w-full rounded-lg object-cover" alt="Foto {{ $orang->nama }}">
                        @else
                            <div class="mb-3 flex h-56 items-center justify-center rounded-lg bg-surface text-muted"><flux:icon name="user" class="size-12" /></div>
                        @endif
                        <input type="file" name="foto_utama" accept="image/jpeg,image/png,image/webp" class="badig-field block w-full text-xs">
                        <p class="mt-2 text-[11px] leading-5 text-muted">JPG, PNG, atau WEBP. Maksimum 10 MB.</p>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold text-ink">Upload File Pendukung</label>
                    <div class="rounded-xl border border-dashed border-line bg-surface-raised p-4" x-data="{ rows: [{ id: 0 }], nextId: 1, addRow() { this.rows.push({ id: this.nextId++ }); }, removeRow(index) { this.rows.splice(index, 1); } }">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <div class="mb-2 flex items-center gap-2">
                                <input type="file" name="attachments[]" class="badig-field block w-full text-xs" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx">
                                <button type="button" x-show="rows.length > 1" @click="removeRow(index)" class="badig-focus inline-flex size-10 shrink-0 items-center justify-center rounded-lg border border-line bg-surface text-muted transition hover:bg-surface-raised hover:text-danger" aria-label="Hapus baris file">
                                    <flux:icon name="trash" class="size-4" />
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addRow()" class="badig-focus mt-1 inline-flex items-center gap-1.5 rounded-lg border border-dashed border-line bg-surface px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised">
                            <flux:icon name="plus" class="size-4 text-muted" />
                            Tambah File
                        </button>
                        <p class="mt-2 text-[11px] leading-5 text-muted">Maksimum 30 file, masing-masing 50 MB. Mendukung gambar, PDF, Word, Excel, PowerPoint, CSV, dan TXT.</p>
                    </div>
                    @if($orang->exists && $attachments->isNotEmpty())
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($attachments as $attachment)
                                <div class="rounded-lg border border-line bg-surface p-3">
                                    <p class="truncate text-xs font-semibold text-ink">{{ $attachment->original_name }}</p>
                                    <p class="mt-1 text-[10px] uppercase tracking-wider text-muted">{{ strtoupper($attachment->extension) }} · {{ $attachment->human_file_size }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </x-ui.form-section>

        <div class="badig-card flex flex-col-reverse gap-3 p-4 sm:flex-row sm:justify-end">
            <x-ui.button :href="route('database-orang.index')" class="h-10 px-4 text-xs">Batal</x-ui.button>
            <x-ui.button type="submit" variant="primary" class="h-10 px-4 text-xs"><flux:icon name="check" class="size-4" />Simpan Data</x-ui.button>
        </div>
    </form>
</div>
@endsection
