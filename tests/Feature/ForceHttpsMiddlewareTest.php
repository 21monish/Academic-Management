<?php

test('plain http requests redirect to https when https is forced', function () {
    config(['app.force_https' => true]);

    $response = $this->get('http://example.com/login');

    $response->assertStatus(308);
    expect($response->headers->get('Location'))->toBe('https://example.com/login');
});

test('forwarded https requests do not redirect again', function () {
    config(['app.force_https' => true]);

    $response = $this
        ->withServerVariables([
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'REMOTE_ADDR' => '10.0.0.1',
        ])
        ->get('http://example.com/login');

    $response->assertOk();
});
