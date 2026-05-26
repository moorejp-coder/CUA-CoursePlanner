<?php

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// ── (1) API routes — sanitised JSON responses ─────────────────────────────────

test('unhandled api exception returns generic 500 json without stack trace', function () {
    $request = Request::create('/api/chat', 'POST');

    $exception = new RuntimeException('DB query: SELECT password FROM users WHERE email="a@cua.edu"');
    $response = app(ExceptionHandler::class)->render($request, $exception);

    $body = $response->getContent();
    $json = json_decode($body, true);

    expect($response->getStatusCode())->toBe(500)
        ->and($json)->toEqual(['message' => 'An unexpected error occurred. Please try again.'])
        // sensitive exception details must never reach the client
        ->and($body)->not->toContain('SELECT')
        ->and($body)->not->toContain('password')
        ->and($body)->not->toContain('RuntimeException')
        ->and($body)->not->toContain('a@cua.edu')
        ->and($json)->not->toHaveKey('trace')
        ->and($json)->not->toHaveKey('file')
        ->and($json)->not->toHaveKey('line')
        ->and($json)->not->toHaveKey('class');
});

test('api 500 exception is logged server side with full diagnostics', function () {
    Log::shouldReceive('error')
        ->once()
        ->withArgs(function (string $message, array $context): bool {
            return $message === 'Unhandled API exception'
                && isset($context['class'], $context['message'], $context['file'], $context['line'], $context['trace']);
        });

    $request = Request::create('/api/chat', 'POST');
    app(ExceptionHandler::class)->render($request, new RuntimeException('Sensitive error'));
});

test('api 404 returns json not html', function () {
    $request = Request::create('/api/chat', 'GET');

    $response = app(ExceptionHandler::class)->render($request, new NotFoundHttpException);

    expect($response->getStatusCode())->toBe(404)
        ->and($response->headers->get('Content-Type'))->toContain('application/json')
        ->and(json_decode($response->getContent(), true))->toEqual(['message' => 'The requested resource was not found.']);
});

test('api 403 returns generic json message', function () {
    $request = Request::create('/api/chat', 'POST');

    $response = app(ExceptionHandler::class)->render(
        $request,
        new AccessDeniedHttpException
    );

    expect($response->getStatusCode())->toBe(403)
        ->and(json_decode($response->getContent(), true))->toEqual(['message' => 'This action is unauthorized.']);
});

test('api validation error returns 422 with field errors not stack trace', function () {
    $user = User::factory()->create();

    StudentProfile::create([
        'user_id' => $user->id,
        'full_name' => 'Test Student',
        'degree' => 'bsba',
        'catalog_year' => 'post_2024',
        'admit_term' => 'Fall 2024',
        'expected_graduation' => 'Spring 2028',
    ]);

    $response = $this->actingAs($user)->postJson(route('profile.suggest-update'), [
        'course_code' => 'ACCT 205',
        'status' => 'not_a_valid_status',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['message', 'errors'])
        ->assertJsonMissing(['trace', 'file', 'line', 'class']);
});

test('non-api exception does not get the json handler', function () {
    // The render callback must return null for web routes so the default
    // renderer produces an HTML page, not JSON.
    $request = Request::create('/profile', 'GET');

    $response = app(ExceptionHandler::class)->render($request, new NotFoundHttpException);

    // Default renderer for a web 404 renders an HTML page
    expect($response->getStatusCode())->toBe(404)
        ->and($response->headers->get('Content-Type'))->toContain('text/html');
});

// ── (2) Web error pages ───────────────────────────────────────────────────────

test('web 404 renders the custom error page', function () {
    $response = $this->get('/this-page-does-not-exist-xyz');

    $response->assertStatus(404)
        ->assertSee('Page Not Found')
        ->assertSee('Busch School');
});

test('web 419 renders the session expired error page', function () {
    // Render the 419 HttpException directly through the exception handler
    // (same code path as a CSRF token mismatch on any web route).
    $request = Request::create('/login', 'POST');
    $e = new HttpException(419, 'CSRF token mismatch.');

    $response = app(ExceptionHandler::class)->render($request, $e);

    expect($response->getStatusCode())->toBe(419)
        ->and($response->getContent())->toContain('Session Expired');
});

test('web error pages never expose stack traces or file paths', function () {
    $response = $this->get('/this-page-does-not-exist-xyz');

    $body = $response->getContent();

    expect($body)->not->toContain('vendor/')
        ->and($body)->not->toContain('app/Http')
        ->and($body)->not->toContain('Stack trace')
        ->and($body)->not->toContain('Exception');
});
