@extends('layouts.app')

@php
    $item = $item ?? null;
    $isEdit = !is_null($item);
    $attachments = $attachments ?? collect();
@endphp

@section('title', $isEdit ? 'Edit ' . $module['title'] : 'Tambah ' . $module['title'])
@section('page_title', $isEdit ? 'Edit ' . $module['title'] : 'Tambah ' . $module['title'])

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-container {
        z-index: 1 !important;
        max-width: 100%;
    }
</style>
@endpush

@section('content')
@php
    $action = $isEdit ? route('units.update', [$unit, $item->id]) : route('units.store', $unit);
    $categories = [
        'unit1' => ['Aliran', 'Parpol', 'Kades', 'Camat'],
        'unit2' => ['Pengusaha', 'SPBU', 'LPG', 'Pasar'],
        'unit3' => ['Ormas', 'Ormawa', 'LSM', 'Tokoh'],
        'unit4' => ['Terorisme', 'Narkotika', 'Curanmor', 'Premanisme'],
    ][$unit] ?? [];
@endphp

<div class="w-full min-w-0">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:p-6 badig-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">inventory_2</span>
                    {{ strtoupper($unit) }}
                </div>
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-ink">
                        {{ $isEdit ? 'Perbarui Rekaman Arsip' : 'Tambah Rekaman Arsip' }}
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-muted">
                        Kelola data pemetaan, pelapor, validasi koordinat GIS, serta manajemen berkas lampiran.
                    </p>
                </div>
            </div>

            <a href="{{ route('units.index', $unit) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-4 py-2 text-sm font-semibold text-muted shadow-sm transition hover:bg-surface-raised badig-action badig-action-secondary">
                <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                Kembali
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="mt-6 flex items-center gap-2 rounded-2xl border border-danger/20 bg-[var(--danger-soft)] px-4 py-3 text-sm font-medium text-danger shadow-sm">
            <span class="material-symbols-outlined text-base text-danger">error</span>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
            <div class="min-w-0 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                        Tanggal Pembuatan <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="tanggal_pembuatan" value="{{ old('tanggal_pembuatan', optional(data_get($item, 'tanggal_pembuatan', now()))->format('Y-m-d') ?? now()->toDateString()) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface" required>
                </div>

                <div
                    class="space-y-1"
                    data-category-value="{{ old('kategori', data_get($item, 'kategori', '')) }}"
                    data-category-options="{{ json_encode($categories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                    x-data="badigCategoryPicker($el)"
                >
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                        Kategori Entitas <span class="text-danger">*</span>
                    </label>
                    <div class="relative">
                        <template x-if="!isManual">
                            <button type="button" @click="open = !open" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-left text-sm text-muted outline-none transition focus:border-accent focus:bg-surface flex items-center justify-between">
                                <span x-text="value ? value : 'Pilih Kategori Entitas...'"></span>
                                <span class="material-symbols-outlined text-base">expand_more</span>
                            </button>
                        </template>
                        <template x-if="isManual">
                            <div class="flex gap-2">
                                <input type="text" name="kategori_manual" x-ref="kategoriManualInput" x-model="manualValue" placeholder="Masukkan kategori manual..." class="h-11 flex-1 rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface" required>
                                <button type="button" @click="isManual = false; value = ''; manualValue = ''" class="h-11 rounded-xl border border-line bg-surface-raised px-3 text-xs text-muted hover:bg-surface-raised hover:text-ink">
                                    Batal
                                </button>
                            </div>
                        </template>
                        
                        <input type="hidden" name="kategori" :value="isManual ? manualValue : value">
                        
                        <div x-show="open" @click.outside="open = false" class="absolute left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-line bg-surface p-2 shadow-lg" x-cloak>
                            <input type="text" x-model="search" placeholder="Cari kategori..." class="mb-2 h-9 w-full rounded-lg border border-line bg-surface-raised px-2.5 text-xs text-muted outline-none focus:border-accent">
                            <div class="space-y-1">
                                <template x-for="opt in filteredOptions" :key="opt">
                                    <button type="button" @click="select(opt)" class="w-full rounded-lg px-2.5 py-1.5 text-left text-xs text-muted hover:bg-surface-raised hover:text-ink" :class="value === opt ? 'bg-accent/10 text-accent font-semibold' : ''">
                                        <span x-text="opt"></span>
                                    </button>
                                </template>
                                <button type="button" @click="select('__MANUAL__')" class="w-full rounded-lg px-2.5 py-1.5 text-left text-xs text-accent font-medium hover:bg-surface-raised">
                                    + Tambah Manual (Isi Sendiri)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-1 md:col-span-2">
                    <div class="min-w-0 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama Pelapor <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="pelapor_nama" value="{{ old('pelapor_nama', data_get($item, 'pelapor_nama', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" placeholder="Nama pelapor" required>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                NRP Pelapor <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="pelapor_nrp" value="{{ old('pelapor_nrp', data_get($item, 'pelapor_nrp', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" placeholder="Contoh: 123456" required>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
            <div class="space-y-4">
                <div class="border-b border-line pb-3">
                    <h3 class="text-sm font-semibold text-ink">Informasi Atribut Lapangan</h3>
                    <p class="mt-1 text-sm text-muted">
                        Atribut penunjang indikator intelijen spesifik klaster unit operasional.
                    </p>
                </div>

                <div class="min-w-0 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @if($unit === 'unit1')
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama Subjek Organisasi / Kelompok <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_subjek" value="{{ old('nama_subjek', data_get($item, 'nama_subjek', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" required>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Estimasi Jumlah Pengikut
                            </label>
                            <input type="number" name="pengikut" value="{{ old('pengikut', data_get($item, 'pengikut', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Status Legalitas Kemenkumham / Instansi
                            </label>
                            <input type="text" name="status_legal" value="{{ old('status_legal', data_get($item, 'status_legal', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Catatan Indeks Kerawanan Kamtibmas
                            </label>
                            <textarea name="catatan_rawan" rows="4" class="min-h-[112px] w-full rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">{{ old('catatan_rawan', data_get($item, 'catatan_rawan', '')) }}</textarea>
                        </div>
                    @elseif($unit === 'unit2')
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama Objek Vital / Objek Ekonomi <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_aset" value="{{ old('nama_aset', data_get($item, 'nama_aset', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" required>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Alamat Operasional Lengkap <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="alamat" value="{{ old('alamat', data_get($item, 'alamat', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" required>
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Kapasitas / Volume Komoditas Logistik
                            </label>
                            <input type="text" name="kapasitas" value="{{ old('kapasitas', data_get($item, 'kapasitas', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                        </div>
                    @elseif($unit === 'unit3')
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama Resmi Ormas / LSM / Lembaga <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_organisasi" value="{{ old('nama_organisasi', data_get($item, 'nama_organisasi', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" required>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama Penanggung Jawab / Pimpinan Utama
                            </label>
                            <input type="text" name="pimpinan" value="{{ old('pimpinan', data_get($item, 'pimpinan', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Total Anggota Terdaftar
                            </label>
                            <input type="number" name="total_anggota" value="{{ old('total_anggota', data_get($item, 'total_anggota', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nomor SK Akta / Surat Izin Operasional
                            </label>
                            <input type="text" name="legalitas" value="{{ old('legalitas', data_get($item, 'legalitas', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Catatan Monitoring Rekam Jejak Aktivitas
                            </label>
                            <textarea name="catatan_aktivitas" rows="4" class="min-h-[112px] w-full rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">{{ old('catatan_aktivitas', data_get($item, 'catatan_aktivitas', '')) }}</textarea>
                        </div>
                    @elseif($unit === 'unit4')
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Nama Lengkap Subjek / Judul Kasus <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_kasus" value="{{ old('nama_kasus', data_get($item, 'nama_kasus', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" required>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Status Penanganan Kasus (Lidik / Sidik / P21) <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="status_kasus" value="{{ old('status_kasus', data_get($item, 'status_kasus', '')) }}" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field" required>
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Modus Operandi Tindakan Kriminalitas
                            </label>
                            <textarea name="modus" rows="4" class="min-h-[112px] w-full rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">{{ old('modus', data_get($item, 'modus', '')) }}</textarea>
                        </div>

                        <div class="space-y-1 md:col-span-2">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Data Sensitif Tambahan (Terenkripsi Internal)
                            </label>
                            <textarea name="data_enkripsi" rows="4" class="min-h-[112px] w-full rounded-xl border border-line bg-surface-raised px-3 py-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">{{ old('data_enkripsi', data_get($item, 'data_enkripsi', '')) }}</textarea>
                        </div>
                    @endif

                    <div class="min-w-0 grid grid-cols-1 gap-4 md:col-span-2 md:grid-cols-2" x-data="{
                        districts: {{ $districts->map(fn($d) => [
                            'id' => $d->id,
                            'name' => $d->name,
                            'villages' => $d->villages->map(fn($v) => ['id' => $v->id, 'name' => $v->name])
                        ])->toJson() }},
                        
                        selectedDistrictId: '{{ old('district_id', data_get($item, 'district_id', '')) }}',
                        selectedVillageId: '{{ old('village_id', data_get($item, 'village_id', '')) }}',
                        
                        districtSearch: '',
                        villageSearch: '',
                        
                        districtOpen: false,
                        villageOpen: false,
                        
                        isDistrictManual: false,
                        isVillageManual: false,
                        
                        districtManualValue: '',
                        villageManualValue: '',
                        
                        init() {
                            if (this.selectedDistrictId && !this.districts.some(d => d.id == this.selectedDistrictId)) {
                                this.isDistrictManual = true;
                                this.districtManualValue = '{{ data_get($item, 'district.name', '') }}';
                            }
                            if (this.selectedVillageId) {
                                let currentDistrict = this.districts.find(d => d.id == this.selectedDistrictId);
                                if (!currentDistrict || !currentDistrict.villages.some(v => v.id == this.selectedVillageId)) {
                                    this.isVillageManual = true;
                                    this.villageManualValue = '{{ data_get($item, 'village.name', '') }}';
                                }
                            }
                        },
                        
                        get filteredDistricts() {
                            if (!this.districtSearch) return this.districts;
                            return this.districts.filter(d => d.name.toLowerCase().includes(this.districtSearch.toLowerCase()));
                        },
                        
                        get availableVillages() {
                            let d = this.districts.find(d => d.id == this.selectedDistrictId);
                            return d ? d.villages : [];
                        },
                        
                        get filteredVillages() {
                            let vils = this.availableVillages;
                            if (!this.villageSearch) return vils;
                            return vils.filter(v => v.name.toLowerCase().includes(this.villageSearch.toLowerCase()));
                        },
                        
                        selectDistrict(id, name) {
                            if (id === '__MANUAL__') {
                                this.isDistrictManual = true;
                                this.selectedDistrictId = '';
                                this.districtManualValue = '';
                                this.isVillageManual = true;
                                this.selectedVillageId = '';
                                this.villageManualValue = '';
                                this.$nextTick(() => this.$refs.districtManualInput.focus());
                            } else {
                                this.isDistrictManual = false;
                                this.selectedDistrictId = id;
                                this.districtManualValue = '';
                                let newDistrict = this.districts.find(d => d.id == id);
                                if (newDistrict && (!this.isVillageManual && this.selectedVillageId && !newDistrict.villages.some(v => v.id == this.selectedVillageId))) {
                                    this.selectedVillageId = '';
                                    this.villageManualValue = '';
                                }
                            }
                            this.districtOpen = false;
                            this.districtSearch = '';
                        },
                        
                        selectVillage(id, name) {
                            if (id === '__MANUAL__') {
                                this.isVillageManual = true;
                                this.selectedVillageId = '';
                                this.villageManualValue = '';
                                this.$nextTick(() => this.$refs.villageManualInput.focus());
                            } else {
                                this.isVillageManual = false;
                                this.selectedVillageId = id;
                                this.villageManualValue = '';
                            }
                            this.villageOpen = false;
                            this.villageSearch = '';
                        }
                    }">
                        <!-- Kecamatan -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Kecamatan <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <template x-if="!isDistrictManual">
                                    <button type="button" @click="districtOpen = !districtOpen" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-left text-sm text-muted outline-none transition focus:border-accent focus:bg-surface flex items-center justify-between">
                                        <span x-text="selectedDistrictId ? (districts.find(d => d.id == selectedDistrictId)?.name || 'Pilih Kecamatan') : 'Pilih Kecamatan'"></span>
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                </template>
                                <template x-if="isDistrictManual">
                                    <div class="flex gap-2">
                                        <input type="text" name="district_name_manual" x-ref="districtManualInput" x-model="districtManualValue" placeholder="Kecamatan manual..." class="h-11 flex-1 rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface" required>
                                        <button type="button" @click="isDistrictManual = false; selectedDistrictId = ''; districtManualValue = ''; isVillageManual = false; selectedVillageId = ''; villageManualValue = ''" class="h-11 rounded-xl border border-line bg-surface-raised px-3 text-xs text-muted hover:bg-surface-raised hover:text-ink">
                                            Batal
                                        </button>
                                    </div>
                                </template>
                                
                                <input type="hidden" name="district_id" :value="isDistrictManual ? '' : selectedDistrictId">
                                
                                <div x-show="districtOpen" @click.outside="districtOpen = false" class="absolute left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-line bg-surface p-2 shadow-lg" x-cloak>
                                    <input type="text" x-model="districtSearch" placeholder="Cari Kecamatan..." class="mb-2 h-9 w-full rounded-lg border border-line bg-surface-raised px-2.5 text-xs text-muted outline-none focus:border-accent">
                                    <div class="space-y-1">
                                        <template x-for="d in filteredDistricts" :key="d.id">
                                            <button type="button" @click="selectDistrict(d.id, d.name)" class="w-full rounded-lg px-2.5 py-1.5 text-left text-xs text-muted hover:bg-surface-raised hover:text-ink" :class="selectedDistrictId == d.id ? 'bg-accent/10 text-accent font-semibold' : ''">
                                                <span x-text="d.name"></span>
                                            </button>
                                        </template>
                                        <button type="button" @click="selectDistrict('__MANUAL__')" class="w-full rounded-lg px-2.5 py-1.5 text-left text-xs text-accent font-medium hover:bg-surface-raised">
                                            + Tambah Manual (Di Luar Sukabumi)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kelurahan -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                                Kelurahan / Desa <span class="text-danger">*</span>
                            </label>
                            <div class="relative">
                                <template x-if="!isVillageManual">
                                    <button type="button" @click="villageOpen = !villageOpen" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-left text-sm text-muted outline-none transition focus:border-accent focus:bg-surface flex items-center justify-between" :disabled="!selectedDistrictId && !isDistrictManual">
                                        <span x-text="selectedVillageId ? (availableVillages.find(v => v.id == selectedVillageId)?.name || 'Pilih Kelurahan') : 'Pilih Kelurahan'"></span>
                                        <span class="material-symbols-outlined text-base">expand_more</span>
                                    </button>
                                </template>
                                <template x-if="isVillageManual">
                                    <div class="flex gap-2">
                                        <input type="text" name="village_name_manual" x-ref="villageManualInput" x-model="villageManualValue" placeholder="Kelurahan manual..." class="h-11 flex-1 rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface" required>
                                        <button type="button" @click="isVillageManual = false; selectedVillageId = ''; villageManualValue = ''" class="h-11 rounded-xl border border-line bg-surface-raised px-3 text-xs text-muted hover:bg-surface-raised hover:text-ink" :disabled="isDistrictManual">
                                            Batal
                                        </button>
                                    </div>
                                </template>
                                
                                <input type="hidden" name="village_id" :value="isVillageManual ? '' : selectedVillageId">
                                <input type="hidden" name="village_name_manual" :value="isVillageManual ? villageManualValue : ''">
                                
                                <div x-show="villageOpen" @click.outside="villageOpen = false" class="absolute left-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-line bg-surface p-2 shadow-lg" x-cloak>
                                    <input type="text" x-model="villageSearch" placeholder="Cari Kelurahan..." class="mb-2 h-9 w-full rounded-lg border border-line bg-surface-raised px-2.5 text-xs text-muted outline-none focus:border-accent">
                                    <div class="space-y-1">
                                        <template x-for="v in filteredVillages" :key="v.id">
                                            <button type="button" @click="selectVillage(v.id, v.name)" class="w-full rounded-lg px-2.5 py-1.5 text-left text-xs text-muted hover:bg-surface-raised hover:text-ink" :class="selectedVillageId == v.id ? 'bg-accent/10 text-accent font-semibold' : ''">
                                                <span x-text="v.name"></span>
                                            </button>
                                        </template>
                                        <button type="button" @click="selectVillage('__MANUAL__')" class="w-full rounded-lg px-2.5 py-1.5 text-left text-xs text-accent font-medium hover:bg-surface-raised">
                                            + Tambah Manual (Isi Sendiri)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Penentuan Titik Lokasi Geografis</h3>
                    <p class="mt-1 text-sm text-muted">
                        Cari alamat secara otomatis atau posisikan penanda langsung di atas peta interaktif.
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <input id="mapSearchInput" type="text" placeholder="Masukkan nama tempat atau nama jalan..." class="h-11 flex-1 rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                    <div class="flex gap-2">
                        <button type="button" id="mapSearchBtn" class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary">
                            <span class="material-symbols-outlined text-base">search</span>
                            Cari
                        </button>
                        <button type="button" id="useMyLocationBtn" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface-raised px-4 py-2 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary">
                            <span class="material-symbols-outlined text-base text-muted">my_location</span>
                            Lokasi Saya
                        </button>
                    </div>
                </div>

                <div id="coordinateMap" class="h-[340px] rounded-2xl border border-line shadow-inner"></div>
                <p id="mapHelpText" class="text-sm text-muted">Gunakan klik kiri pada peta atau geser penanda merah untuk mengatur koordinat secara manual.</p>

                <div class="min-w-0 grid grid-cols-1 gap-4 border-t border-line pt-4 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            Garis Lintang (Latitude)
                        </label>
                        <input id="koordinat_lat" type="text" name="koordinat_lat" value="{{ old('koordinat_lat', data_get($item, 'koordinat_lat', '')) }}" placeholder="-6.92712000" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                            Garis Bujur (Longitude)
                        </label>
                        <input id="koordinat_lng" type="text" name="koordinat_lng" value="{{ old('koordinat_lng', data_get($item, 'koordinat_lng', '')) }}" placeholder="106.93041000" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field">
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-ink">Unggah File Lampiran</h3>
                    <p class="mt-1 text-sm text-muted">
                        Format yang didukung: gambar, PDF, Word, Excel, CSV, teks, dan PowerPoint, dengan batas maksimal 50 MB per berkas.
                    </p>
                </div>

                <div class="space-y-2" x-data="{ rows: [{ id: 0 }], nextId: 1, addRow() { this.rows.push({ id: this.nextId++ }); }, removeRow(index) { this.rows.splice(index, 1); } }">
                    <template x-for="(row, index) in rows" :key="row.id">
                        <div class="flex items-center gap-2">
                            <input type="file" name="attachments[]" class="block w-full cursor-pointer rounded-2xl border border-dashed border-line bg-surface-raised p-4 text-sm text-muted transition hover:bg-surface-raised file:mr-4 file:rounded-lg file:border-0 file:bg-surface-raised file:px-3 file:py-1.5 file:text-[11px] file:font-semibold file:text-ink badig-field" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx">
                            <button type="button" x-show="rows.length > 1" @click="removeRow(index)" class="badig-focus inline-flex size-11 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-muted transition hover:bg-surface-raised hover:text-danger" aria-label="Hapus baris file">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="addRow()" class="badig-focus inline-flex items-center justify-center gap-2 rounded-lg border border-dashed border-line bg-surface px-4 py-2 text-sm font-semibold text-ink transition hover:bg-surface-raised">
                        <span class="material-symbols-outlined text-base text-muted">add</span>
                        Tambah File
                    </button>
                </div>

                @if($isEdit && $attachments->count())
                    <div class="space-y-2 border-t border-line pt-4">
                        <h4 class="text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Berkas Tersimpan Sebelumnya</h4>
                        <div class="min-w-0 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($attachments as $attachment)
                                <div class="flex items-center justify-between gap-3 rounded-2xl border border-line bg-surface-raised p-3 badig-card">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-medium text-ink">{{ $attachment->original_name }}</div>
                                        <div class="mt-1 text-[10px] uppercase tracking-[0.24em] text-faint">{{ $attachment->extension }} • {{ $attachment->human_file_size }}</div>
                                    </div>
                                    <a href="{{ route('units.attachments.download', [$unit, $attachment->id]) }}" class="shrink-0 text-sm font-semibold text-muted transition hover:text-ink">Unduh</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row">
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary">
                <span class="material-symbols-outlined text-base">save</span>
                Simpan Berkas Arsip
            </button>
            <a href="{{ route('units.index', $unit) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-5 py-2.5 text-sm font-semibold text-muted shadow-sm transition hover:bg-surface-raised badig-action badig-action-secondary">
                <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                Batalkan
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script @cspNonce>
window.badigCategoryPicker = function (element) {
    let options = [];

    try {
        const decoded = JSON.parse(element.dataset.categoryOptions || '[]');
        options = Array.isArray(decoded) ? decoded.filter((value) => typeof value === 'string') : [];
    } catch (error) {
        options = [];
    }

    return {
        open: false,
        search: '',
        value: element.dataset.categoryValue || '',
        options,
        isManual: false,
        manualValue: '',

        init() {
            if (this.value && !this.options.includes(this.value)) {
                this.isManual = true;
                this.manualValue = this.value;
                this.value = '__MANUAL__';
            }
        },

        get filteredOptions() {
            const search = this.search.trim().toLocaleLowerCase('id-ID');

            if (!search) {
                return this.options;
            }

            return this.options.filter((option) => option.toLocaleLowerCase('id-ID').includes(search));
        },

        select(option) {
            if (option === '__MANUAL__') {
                this.isManual = true;
                this.value = '__MANUAL__';
                this.manualValue = '';
                this.$nextTick(() => this.$refs.kategoriManualInput?.focus());
            } else {
                this.isManual = false;
                this.value = option;
                this.manualValue = '';
            }

            this.open = false;
            this.search = '';
        },
    };
};
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script @cspNonce>
document.addEventListener('DOMContentLoaded', () => {
    const latInput = document.getElementById('koordinat_lat');
    const lngInput = document.getElementById('koordinat_lng');
    const helpText = document.getElementById('mapHelpText');
    const mapContainer = document.getElementById('coordinateMap');

    if (!latInput || !lngInput || !mapContainer || typeof L === 'undefined') {
        if (helpText) {
            helpText.textContent = 'Gagal memuat komponen peta GIS. Periksa ketersediaan modul jaringan CDN.';
        }

        return;
    }

    const defaultLat = parseFloat(latInput.value) || -6.9175;
    const defaultLng = parseFloat(lngInput.value) || 106.9266;

    const map = L.map('coordinateMap').setView([defaultLat, defaultLng], 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    function setPoint(lat, lng, zoom = 15) {
        lat = Number(lat);
        lng = Number(lng);
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], zoom);
    }

    marker.on('dragend', (event) => {
        const point = event.target.getLatLng();
        setPoint(point.lat, point.lng, map.getZoom());
    });

    map.on('click', (event) => setPoint(event.latlng.lat, event.latlng.lng, map.getZoom()));

    function useBrowserLocation() {
        if (!navigator.geolocation) {
            helpText.textContent = 'Modul pembaca lokasi eksternal tidak didukung oleh peramban ini.';
            return;
        }

        helpText.textContent = 'Mengakses koordinat satelit GPS perangkat...';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                setPoint(position.coords.latitude, position.coords.longitude, 16);
                helpText.textContent = 'Sinkronisasi lokasi perangkat berhasil diterapkan.';
            },
            () => {
                helpText.textContent = 'Akses koordinat GPS dibatalkan atau tidak menerima otoritas izin sistem.';
            },
            { enableHighAccuracy: true, timeout: 7000 },
        );
    }

    if (!latInput.value || !lngInput.value) {
        useBrowserLocation();
    }

    document.getElementById('useMyLocationBtn')?.addEventListener('click', useBrowserLocation);

    document.getElementById('mapSearchBtn')?.addEventListener('click', async () => {
        const query = document.getElementById('mapSearchInput').value.trim();

        if (!query) {
            helpText.textContent = 'Isi kolom pencarian nama alamat terlebih dahulu.';
            return;
        }

        helpText.textContent = 'Memproses pencarian nama geolokasi...';
        try {
            const response = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query));
            const data = await response.json();

            if (data && data[0]) {
                setPoint(data[0].lat, data[0].lon, 16);
                helpText.textContent = data[0].display_name;
            } else {
                helpText.textContent = 'Alamat tidak teridentifikasi. Masukkan kata kunci yang lebih presisi.';
            }
        } catch (error) {
            helpText.textContent = 'Gagal memproses alamat karena gangguan interkoneksi jaringan server.';
        }
    });
});
</script>
@endpush