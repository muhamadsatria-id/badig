@props([
    'currentUser',
    'sidebarUnits' => collect(),
    'avatarUrl' => null,
    'userInitial' => 'U',
])

@php
    $unitLabels = [
        1 => 'Ideologi Politik',
        2 => 'Ekonomi',
        3 => 'Sosial Budaya',
        4 => 'Keamanan Negara',
    ];

    $unitIcons = [
        1 => 'shield-check',
        2 => 'banknotes',
        3 => 'user-group',
        4 => 'shield-exclamation',
    ];

    $isSuperAdmin = $currentUser->isSuperAdmin();
@endphp

<aside
    class="badig-sidebar fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-white/6 px-3.5 py-4 transition-all duration-200"
    :class="[
        mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarMini ? 'lg:w-20' : 'lg:w-72'
    ]"
>
    <div class="mb-5 flex h-12 items-center gap-3 px-2" :class="sidebarMini ? 'lg:justify-center' : ''">
        <!-- <span class="relative flex size-10 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/6 text-xs font-black tracking-tight text-white">
            B
            <span class="absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-[#101411] bg-accent"></span>
        </span> -->
        <img
                            src="{{ asset('logo-badig.webp') }}"
                            alt="Logo BADIG"
                            class="size-12 object-contain"
                        >
        <div class="badig-sidebar-text min-w-0" x-show="!sidebarMini" x-cloak>
            <div class="text-sm font-extrabold tracking-[0.12em] text-white">BADIG</div>
            <div class="mt-0.5 truncate text-[8px] font-semibold uppercase tracking-[0.15em] text-white/40">
                Basis Data Terintegrasi Sat Intelkam
            </div>
        </div>
    </div>

    <nav class="badig-scrollbar flex-1 space-y-5 overflow-y-auto pr-1">
        <div class="space-y-1">
            <p class="badig-sidebar-text px-3 pb-1 text-[9px] font-bold uppercase tracking-[0.20em] text-white/30" x-show="!sidebarMini" x-cloak>MAIN</p>

            @if(Route::has('dashboard'))
                <a href="{{ route('dashboard') }}" title="Dashboard" class="badig-sidebar-link {{ request()->routeIs('dashboard') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                    <flux:icon name="squares-2x2" class="size-4.5 shrink-0" />
                    <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Dashboard</span>
                </a>
            @endif

            @if($currentUser->canAccessDatabaseOrang() && Route::has('database-orang.index'))
                <a href="{{ route('database-orang.index') }}" title="Data Team" class="badig-sidebar-link {{ request()->routeIs('database-orang.*') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                    <flux:icon name="users" class="size-4.5 shrink-0" />
                    <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Data Orang</span>
                </a>
            @endif

            @if($isSuperAdmin && Route::has('pimpinan.file-manager'))
                <a href="{{ route('pimpinan.file-manager') }}" title="File Manager" class="badig-sidebar-link {{ request()->routeIs('pimpinan.file-manager*') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                    <flux:icon name="folder" class="size-4.5 shrink-0" />
                    <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>File Manager</span>
                </a>
            @endif
        </div>

        @if($isSuperAdmin)
            <div class="space-y-1">
                <p class="badig-sidebar-text px-3 pb-1 text-[9px] font-bold uppercase tracking-[0.20em] text-white/30" x-show="!sidebarMini" x-cloak>SUPER ADMIN</p>

                @if(Route::has('super-admin.index'))
                    <a href="{{ route('super-admin.index') }}" title="Panel Super Admin" class="badig-sidebar-link {{ request()->routeIs('super-admin.index') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                        <flux:icon name="shield-check" class="size-4.5 shrink-0" />
                        <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Panel Super Admin</span>
                    </a>
                @endif

                <!-- @if(Route::has('super-admin.units.index'))
                    <a href="{{ route('super-admin.units.index') }}" title="Master Unit" class="badig-sidebar-link {{ request()->routeIs('super-admin.units.*') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                        <flux:icon name="rectangle-stack" class="size-4.5 shrink-0" />
                        <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Master Unit</span>
                    </a>
                @endif -->
            </div>
        @endif

        <div class="space-y-1">
                <p class="badig-sidebar-text px-3 pb-1 text-[9px] font-bold uppercase tracking-[0.20em] text-white/30" x-show="!sidebarMini" x-cloak>DATA UNIT</p>

                @foreach($sidebarUnits as $unit)
                    @php
                        $unitId = (int) ($unit->id ?? 0);
                        $unitSlug = $unit->slug ?? ('unit'.$unitId);
                        $canAccessUnit = method_exists($currentUser, 'canAccessUnitId')
                            ? $currentUser->canAccessUnitId($unitId)
                            : true;
                    @endphp

                    @if($unitId > 0 && $canAccessUnit && Route::has('units.index'))
                        <div class="space-y-1">
                            <a href="{{ route('units.index', $unitSlug) }}" title="{{ $unitLabels[$unitId] ?? ('Unit '.$unitId) }}" class="badig-sidebar-link {{ request()->routeIs('units.index','units.show','units.create','units.edit') && request()->route('unit') === $unitSlug ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                                <flux:icon name="{{ $unitIcons[$unitId] ?? 'folder-open' }}" class="size-4.5 shrink-0" />
                                <span class="badig-sidebar-text truncate" x-show="!sidebarMini" x-cloak>{{ $unitLabels[$unitId] ?? ('Unit '.$unitId) }}</span>
                            </a>
                            @if(Route::has('units.file-manager'))
                                <a href="{{ route('units.file-manager', $unitSlug) }}" title="File Manager {{ $unitLabels[$unitId] ?? ('Unit '.$unitId) }}" class="badig-sidebar-link {{ request()->routeIs('units.file-manager') && request()->route('unit') === $unitSlug ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl py-2 pl-7 pr-3 text-[11px] font-semibold transition" :class="sidebarMini ? 'lg:justify-center lg:pl-3' : ''">
                                    <flux:icon name="folder" class="size-4 shrink-0" />
                                    <span class="badig-sidebar-text truncate" x-show="!sidebarMini" x-cloak>File Manager</span>
                                </a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

        @if($isSuperAdmin)
            <div class="space-y-1">
                <p class="badig-sidebar-text px-3 pb-1 text-[9px] font-bold uppercase tracking-[0.20em] text-white/30" x-show="!sidebarMini" x-cloak>SISTEM</p>

                @if(Route::has('admin.users'))
                    <a href="{{ route('admin.users') }}" title="Manajemen User" class="badig-sidebar-link {{ request()->routeIs('admin.users*') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                        <flux:icon name="user-group" class="size-4.5 shrink-0" />
                        <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Manajemen User</span>
                    </a>
                @endif

                @if(Route::has('admin.audit'))
                    <a href="{{ route('admin.audit') }}" title="Audit Trail" class="badig-sidebar-link {{ request()->routeIs('admin.audit') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                        <flux:icon name="clipboard-document-list" class="size-4.5 shrink-0" />
                        <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Audit Trail</span>
                    </a>
                @endif

                @if(Route::has('admin.backup'))
                    <a href="{{ route('admin.backup') }}" title="Backup Sistem" class="badig-sidebar-link {{ request()->routeIs('admin.backup*') ? 'badig-sidebar-link-active' : '' }} relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition" :class="sidebarMini ? 'lg:justify-center' : ''">
                        <flux:icon name="circle-stack" class="size-4.5 shrink-0" />
                        <span class="badig-sidebar-text" x-show="!sidebarMini" x-cloak>Backup Sistem</span>
                    </a>
                @endif
            </div>
        @endif
    </nav>

    <x-layout.footer-user :user="$currentUser" :avatar-url="$avatarUrl" :initial="$userInitial" />
</aside>
