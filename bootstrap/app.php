<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $trustedProxies = array_values(array_filter(array_map(
            static fn (string $proxy): string => trim($proxy),
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        )));

        if ($trustedProxies !== []) {
            $middleware->trustProxies(at: $trustedProxies);
        }

        $trustedHosts = array_values(array_filter(array_map(
            static fn (string $host): string => trim($host),
            explode(',', (string) env('TRUSTED_HOSTS', ''))
        )));

        if ($trustedHosts !== []) {
            $middleware->trustHosts(at: $trustedHosts, subdomains: false);
        }

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'session.fresh' => \App\Http\Middleware\EnsureSessionFresh::class,
            'role.unit.valid' => \App\Http\Middleware\EnsureRoleUnitConsistency::class,
            'unit.isolation' => \App\Http\Middleware\UnitIsolation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Keep production exception rendering generic; details remain in server logs.
    })->create();
