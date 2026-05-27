<?php

use App\Models\User;

test('external url in intended session is cleared by middleware', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['url.intended' => 'https://evil-site.com/steal'])
        ->get('/');

    $response->assertSessionMissing('url.intended');
});

test('internal intended url is preserved by middleware', function () {
    $user = User::factory()->create();

    $internalUrl = url('/chat');

    $response = $this->actingAs($user)
        ->withSession(['url.intended' => $internalUrl])
        ->get('/');

    $response->assertSessionHas('url.intended', $internalUrl);
});

test('relative path in intended session is preserved by middleware', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['url.intended' => '/onboarding'])
        ->get('/');

    $response->assertSessionHas('url.intended', '/onboarding');
});

test('login does not redirect to external url even if intended url was poisoned', function () {
    $user = User::factory()->create([
        'password' => bcrypt('Password1!'),
    ]);

    $response = $this->withSession(['url.intended' => 'https://evil-site.com/steal'])
        ->post('/login', [
            'email' => $user->email,
            'password' => 'Password1!',
        ]);

    $location = $response->headers->get('Location', '');
    expect($location)->not->toContain('evil-site.com');
});
