<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production') && Str::startsWith((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', function (Request $request): array {
            $email = Str::lower(trim((string) $request->input('email', 'guest')));

            return [
                Limit::perMinute(10)->by('login:'.$email.'|'.$request->ip()),
                Limit::perMinute(30)->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('sensitive-write', function (Request $request): Limit {
            return Limit::perMinute(30)->by('write:'.($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('downloads', function (Request $request): Limit {
            return Limit::perMinute(30)->by('download:'.($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('heavy-export', function (Request $request): Limit {
            return Limit::perMinute(5)->by('export:'.($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('imports', function (Request $request): Limit {
            return Limit::perHour(5)->by('import:'.($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('backup', function (Request $request): Limit {
            return Limit::perHour(2)->by('backup:'.($request->user()?->id ?: $request->ip()));
        });

        Blade::directive('cspNonce', static function (): string {
            return '<?php echo \'nonce="\'.e((string) request()->attributes->get(\'csp_nonce\', \'\')).\'"\'; ?>';
        });
    }
}
