@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $view = app('view');
    $defaultTitle = 'BADIG | Manajemen Data Per-Unit Internal Terintegrasi';
    $rawTitle = trim((string) ($title ?? $view->yieldContent('title')));
    $pageTitle = $rawTitle !== ''
        ? (str_contains(mb_strtoupper($rawTitle), 'BADIG') ? $rawTitle : 'BADIG | '.$rawTitle)
        : $defaultTitle;

    if (mb_strlen($pageTitle) < 50) {
        $pageTitle .= ' | Sistem Manajemen Internal';
    }

    if (mb_strlen($pageTitle) < 50) {
        $pageTitle .= ' Terintegrasi';
    }

    if (mb_strlen($pageTitle) > 60) {
        $pageTitle = rtrim(mb_substr($pageTitle, 0, 59)).'…';
    }

    $defaultDescription = 'BADIG adalah aplikasi internal untuk pengelolaan data per-unit dengan kontrol akses berbasis role, pencatatan audit, dan penyimpanan dokumen terotorisasi.';
    $rawDescription = trim((string) ($description ?? $view->yieldContent('meta_description')));
    $pageDescription = $rawDescription !== '' ? $rawDescription : $defaultDescription;

    if (mb_strlen($pageDescription) > 160) {
        $pageDescription = rtrim(mb_substr($pageDescription, 0, 157)).'...';
    }

    $rawImage = trim((string) ($image ?? $view->yieldContent('og_image')));
    $pageImage = $rawImage !== '' ? $rawImage : asset('logo-badig.png');
    $canonicalUrl = url()->current();
    $isPrivatePage = auth()->check() || request()->routeIs('login', 'login.process');
    $robots = $isPrivatePage ? 'noindex, nofollow, noarchive, nosnippet' : 'index, follow, max-image-preview:large';

    $schemas = [
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'BADIG',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => url('/'),
            'description' => $pageDescription,
            'inLanguage' => 'id-ID',
        ],
        [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'BADIG',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $pageTitle,
                    'item' => $canonicalUrl,
                ],
            ],
        ],
    ];
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<meta name="author" content="BADIG">
<meta name="robots" content="{{ $robots }}">
<meta name="theme-color" content="#0f172a">
<link rel="canonical" href="{{ $canonicalUrl }}">

<meta property="og:site_name" content="BADIG">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:locale" content="id_ID">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">

<script @cspNonce type="application/ld+json">{!! json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
