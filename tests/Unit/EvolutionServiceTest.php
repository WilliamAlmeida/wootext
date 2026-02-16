<?php

use App\Services\EvolutionService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

test('createInstance sends correct payload with old project settings', function () {
    Config::set('services.evolution.url', 'http://evolution.test');
    Config::set('services.evolution.api_key', 'test-api-key');

    Http::fake([
        '*/instance/create' => Http::response(['instance' => ['key' => 'test-instance']], 200),
    ]);

    $service = new EvolutionService;
    $service->createInstance('test-instance', 'http://webhook.url');

    Http::assertSent(function ($request) {
        $data = $request->data();

        return $request->url() === 'http://evolution.test/instance/create' &&
               $data['instanceName'] === 'test-instance' &&
               $data['qrcode'] === true &&
               $data['integration'] === 'WHATSAPP-BAILEYS' &&
               $data['rejectCall'] === true &&
               $data['msgCall'] === 'Infelizmente não aceitamos ligações' &&
               $data['groupsIgnore'] === false &&
               $data['alwaysOnline'] === false &&
               $data['readMessages'] === true &&
               $data['readStatus'] === true &&
               $data['syncFullHistory'] === false &&
               $data['webhook']['enabled'] === true &&
               $data['webhook']['url'] === 'http://webhook.url' &&
               $data['webhook']['webhookByEvents'] === true &&
               $data['webhook']['base64'] === true &&
               in_array('MESSAGES_UPSERT', $data['webhook']['events']);
    });
});
