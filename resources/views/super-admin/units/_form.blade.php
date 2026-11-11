<div class="w-full min-w-0 rounded-2xl border border-line bg-surface p-5 shadow-sm sm:p-6 badig-card">
    <div class="rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 badig-card">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                    <span class="material-symbols-outlined text-base text-muted">account_tree</span>
                    Pengaturan unit
                </div>
                <h2 class="mt-2 text-xl font-semibold tracking-tight text-ink">{{ $title }}</h2>
                <p class="mt-1 text-sm leading-6 text-muted">
                    Unit baru akan langsung aktif dan dapat dipakai untuk akses pengguna serta data database orang.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" class="mt-6 min-w-0 grid grid-cols-1 gap-4 sm:grid-cols-2">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="space-y-1.5">
            <label for="nama_unit" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                Nama Unit
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                    corporate_fare
                </span>
                <input
                    id="nama_unit"
                    type="text"
                    name="nama_unit"
                    value="{{ old('nama_unit', $unit->nama_unit) }}"
                    required
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    placeholder="Contoh: Intelijen"
                >
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="kode" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                Kode
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                    tag
                </span>
                <input
                    id="kode"
                    type="text"
                    name="kode"
                    value="{{ old('kode', $unit->kode) }}"
                    required
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    placeholder="Contoh: INT"
                >
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="slug" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                Slug Route Legacy
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-faint">
                    link
                </span>
                <input
                    id="slug"
                    type="text"
                    name="slug_display" disabled
                    value="{{ old('slug', $unit->slug) }}"
                    class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 pl-9 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface"
                    placeholder="Contoh: unit1"
                >
            </div>
        </div>

        <div class="space-y-1.5">
            <label for="is_active" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                Status
            </label>
            <select
                id="is_active"
                name="is_active_display" disabled
                class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm text-muted outline-none transition focus:border-accent focus:bg-surface badig-field"
            >
                <option value="1" @selected(old('is_active', $unit->is_active) == 1)>Aktif</option>
                <option value="0" @selected(old('is_active', $unit->is_active) == 0)>Nonaktif</option>
            </select>
        </div>

        <div class="space-y-1.5 sm:col-span-2">
            <label for="deskripsi" class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                Deskripsi
            </label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                rows="4"
                class="w-full rounded-xl border border-line bg-surface-raised px-3 py-2.5 text-sm text-muted outline-none transition placeholder:text-faint focus:border-accent focus:bg-surface badig-field"
                placeholder="Jelaskan fungsi unit ini secara singkat"
            >{{ old('deskripsi', $unit->deskripsi) }}</textarea>
        </div>

        <div class="flex flex-col gap-2 border-t border-line pt-4 sm:col-span-2 sm:flex-row sm:items-center">
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong badig-action badig-action-primary"
            >
                <span class="material-symbols-outlined text-base">save</span>
                Simpan
            </button>

            <a
                href="{{ route('super-admin.units.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg border border-line bg-surface px-5 py-2.5 text-sm font-semibold text-muted transition hover:bg-surface-raised badig-action badig-action-secondary"
            >
                <span class="material-symbols-outlined text-base text-faint">arrow_back</span>
                Kembali
            </a>
        </div>
    </form>
</div>
