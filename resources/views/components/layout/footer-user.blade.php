@props([
    'user',
    'avatarUrl' => null,
    'initial' => 'U',
])

<details class="group relative mt-3 border-t border-white/8 pt-3">
    <summary class="badig-user-panel flex cursor-pointer list-none items-center gap-3 rounded-xl p-2.5 text-left transition hover:bg-white/5">
        <span class="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/10 bg-white/8 text-xs font-bold text-white">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Foto profil" class="size-full object-cover">
            @else
                {{ $initial }}
            @endif
        </span>

        <span class="badig-sidebar-text min-w-0 flex-1" x-show="!sidebarMini">
            <span class="block truncate text-xs font-semibold text-white">{{ $user->name }}</span>
            <span class="mt-0.5 block truncate text-[10px] text-white/45">
                {{ method_exists($user, 'roleName') ? $user->roleName() : 'Pengguna' }}
            </span>
        </span>

        <flux:icon name="ellipsis-horizontal" class="badig-sidebar-text size-4 text-white/45" x-show="!sidebarMini" />
    </summary>

    <div class="absolute bottom-full left-0 right-0 z-20 mb-2 overflow-hidden rounded-xl border border-line bg-surface p-2 shadow-[var(--shadow-soft)]" x-show="!sidebarMini">
        @if(Route::has('profile.edit'))
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-ink transition hover:bg-surface-raised">
                <flux:icon name="user-circle" class="size-4 text-muted" />
                Pengaturan Profil
            </a>
        @endif

        @if(Route::has('logout'))
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-semibold text-danger transition hover:bg-[var(--danger-soft)]">
                    <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                    Keluar Sistem
                </button>
            </form>
        @endif
    </div>
</details>
