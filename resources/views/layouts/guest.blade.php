<!DOCTYPE html>
<html lang="id" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-layout.seo-head />
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
<body class="min-h-screen bg-base text-ink antialiased">
    <div class="fixed right-4 top-4 z-50 sm:right-6 sm:top-6">
        <x-ui.theme-toggle />
    </div>

    @yield('content')

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
