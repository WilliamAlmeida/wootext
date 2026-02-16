<?php

use App\Services\WahaService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

test('createChatwootApp sends correct payload', function () {
    Config::set('services.waha.url', 'http://waha.test');
    Config::set('services.waha.api_key', 'test-api-key');

    Http::fake([
        'http://waha.test/api/apps' => Http::response(['id' => 'app-1'], 200),
    ]);

    $service = new WahaService;
    $service->createChatwootApp('test-session', [
        'url' => 'https://chatwoot.test',
        'accountId' => 1,
        'inboxId' => 10,
        'token' => 'secret-token',
        'identifier' => 'identifier-123',
    ]);

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->url() === 'http://waha.test/api/apps'
            && $request->hasHeader('X-Api-Key', 'test-api-key')
            && ($data['session'] ?? null) === 'test-session'
            && ($data['app'] ?? null) === 'chatwoot'
            && ($data['config']['url'] ?? null) === 'https://chatwoot.test'
            && ($data['config']['accountId'] ?? null) === 1
            && ($data['config']['inboxId'] ?? null) === 10
            && ($data['config']['accountToken'] ?? null) === 'secret-token'
            && ($data['config']['inboxIdentifier'] ?? null) === 'identifier-123';
    });
});

test('getQRCodeImage falls back to legacy endpoint', function () {
    Config::set('services.waha.url', 'http://waha.test');
    Config::set('services.waha.api_key', 'test-api-key');

    Http::fake([
        'http://waha.test/api/sessions/test-session/auth/qr?format=image' => Http::response('not-found', 404),
        'http://waha.test/api/test-session/auth/qr?format=image' => Http::response('png-bytes', 200),
    ]);

    $service = new WahaService;
    $result = $service->getQRCodeImage('test-session');

    expect($result)->toBe(base64_encode('png-bytes'));
});
