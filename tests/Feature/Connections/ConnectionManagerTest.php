<?php

use App\Livewire\Pages\Connections\ConnectionManager;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders the connection manager page', function () {
    Livewire::test(ConnectionManager::class)
        ->assertStatus(200)
        ->assertSee('Conexões WhatsApp');
});

it('opens the create modal', function () {
    Livewire::test(ConnectionManager::class)
        ->assertSet('showCreateModal', false)
        ->call('openCreateModal')
        ->assertSet('showCreateModal', true)
        ->assertSee('Nova Conexão');
});

it('validates the create instance form', function () {
    Livewire::test(ConnectionManager::class)
        ->call('createInstance')
        ->assertHasErrors(['newInstanceName' => 'required']);
});

it('opens the qr modal for waha connections', function () {
    Config::set('services.evolution.url', 'http://evolution.test');
    Config::set('services.evolution.api_key', 'evolution-key');
    Config::set('services.waha.url', 'http://waha.test');
    Config::set('services.waha.api_key', 'waha-key');
    Config::set('services.chatwoot.account_id', 1);

    Http::fake(function (Request $request) {
        if ($request->url() === 'http://evolution.test/instance/fetchInstances') {
            return Http::response([], 200);
        }

        if ($request->url() === 'http://waha.test/api/sessions') {
            return Http::response([], 200);
        }

        if ($request->url() === 'http://waha.test/api/sessions/Whatsapp_Test_CWID_1/auth/qr?format=image') {
            return Http::response('qr-bytes', 200, ['Content-Type' => 'image/png']);
        }

        return Http::response([], 200);
    });

    Livewire::test(ConnectionManager::class)
        ->call('connectInstance', 'Whatsapp_Test_CWID_1', 'waha')
        ->assertSet('showQrModal', true)
        ->assertSet('qrCode', 'data:image/png;base64,'.base64_encode('qr-bytes'));
});
