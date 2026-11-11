<?php

return [
    'headers' => [
        'hsts' => [
            'enabled' => env('SECURITY_HSTS_ENABLED', true),
            'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
            'include_subdomains' => env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => env('SECURITY_HSTS_PRELOAD', false),
        ],

        'csp' => [
            'enabled' => env('SECURITY_CSP_ENABLED', true),
            'report_only' => env('SECURITY_CSP_REPORT_ONLY', false),
            'report_uri' => env('SECURITY_CSP_REPORT_URI'),
        ],
    ],

    'trusted_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => trim($host),
        explode(',', (string) env('TRUSTED_HOSTS', ''))
    ))),

    'trusted_proxies' => array_values(array_filter(array_map(
        static fn (string $proxy): string => trim($proxy),
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),

    'auth' => [
        'otp_enabled' => env('AUTH_OTP_ENABLED', false),
        'otp_ttl_minutes' => (int) env('AUTH_OTP_TTL_MINUTES', 5),
        'failed_password_audit_threshold' => (int) env(
            'AUTH_FAILED_PASSWORD_AUDIT_THRESHOLD',
            env('AUTH_MAX_FAILED_PASSWORDS', 5)
        ),
    ],
];
