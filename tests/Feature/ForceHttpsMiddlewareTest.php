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

test('forced https uses https urls for vite and public assets', function () {
    config([
        'app.force_https' => true,
        'app.url' => 'https://academic-management-uotbt.sevalla.app',
        'app.asset_url' => 'https://academic-management-uotbt.sevalla.app',
    ]);

    (new \App\Providers\AppServiceProvider(app()))->boot();

    $viteAsset = app(\Illuminate\Foundation\Vite::class)->asset('resources/css/app.css');
    $favicon = asset('favicon.svg');

    expect($viteAsset)->toStartWith('https://academic-management-uotbt.sevalla.app/build/assets/');
    expect($favicon)->toBe('https://academic-management-uotbt.sevalla.app/favicon.svg');
});
