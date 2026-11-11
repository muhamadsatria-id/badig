<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_login_response_contains_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '0');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Cache-Control');

        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("script-src-attr 'none'", $policy);
        $this->assertMatchesRegularExpression("/nonce-[A-Za-z0-9+\\/=]+/", $policy);
    }

    public function test_sitemap_is_public_xml_and_excludes_private_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringStartsWith(
            'application/xml',
            (string) $response->headers->get('Content-Type')
        );
        $response->assertSee('/privacy', false);
        $response->assertDontSee('/dashboard', false);
        $response->assertDontSee('/database-orang', false);
    }
}
