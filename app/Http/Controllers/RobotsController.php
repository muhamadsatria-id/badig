<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $content = implode('
', [
            'User-agent: *',
            'Allow: /',
            'Disallow: /dashboard',
            'Disallow: /data/',
            'Disallow: /database-orang',
            'Disallow: /admin-it/',
            'Disallow: /super-admin/',
            'Disallow: /pimpinan/',
            'Disallow: /profile',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
