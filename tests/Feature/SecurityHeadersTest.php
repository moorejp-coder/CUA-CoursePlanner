<?php

use App\Models\User;

// Every test hits a real route so the full middleware stack runs.
// The login page is public and requires no auth — ideal for header assertions.

test('X-Frame-Options is DENY', function () {
    $this->get('/login')->assertHeader('X-Frame-Options', 'DENY');
});

test('X-Content-Type-Options is nosniff', function () {
    $this->get('/login')->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('Referrer-Policy is strict-origin-when-cross-origin', function () {
    $this->get('/login')->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

test('Permissions-Policy disables all unused browser features', function () {
    $header = $this->get('/login')->headers->get('Permissions-Policy');

    foreach (['camera=()', 'microphone=()', 'geolocation=()', 'payment=()', 'usb=()'] as $directive) {
        expect($header)->toContain($directive);
    }
});

test('Content-Security-Policy is present and includes critical directives', function () {
    $csp = $this->get('/login')->headers->get('Content-Security-Policy');

    expect($csp)
        ->toContain("default-src 'self'")
        ->toContain("frame-ancestors 'none'")
        ->toContain("form-action 'self'")
        ->toContain("base-uri 'self'")
        // Fonts are self-hosted — no external CDN allowances in CSP
        ->not->toContain('fonts.googleapis.com')
        ->not->toContain('fonts.gstatic.com');
});

test('Strict-Transport-Security is not set in the testing environment', function () {
    // HSTS must be omitted locally so HTTP dev sessions are not poisoned by a 1-year pin.
    $this->get('/login')->assertHeaderMissing('Strict-Transport-Security');
});

test('legacy X-XSS-Protection header is not set', function () {
    // Removed: this IE-era header was retired in Chrome 78 and can re-enable
    // certain XSS vectors on older browsers. CSP covers XSS protection instead.
    $this->get('/login')->assertHeaderMissing('X-XSS-Protection');
});

test('security headers are present on authenticated routes too', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/chat');

    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->headers->get('Content-Security-Policy'))->toContain("default-src 'self'");
});
