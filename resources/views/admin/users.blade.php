@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-line bg-gradient-to-br from-surface-raised to-surface p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between badig-card">
        <div class="space-y-1">
            <!-- <div class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">
                <span class="material-symbols-outlined text-base text-accent">group</span>
                Manajemen Pengguna
            </div> -->
            <h2 class="text-xl font-semibold tracking-tight text-ink">Manajemen Akses Pengguna</h2>
            <p class="max-w-2xl text-sm leading-6 text-muted">
                Kelola akun, peran, profil dasar, dan penempatan unit secara konsisten dari satu halaman.
            </p>
        </div>

        <button
            type="button"
            id="open-drawer-btn"
            class="inline-flex w-fit items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong active:scale-[0.99] badig-action badig-action-primary"
        >
            <span class="material-symbols-outlined text-base">person_add</span>
            Registrasi Pengguna
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-surface shadow-sm badig-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-line text-sm">
                <thead class="bg-surface-raised">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Nama</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Email</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">NRP</th>
                        <!-- <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Role</th> -->
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Unit</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line bg-surface text-muted">
                    @forelse($users as $user)
                        <tr class="transition-colors duration-150 {{ $user->is_locked ? 'bg-[var(--danger-soft)]' : 'hover:bg-surface-raised' }}">
                            <td class="whitespace-nowrap px-4 py-3 font-semibold text-ink">{{ $user->name }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-muted">{{ $user->email }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-muted">{{ $user->nrp ?: '-' }}</td>
                            <!-- <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex rounded-full border border-line bg-surface-raised px-2.5 py-1 text-[11px] font-semibold text-muted badig-badge">
                                    {{ $user->roleName() }}
                                </span>
                            </td> -->
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-muted">{{ $user->unitName() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                @if($user->is_locked)
                                    <span class="inline-flex items-center gap-1 rounded-full border border-danger/20 bg-[var(--danger-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-danger badig-badge">
                                        <span class="size-1.5 rounded-full bg-danger"></span>
                                        Locked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full border border-accent/20 bg-[var(--accent-soft)] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-accent badig-badge">
                                        <span class="size-1.5 rounded-full bg-accent"></span>
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div x-data="{
                                    open: false,
                                    top: 0,
                                    left: 0,
                                    menuWidth: 160,

                                    toggle() {
                                        this.open = !this.open;
                                        if (this.open) {
                                            this.$nextTick(() => this.updatePosition());
                                        }
                                    },

                                    close() {
                                        this.open = false;
                                    },

                                    updatePosition() {
                                        const trigger = this.$refs.trigger;
                                        const menu = this.$refs.menu;
                                        if (!trigger) return;
                                        const rect = trigger.getBoundingClientRect();
                                        const gap = 8;
                                        const viewportPadding = 12;
                                        const menuHeight = menu?.offsetHeight || 100;

                                        let calculatedTop = rect.bottom + gap;
                                        if (calculatedTop + menuHeight > window.innerHeight - viewportPadding) {
                                            calculatedTop = rect.top - menuHeight - gap;
                                        }

                                        let calculatedLeft = rect.right - this.menuWidth;
                                        calculatedLeft = Math.max(viewportPadding, Math.min(calculatedLeft, window.innerWidth - this.menuWidth - viewportPadding));

                                        this.top = calculatedTop;
                                        this.left = calculatedLeft;
                                    }
                                }" class="inline-flex" @keydown.escape.window="close()" @resize.window="open && updatePosition()" @scroll.window="open && updatePosition()">
                                    <button x-ref="trigger" type="button" @click.stop="toggle()" class="badig-focus inline-flex h-8 items-center gap-1.5 rounded-lg border border-line bg-surface px-2.5 text-xs font-semibold text-ink shadow-sm transition hover:bg-surface-raised" :aria-expanded="open" aria-haspopup="menu">
                                        <flux:icon name="ellipsis-vertical" class="size-4 text-muted" />
                                        <span>Aksi</span>
                                        <span class="transition-transform duration-200" :class="{ 'rotate-180': open }">
                                            <flux:icon name="chevron-down" class="size-3 text-muted" />
                                        </span>
                                    </button>

                                    <template x-teleport="body">
                                        <div x-cloak x-show="open" x-ref="menu" role="menu" @click.outside="close()" @click.stop
                                            x-transition:enter="transition duration-150 ease-out"
                                            x-transition:enter-start="translate-y-1 scale-95 opacity-0"
                                            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                            x-transition:leave="transition duration-100 ease-in"
                                            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                                            x-transition:leave-end="translate-y-1 scale-95 opacity-0"
                                            :style="`top: ${top}px; left: ${left}px; width: ${menuWidth}px;`"
                                            class="fixed z-[9999] origin-top-right rounded-xl border border-line bg-surface p-1.5 text-left shadow-2xl space-y-0.5">

                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="w-full">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" role="menuitem" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium {{ $user->is_locked ? 'text-emerald-500 hover:bg-emerald-500/10' : 'text-amber-500 hover:bg-amber-500/10' }} transition">
                                                        <flux:icon name="{{ $user->is_locked ? 'lock-open' : 'lock-closed' }}" class="size-4 shrink-0" />
                                                        <span>{{ $user->is_locked ? 'Buka Akses' : 'Kunci Akun' }}</span>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="w-full" data-confirm="Apakah Anda yakin ingin menghapus akun pengguna {{ $user->name }} ({{ $user->email }})? Data pengguna akan dihapus secara permanen.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" role="menuitem" class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-500/10">
                                                        <flux:icon name="trash" class="size-4 shrink-0 text-red-500" />
                                                        <span>Hapus Akun</span>
                                                    </button>
                                                </form>
                                            @else
                                                <div class="px-2.5 py-1.5 text-[11px] text-muted italic">Akun Sendiri</div>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center justify-center gap-2">
                                    <div class="flex size-14 items-center justify-center rounded-2xl bg-surface-raised text-muted">
                                        <span class="material-symbols-outlined text-2xl">person_off</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-ink">Belum ada data pengguna</h3>
                                    <p class="text-sm leading-6 text-muted">Mulai dengan menambahkan akun baru melalui tombol registrasi di atas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line bg-surface-raised px-4 py-3">
            <div class="flex justify-end">{{ $users->links() }}</div>
        </div>
    </div>
</div>

<div id="drawer-overlay" class="fixed inset-0 z-[90] hidden bg-[var(--accent-soft)] opacity-0 backdrop-blur-sm transition-all duration-300"></div>
<div id="user-drawer" class="fixed inset-y-0 right-0 z-[100] flex w-full max-w-sm flex-col border-l border-line bg-surface p-5 shadow-2xl transition-transform duration-300 ease-in-out translate-x-full">
    <div class="mb-5 flex items-center justify-between border-b border-line pb-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-lg text-ink">person_add</span>
            <h3 class="text-sm font-semibold text-ink">Registrasi Pengguna Baru</h3>
        </div>
        <button type="button" id="close-drawer-btn" class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition hover:bg-surface-raised hover:text-ink badig-action badig-action-secondary">
            <span class="material-symbols-outlined text-lg">close</span>
        </button>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="flex-1 space-y-4 overflow-y-auto pr-1">
        @csrf
        <x-ui.input name="name" label="Nama Lengkap Personel" required />
        <x-ui.input name="email" label="Alamat Email Dinas" type="email" required />
        <x-ui.input name="password" label="Kata Sandi Awal (min. 12 karakter)" type="password" required revealable />
        <x-ui.input name="password_confirmation" label="Konfirmasi Kata Sandi" type="password" required revealable />
        <x-ui.input name="nrp" label="NRP" />
        <x-ui.input name="phone" label="No HP" />

        <div class="space-y-1.5">
            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Otoritas Akses</label>
            <select name="role_id" class="h-11 w-full rounded-xl border border-line bg-surface-raised px-3 text-sm font-medium text-ink focus:border-accent focus:outline-none focus:ring-0 badig-field" required>
                <option value="" disabled selected style="background-color: var(--surface); color: var(--muted);">Pilih role</option>
                <option value="1" style="background-color: var(--surface); color: var(--ink);">Role Unit 1</option>
                <option value="2" style="background-color: var(--surface); color: var(--ink);">Role Unit 2</option>
                <option value="3" style="background-color: var(--surface); color: var(--ink);">Role Unit 3</option>
                <option value="4" style="background-color: var(--surface); color: var(--ink);">Role Unit 4</option>
                <option value="5" style="background-color: var(--surface); color: var(--ink);">Super Admin</option>
            </select>
        </div>

        <p class="rounded-xl border border-line bg-surface-raised px-3 py-2 text-xs leading-5 text-muted">Role Unit 1–4 otomatis dipetakan ke unit dengan nomor yang sama. Super Admin tidak terikat unit.</p>

        <div class="space-y-1.5">
            <label class="block text-[11px] font-semibold uppercase tracking-[0.24em] text-muted">Alamat</label>
            <textarea name="address" rows="3" class="w-full rounded-xl border border-line bg-surface-raised px-3 py-2.5 text-sm text-ink focus:border-accent focus:outline-none focus:ring-0 badig-field"></textarea>
        </div>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2.5 text-sm font-semibold text-[#07140c] transition hover:bg-accent-strong active:scale-[0.99] badig-action badig-action-primary">
            <span class="material-symbols-outlined text-base">save</span>
            Simpan Personel Baru
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script @cspNonce>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('open-drawer-btn');
        const closeBtn = document.getElementById('close-drawer-btn');
        const overlay = document.getElementById('drawer-overlay');
        const drawer = document.getElementById('user-drawer');

        const openDrawer = () => {
            overlay.classList.remove('hidden');
            requestAnimationFrame(() => overlay.classList.add('opacity-100'));
            drawer.classList.remove('translate-x-full');
        };

        const closeDrawer = () => {
            overlay.classList.remove('opacity-100');
            drawer.classList.add('translate-x-full');
            window.setTimeout(() => overlay.classList.add('hidden'), 300);
        };

        openBtn?.addEventListener('click', openDrawer);
        closeBtn?.addEventListener('click', closeDrawer);
        overlay?.addEventListener('click', closeDrawer);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && drawer && !drawer.classList.contains('translate-x-full')) {
                closeDrawer();
            }
        });
    });
</script>
@endpush
