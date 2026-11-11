<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StaticMapService
{
    public function snapshot(?float $latitude, ?float $longitude): array
    {
        if ($latitude === null || $longitude === null) {
            return ['data_uri' => null, 'source' => null, 'is_fallback' => true];
        }

        if (! config('services.static_map.enabled', false)) {
            return [
                'data_uri' => $this->fallbackSvg($latitude, $longitude),
                'source' => 'Pratinjau koordinat lokal',
                'is_fallback' => true,
            ];
        }

        $endpoint = (string) config('services.static_map.url', 'https://staticmap.openstreetmap.de/staticmap.php');
        $endpointParts = parse_url($endpoint);
        $allowedHosts = array_map('strtolower', (array) config('services.static_map.allowed_hosts', []));
        $endpointHost = strtolower((string) ($endpointParts['host'] ?? ''));

        if (($endpointParts['scheme'] ?? null) !== 'https'
            || $endpointHost === ''
            || ! in_array($endpointHost, $allowedHosts, true)) {
            return [
                'data_uri' => $this->fallbackSvg($latitude, $longitude),
                'source' => 'Pratinjau koordinat lokal',
                'is_fallback' => true,
            ];
        }

        $url = rtrim($endpoint, '?').'?'.http_build_query([
            'center' => $latitude.','.$longitude,
            'zoom' => 15,
            'size' => '760x320',
            'maptype' => 'mapnik',
            'markers' => $latitude.','.$longitude.',red-pushpin',
        ]);

        try {
            $response = Http::connectTimeout(3)->timeout(7)->accept('image/*')->get($url);
            $maxBytes = max(1024, (int) config('services.static_map.max_bytes', 5242880));
            $body = $response->body();

            $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $safeMimes = ['image/png', 'image/jpeg', 'image/webp'];

            if ($response->successful()
                && strlen($body) <= $maxBytes
                && in_array($mime, $safeMimes, true)) {
                return [
                    'data_uri' => 'data:'.$mime.';base64,'.base64_encode($body),
                    'source' => 'OpenStreetMap',
                    'is_fallback' => false,
                ];
            }
        } catch (\Throwable) {
            // A generated fallback is returned below so PDF generation never fails because of network access.
        }

        return [
            'data_uri' => $this->fallbackSvg($latitude, $longitude),
            'source' => 'Pratinjau koordinat lokal',
            'is_fallback' => true,
        ];
    }

    private function fallbackSvg(float $latitude, float $longitude): string
    {
        $lat = htmlspecialchars(number_format($latitude, 6, '.', ''), ENT_QUOTES, 'UTF-8');
        $lng = htmlspecialchars(number_format($longitude, 6, '.', ''), ENT_QUOTES, 'UTF-8');
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="760" height="320" viewBox="0 0 760 320">
  <rect width="760" height="320" fill="#f8fafc"/>
  <g stroke="#dbe4ee" stroke-width="1">
    <path d="M0 64H760M0 128H760M0 192H760M0 256H760"/>
    <path d="M95 0V320M190 0V320M285 0V320M380 0V320M475 0V320M570 0V320M665 0V320"/>
  </g>
  <path d="M80 265 C180 180, 250 230, 355 135 S560 90, 690 40" fill="none" stroke="#cbd5e1" stroke-width="18" stroke-linecap="round"/>
  <circle cx="380" cy="155" r="18" fill="#dc2626" opacity=".18"/>
  <circle cx="380" cy="155" r="8" fill="#dc2626"/>
  <rect x="205" y="226" width="350" height="58" rx="10" fill="#ffffff" stroke="#cbd5e1"/>
  <text x="380" y="250" text-anchor="middle" font-family="Arial" font-size="14" font-weight="700" fill="#0f172a">Titik Koordinat Lokasi</text>
  <text x="380" y="272" text-anchor="middle" font-family="Arial" font-size="12" fill="#475569">{$lat}, {$lng}</text>
</svg>
SVG;
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
