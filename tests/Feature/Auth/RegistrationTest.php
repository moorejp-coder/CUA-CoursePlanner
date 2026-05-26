<?php

use Illuminate\Support\Facades\Http;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'testuser@cua.edu',
        'password' => 'correct-horse-battery-staple',
        'password_confirmation' => 'correct-horse-battery-staple',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('onboarding'));
});
