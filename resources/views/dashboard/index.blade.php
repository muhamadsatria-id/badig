@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@php
    $unitMeta = [
        'unit1' => ['label' => 'Ideologi Politik', 'icon' => 'shield-check'],
        'unit2' => ['label' => 'Ekonomi', 'icon' => 'banknotes'],
        'unit3' => ['label' => 'Sosial Budaya', 'icon' => 'user-group'],
        'unit4' => ['label' => 'Keamanan Negara', 'icon' => 'shield-exclamation'],
    ];

    $totalData = collect($counts ?? [])->sum();
    $recentLogsCollection = collect($recentLogs ?? []);
    $unit4TrendCollection = collect($unit4Trend ?? []);
    $unit2PieCollection = collect($unit2Pie ?? []);
    $maxUnitCount = max(1, collect($counts ?? [])->max() ?? 1);

    $activityByDate = $recentLogsCollection
        ->filter(fn ($log) => !empty($log->created_at))
        ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'))
        ->map->count();

    $heatmapLevels = collect(range(83, 0))->map(function ($offset) use ($activityByDate) {
        $count = (int) ($activityByDate[now()->subDays($offset)->format('Y-m-d')] ?? 0);
        return min(4, $count);
    });
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #map { position: relative; z-index: 0; }
    #map .leaflet-pane, #map .leaflet-control { z-index: 1; }
    .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: var(--bg-surface); color: var(--text-accent); }
    .leaflet-popup-content-wrapper { border: 1px solid var(--border-subtle); border-radius: 12px; box-shadow: var(--shadow-soft); }
    .leaflet-popup-content { margin: 0; width: 280px !important; }
    .badig-map-popup { overflow: hidden; border-radius: 11px; font-family: Inter, sans-serif; }
    .badig-map-popup__head { padding: 13px 14px 11px; border-bottom: 1px solid var(--border-subtle); background: var(--bg-surface-raised); }
    .badig-map-popup__unit { font-size: 9px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: var(--accent); }
    .badig-map-popup__title { margin-top: 4px; font-size: 13px; line-height: 1.35; font-weight: 750; color: var(--text-accent); }
    .badig-map-popup__body { padding: 12px 14px 14px; }
    .badig-map-popup__meta { display: grid; grid-template-columns: 72px 1fr; gap: 5px 8px; margin-bottom: 10px; font-size: 10px; }
    .badig-map-popup__meta dt { color: var(--text-muted); }
    .badig-map-popup__meta dd { margin: 0; color: var(--text-accent); font-weight: 600; }
    .badig-map-popup__desc { margin: 0 0 11px; font-size: 10px; line-height: 1.55; color: var(--text-muted); }
    .badig-map-popup__actions { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
    .badig-map-popup__actions a,.badig-map-popup__actions button { min-height: 34px; border-radius: 8px; font-size: 9px; font-weight: 750; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: center; }
    .badig-map-popup__detail { border: 1px solid var(--border-subtle); background: var(--bg-surface); color: var(--text-accent); }
    .badig-map-popup__route { border: 1px solid transparent; background: var(--accent); color: var(--accent-contrast); }
    .dark .leaflet-tile-pane { filter: grayscale(.88) invert(.90) hue-rotate(95deg) brightness(.68) contrast(.92); }
</style>
@endpush

@section('content')
<div
    class="space-y-5"
    x-data="{ filterOpen: false, actionFilter: 'all' }"
    @toggle-page-filter.window="filterOpen = !filterOpen"
>
    <section class="relative overflow-hidden rounded-2xl border border-line bg-surface px-5 py-5 shadow-[var(--shadow-card)] sm:px-6 badig-card">
        <div class="badig-grid-pattern pointer-events-none absolute inset-0 opacity-35 [mask-image:linear-gradient(to_right,black,transparent_72%)]"></div>
        <div class="pointer-events-none absolute -right-20 -top-20 size-64 rounded-full bg-[var(--accent-soft)] blur-3xl"></div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <x-ui.badge color="success" dot>System Online</x-ui.badge>
                <h2 class="mt-4 text-2xl font-bold tracking-[-0.035em] text-ink sm:text-3xl">
                    Ringkasan operasi BADIG
                </h2>
                <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                    Monitoring terpusat data personel, unit intelijen, aktivitas sistem, dan persebaran koordinat wilayah hukum.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:flex">
                <div class="rounded-xl border border-line bg-surface-raised px-4 py-3">
                    <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-muted">Total Data</p>
                    <p class="mt-1 text-xl font-bold tracking-tight text-ink">{{ number_format($totalData) }}</p>
                </div>
                <div class="rounded-xl border border-line bg-surface-raised px-4 py-3">
                    <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-muted">Log Terbaru</p>
                    <p class="mt-1 text-xl font-bold tracking-tight text-ink">{{ number_format($recentLogsCollection->count()) }}</p>
                </div>
            </div>
        </div>
    </section>

    <div x-show="filterOpen" x-cloak x-transition class="rounded-xl border border-line bg-surface p-3 shadow-[var(--shadow-card)]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold text-ink">Filter aktivitas sistem</p>
                <p class="mt-0.5 text-[11px] text-muted">Menyaring tabel aktivitas tanpa memuat ulang halaman.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'Semua', 'create' => 'Tambah', 'update' => 'Perbarui', 'delete' => 'Hapus'] as $key => $label)
                    <button
                        type="button"
                        @click="actionFilter = '{{ $key }}'"
                        class="badig-focus rounded-lg border px-3 py-2 text-xs font-semibold transition badig-action badig-action-secondary"
                        :class="actionFilter === '{{ $key }}' ? 'border-accent bg-[var(--accent-soft)] text-accent-strong dark:text-accent' : 'border-line bg-surface-raised text-muted hover:text-ink' badig-action badig-action-primary"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($unitMeta as $key => $meta)
            <x-ui.stat-widget
                :label="$meta['label']"
                :value="number_format($counts[$key] ?? 0)"
                suffix="data"
                :icon="$meta['icon']"
            />
        @endforeach
    </div>


    @if(collect($accessibleUnitManagers ?? [])->isNotEmpty())
        <x-ui.card title="File Manager Unit" subtitle="Akses berkas lampiran berdasarkan unit yang berada dalam otorisasi akun Anda." icon="folder-open">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($accessibleUnitManagers as $manager)
                    <a href="{{ $manager['url'] }}" class="group rounded-xl border border-line bg-surface-raised p-4 transition hover:border-accent/40 hover:bg-surface">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex size-9 items-center justify-center rounded-lg bg-[var(--accent-soft)] text-accent"><flux:icon name="folder" class="size-4.5" /></span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted">Unit {{ $manager['id'] }}</span>
                        </div>
                        <h4 class="mt-3 text-sm font-semibold text-ink">{{ $manager['name'] }}</h4>
                        <div class="mt-3 flex items-center justify-between text-xs text-muted">
                            <span>{{ number_format($manager['files']) }} file</span>
                            <span>{{ $manager['size'] >= 1048576 ? number_format($manager['size']/1048576, 1).' MB' : number_format($manager['size']/1024, 1).' KB' }}</span>
                        </div>
                        <div class="mt-3 flex items-center gap-1 text-xs font-semibold text-accent">Buka File Manager <flux:icon name="arrow-right" class="size-3.5 transition group-hover:translate-x-0.5" /></div>
                    </a>
                @endforeach
            </div>
        </x-ui.card>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        <x-ui.chart-card
            class="xl:col-span-8"
            title="Tren Keamanan Negara"
            subtitle="Perubahan kuantitas berkas berdasarkan kategori pemantauan."
            icon="presentation-chart-line"
        >
            <canvas id="unit4Chart"></canvas>
            @if($unit4TrendCollection->isEmpty())
                <div class="absolute inset-0 flex items-center justify-center text-xs italic text-muted">
                    Belum ada data tren untuk ditampilkan.
                </div>
            @endif
        </x-ui.chart-card>

        <x-ui.card
            class="xl:col-span-4"
            title="Distribusi Data Unit"
            subtitle="Perbandingan volume data antarunit."
            icon="bars-3-bottom-left"
        >
            <div class="space-y-5">
                @foreach($unitMeta as $key => $meta)
                    @php
                        $unitCount = (int) ($counts[$key] ?? 0);
                        $percentage = round(($unitCount / $maxUnitCount) * 100);
                    @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="truncate text-xs font-medium text-ink">{{ $meta['label'] }}</span>
                            <span class="text-xs font-semibold text-muted">{{ number_format($unitCount) }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-surface-raised">
                            <div class="h-full rounded-full bg-accent transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-12">
        <x-ui.chart-card
            class="xl:col-span-7"
            title="Komposisi Data Ekonomi"
            subtitle="Proporsi aset, objek vital, dan klaster pemantauan ekonomi."
            icon="chart-pie"
        >
            <canvas id="unit2Chart"></canvas>
            @if($unit2PieCollection->isEmpty())
                <div class="absolute inset-0 flex items-center justify-center text-xs italic text-muted">
                    Belum ada data komposisi untuk ditampilkan.
                </div>
            @endif
        </x-ui.chart-card>

        <x-ui.card
            class="xl:col-span-5"
            title="Intensitas Aktivitas"
            subtitle="Distribusi log sistem selama 12 minggu terakhir."
            icon="calendar-days"
        >
            <x-ui.heatmap :levels="$heatmapLevels" :columns="12" />

            <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-line bg-surface-raised p-3">
                    <p class="text-[9px] font-semibold uppercase tracking-[0.15em] text-muted">Hari Aktif</p>
                    <p class="mt-1 text-lg font-bold text-ink">{{ $activityByDate->count() }}</p>
                </div>
                <div class="rounded-xl border border-line bg-surface-raised p-3">
                    <p class="text-[9px] font-semibold uppercase tracking-[0.15em] text-muted">Total Log</p>
                    <p class="mt-1 text-lg font-bold text-ink">{{ $recentLogsCollection->count() }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    @if(true)
        <x-ui.card
            title="Peta Koordinat Kerawanan & Aset"
            subtitle="Persebaran titik intelijen geografis pada wilayah hukum."
            icon="map"
        >
            <x-slot:actions>
                <x-ui.badge id="map-status-badge" color="success" dot>Map Active</x-ui.badge>
            </x-slot:actions>

            <div id="map" class="h-[420px] w-full overflow-hidden rounded-xl border border-line bg-surface-raised"></div>
            <p id="map-empty-note" class="mt-3 hidden text-xs italic text-muted">
                Belum ada titik koordinat yang tercatat untuk ditampilkan.
            </p>
        </x-ui.card>
    @endif

    <x-ui.card
        title="Aktivitas Sistem Terbaru"
        subtitle="Riwayat perubahan data untuk audit dan akuntabilitas operasional."
        icon="clipboard-document-list"
        :padding="false"
    >
        <x-slot:actions>
            <x-ui.badge color="success" dot>Logs Secured</x-ui.badge>
        </x-slot:actions>

        <div class="badig-scrollbar overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap text-left">Waktu</th>
                        <th class="whitespace-nowrap text-left">Operasi</th>
                        <th class="min-w-80 text-left">Deskripsi</th>
                        <th class="whitespace-nowrap text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogsCollection as $log)
                        @php
                            $action = strtolower((string) ($log->action ?? 'aktivitas'));
                            $actionKey = str_contains($action, 'hapus') || str_contains($action, 'delete') ? 'delete'
                                : (str_contains($action, 'ubah') || str_contains($action, 'update') || str_contains($action, 'edit') ? 'update'
                                : (str_contains($action, 'tambah') || str_contains($action, 'create') || str_contains($action, 'store') ? 'create' : 'other'));
                            $badgeColor = match ($actionKey) {
                                'create' => 'success',
                                'update' => 'warning',
                                'delete' => 'danger',
                                default => 'neutral',
                            };
                        @endphp
                        <tr data-action-key="{{ $actionKey }}" x-show="actionFilter === 'all' || actionFilter === $el.dataset.actionKey">
                            <td class="whitespace-nowrap font-mono text-[11px] text-muted">
                                {{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td>
                                <x-ui.badge :color="$badgeColor">{{ $log->action ?? 'Aktivitas' }}</x-ui.badge>
                            </td>
                            <td class="max-w-xl text-xs leading-5 text-muted" title="{{ $log->description ?? '' }}">
                                {{ $log->description ?? '-' }}
                            </td>
                            <td class="text-right">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-accent-strong dark:text-accent">
                                    <span class="size-1.5 rounded-full bg-accent"></span>
                                    Tercatat
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-sm text-muted">
                                Belum ada aktivitas sistem yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script @cspNonce>
    document.addEventListener('DOMContentLoaded', function () {
        const unit4Labels = @json($unit4TrendCollection->keys()->values());
        const unit4Data = @json($unit4TrendCollection->values());
        const unit2Labels = @json($unit2PieCollection->keys()->values());
        const unit2Data = @json($unit2PieCollection->values());

        let unit4Chart = null;
        let unit2Chart = null;

        function cssVar(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        }

        function rgba(hex, alpha) {
            const normalized = hex.replace('#', '');
            if (!/^[0-9a-fA-F]{6}$/.test(normalized)) {
                return `rgba(34, 197, 94, ${alpha})`;
            }
            const number = parseInt(normalized, 16);
            const red = (number >> 16) & 255;
            const green = (number >> 8) & 255;
            const blue = number & 255;
            return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
        }

        function renderCharts() {
            if (typeof Chart === 'undefined') return;

            const accent = cssVar('--accent');
            const text = cssVar('--text-muted');
            const line = cssVar('--border-subtle');
            const surface = cssVar('--bg-surface');

            if (unit4Chart) unit4Chart.destroy();
            if (unit2Chart) unit2Chart.destroy();

            const unit4Canvas = document.getElementById('unit4Chart');
            if (unit4Canvas && unit4Labels.length > 0) {
                unit4Chart = new Chart(unit4Canvas, {
                    type: 'line',
                    data: {
                        labels: unit4Labels,
                        datasets: [{
                            label: 'Kuantitas Berkas',
                            data: unit4Data,
                            borderColor: accent,
                            backgroundColor: rgba(accent, 0.10),
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: surface,
                            pointBorderColor: accent,
                            pointBorderWidth: 2,
                            tension: 0.38,
                            fill: true,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { intersect: false, mode: 'index' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                displayColors: false,
                                backgroundColor: cssVar('--text-accent'),
                                titleColor: cssVar('--bg-surface'),
                                bodyColor: cssVar('--bg-surface'),
                                padding: 10,
                                cornerRadius: 8,
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: line, borderDash: [4, 4] },
                                ticks: { color: text, precision: 0, font: { family: 'Inter', size: 10 } },
                            },
                            x: {
                                border: { display: false },
                                grid: { display: false },
                                ticks: { color: text, font: { family: 'Inter', size: 10 } },
                            },
                        },
                    },
                });
            }

            const unit2Canvas = document.getElementById('unit2Chart');
            if (unit2Canvas && unit2Labels.length > 0) {
                const opacities = [1, 0.82, 0.66, 0.52, 0.38, 0.26, 0.16];
                unit2Chart = new Chart(unit2Canvas, {
                    type: 'doughnut',
                    data: {
                        labels: unit2Labels,
                        datasets: [{
                            data: unit2Data,
                            backgroundColor: unit2Labels.map((_, index) => rgba(accent, opacities[index % opacities.length])),
                            borderColor: surface,
                            borderWidth: 3,
                            hoverOffset: 5,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    color: text,
                                    padding: 14,
                                    font: { family: 'Inter', size: 10, weight: '500' },
                                },
                            },
                        },
                    },
                });
            }
        }

        renderCharts();
        window.addEventListener('badig-theme-changed', function () {
            window.setTimeout(renderCharts, 50);
        });

        @if(true)
            (function initBadigMap() {
                const mapBadge = document.getElementById('map-status-badge');
                const emptyNote = document.getElementById('map-empty-note');

                function setBadge(text, state) {
                    if (!mapBadge) return;

                    const dot = document.createElement('span');
                    dot.className = `size-1.5 rounded-full ${state === 'error' ? 'bg-danger' : 'bg-accent'}`;
                    mapBadge.replaceChildren(dot, document.createTextNode(String(text)));
                }

                function escapeHtml(value) {
                    return String(value ?? '').replace(/[&<>'"]/g, function (character) {
                        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character];
                    });
                }

                window.openBadigGoogleRoute = function (destinationLat, destinationLng) {
                    const openRoute = function (originLat, originLng) {
                        const base = 'https://www.google.com/maps/dir/?api=1';
                        const destination = '&destination=' + encodeURIComponent(destinationLat + ',' + destinationLng);
                        const travelMode = '&travelmode=driving';
                        const origin = originLat && originLng ? '&origin=' + encodeURIComponent(originLat + ',' + originLng) : '';
                        window.open(base + origin + destination + travelMode, '_blank', 'noopener');
                    };

                    if (!navigator.geolocation) {
                        openRoute(null, null);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        function (position) { openRoute(position.coords.latitude, position.coords.longitude); },
                        function () { openRoute(null, null); },
                        { enableHighAccuracy: true, timeout: 6000, maximumAge: 60000 }
                    );
                };

                document.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-route-lat][data-route-lng]');
                    if (!button) return;
                    window.openBadigGoogleRoute(Number(button.dataset.routeLat), Number(button.dataset.routeLng));
                });

                let map;
                try {
                    map = L.map('map', { scrollWheelZoom: false }).setView([-6.9175, 106.9266], 12);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap',
                    }).addTo(map);
                } catch (error) {
                    console.error('Gagal menginisialisasi peta:', error);
                    setBadge('Map Error', 'error');
                    return;
                }

                const points = @json($points ?? []);
                const validPoints = points.filter((point) => point && point.lat !== null && point.lng !== null);

                if (validPoints.length === 0) {
                    if (emptyNote) emptyNote.classList.remove('hidden');
                } else {
                    const bounds = [];
                    validPoints.forEach((point) => {
                        const lat = Number(point.lat);
                        const lng = Number(point.lng);
                        if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                        const popup = `
                            <article class="badig-map-popup">
                                <header class="badig-map-popup__head">
                                    <div class="badig-map-popup__unit">${escapeHtml(point.unit || 'Unit BADIG')}</div>
                                    <div class="badig-map-popup__title">${escapeHtml(point.title || point.label)}</div>
                                </header>
                                <div class="badig-map-popup__body">
                                    <dl class="badig-map-popup__meta">
                                        <dt>Kategori</dt><dd>${escapeHtml(point.kategori || '-')}</dd>
                                        <dt>Tanggal</dt><dd>${escapeHtml(point.date || '-')}</dd>
                                        <dt>Lokasi</dt><dd>${escapeHtml(point.address || '-')}</dd>
                                        <dt>Koordinat</dt><dd>${lat.toFixed(6)}, ${lng.toFixed(6)}</dd>
                                    </dl>
                                    <p class="badig-map-popup__desc">${escapeHtml(point.description || 'Tidak ada keterangan tambahan.')}</p>
                                    <div class="badig-map-popup__actions">
                                        <a class="badig-map-popup__detail" href="${escapeHtml(point.detail_url || '#')}">Lihat Detail</a>
                                        <button class="badig-map-popup__route" type="button" data-route-lat="${lat}" data-route-lng="${lng}">Buka Rute</button>
                                    </div>
                                </div>
                            </article>`;

                        L.marker([lat, lng], { icon: L.divIcon(window.badigMapPinIcon()) }).addTo(map).bindPopup(popup);
                        bounds.push([lat, lng]);
                    });

                    if (bounds.length > 1) {
                        map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
                    } else if (bounds.length === 1) {
                        map.setView(bounds[0], 14);
                    }
                }

                setBadge('Map Active', 'success');
                window.setTimeout(() => map.invalidateSize(), 250);
                window.addEventListener('resize', () => map.invalidateSize());
            })();
        @endif
    });
</script>
@endpush
