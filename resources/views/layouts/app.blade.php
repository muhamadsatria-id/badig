<!DOCTYPE html>
<html lang="id" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-layout.seo-head />

    {{-- Apply the saved theme before paint to prevent a light-mode flash. --}}
    <script @cspNonce>
        (function () {
            try {
                const savedTheme = localStorage.getItem('theme');
                const theme = savedTheme === 'light' ? 'light' : 'dark';
                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.dataset.theme = theme;
                document.documentElement.style.colorScheme = theme;
            } catch (error) {
                document.documentElement.classList.add('dark');
                document.documentElement.dataset.theme = 'dark';
                document.documentElement.style.colorScheme = 'dark';
            }
        })();
    </script>
    <link
    rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    {{-- Theme state handled by BADIG themeSwitcher --}}
    @stack('styles')
</head>

<body
    class="min-h-screen bg-base text-ink antialiased"
    x-data="dashboardShell"
    :class="{ 'sidebar-mini': sidebarMini }"
>
@auth
    @php
        $currentUser = auth()->user();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('units')) {
                $unitQuery = \Illuminate\Support\Facades\DB::table('units')
                    ->whereIn('id', [1, 2, 3, 4])
                    ->orderBy('id');

                if (\Illuminate\Support\Facades\Schema::hasColumn('units', 'is_active')) {
                    $unitQuery->where(function ($query) {
                        $query->where('is_active', 1)->orWhereNull('is_active');
                    });
                }

                $sidebarUnits = $unitQuery->get()->map(function ($unit) {
                    $unit->slug = $unit->slug ?? ('unit'.$unit->id);
                    return $unit;
                });
            } else {
                $sidebarUnits = collect();
            }
        } catch (\Throwable $e) {
            $sidebarUnits = collect();
        }

        if ($sidebarUnits->isEmpty()) {
            $sidebarUnits = collect([
                (object) ['id' => 1, 'slug' => 'unit1'],
                (object) ['id' => 2, 'slug' => 'unit2'],
                (object) ['id' => 3, 'slug' => 'unit3'],
                (object) ['id' => 4, 'slug' => 'unit4'],
            ]);
        }

        $avatarUrl = $currentUser->photo ? route('profile.photo') : null;

        $userInitial = strtoupper(mb_substr($currentUser->name ?? 'U', 0, 1));
        $pageTitle = trim($__env->yieldContent('page_title', 'BADIG'));
    @endphp

    <div
        x-show="mobileOpen"
        x-cloak
        @click="mobileOpen = false"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
        aria-hidden="true"
    ></div>

    <x-layout.sidebar
        :current-user="$currentUser"
        :sidebar-units="$sidebarUnits"
        :avatar-url="$avatarUrl"
        :user-initial="$userInitial"
    />

    <main
        class="badig-main min-h-screen transition-[margin] duration-200"
        :class="sidebarMini ? 'lg:ml-20' : 'lg:ml-72'"
    >
        <x-layout.topbar :page-title="$pageTitle" />

        <section class="px-4 pb-10 pt-24 sm:px-6 lg:px-8">
            <div id="badig-content" class="mx-auto max-w-[1500px] space-y-5">
                @if(session('success'))
                    <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
                @endif

                @if(session('error'))
                    <x-ui.alert variant="error">{{ session('error') }}</x-ui.alert>
                @endif

                @if($errors->any())
                    <x-ui.alert variant="error">
                        <div class="font-semibold">Validasi gagal</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif

                @yield('content')
            </div>
        </section>
    </main>
@else
    @yield('content')
@endauth

@livewireScripts
@fluxScripts
@stack('scripts')

<script @cspNonce>
document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && !window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) {
        event.preventDefault();
    }
});
document.addEventListener('click', (event) => {
    const target = event.target.closest('[data-action]');
    if (!target) return;
    if (target.dataset.action === 'reload-page') window.location.reload();
    if (target.dataset.action === 'print-page') window.print();
});
</script>

    <x-cookie-consent />
</body>
</html>
