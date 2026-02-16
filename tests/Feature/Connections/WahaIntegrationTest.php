<?php

use App\Livewire\Pages\Connections\ConnectionManager;
use App\Services\ChatwootDatabaseService;
use App\Services\ConnectionNameHelper;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('creates waha session app and webhook integration', function () {
    Config::set('services.waha.url', 'http://waha.test');
    Config::set('services.waha.api_key', 'waha-key');
    Config::set('services.chatwoot.url', 'http://chatwoot.test');
    Config::set('services.chatwoot.api_token', 'chatwoot-token');
    Config::set('services.chatwoot.account_id', 1);
    Config::set('services.evolution.url', 'http://evolution.test');
    Config::set('services.evolution.api_key', 'evolution-key');

    $fakeDb = new class
    {
        public array $webhookUpdates = [];

        public function getChannelIdByInboxId(int $inboxId): ?int
        {
            return 20;
        }

        public function getChannelApiIdentifier(int $channelId): ?string
        {
            return 'identifier-xyz';
        }

        public function updateChannelApiWebhook(int $channelId, string $webhookUrl): bool
        {
            $this->webhookUpdates[] = [
                'channel_id' => $channelId,
                'url' => $webhookUrl,
            ];

            return true;
        }
    };

    app()->instance(ChatwootDatabaseService::class, $fakeDb);

    Http::fake(function (Request $request) {
        $url = $request->url();
        $method = $request->method();

        if ($url === 'http://evolution.test/instance/fetchInstances') {
            return Http::response([], 200);
        }

        if ($url === 'http://waha.test/api/sessions' && $method === 'GET') {
            return Http::response([], 200);
        }

        if ($url === 'http://waha.test/api/sessions' && $method === 'POST') {
            return Http::response(['name' => $request->data()['name'] ?? ''], 200);
        }

        if ($url === 'http://waha.test/api/apps' && $method === 'POST') {
            return Http::response(['id' => 'app-123'], 200);
        }

        if (str_starts_with($url, 'http://waha.test/api/apps') && $method === 'GET') {
            return Http::response([
                ['id' => 'app-123', 'app' => 'chatwoot', 'session' => 'unused'],
            ], 200);
        }

        if ($url === 'http://chatwoot.test/api/v1/accounts/1/inboxes' && $method === 'POST') {
            return Http::response(['id' => 10, 'channel_id' => 20], 200);
        }

        return Http::response([], 200);
    });

    $instanceName = 'TesteWaha';
    $sessionName = ConnectionNameHelper::buildProviderName($instanceName, 1);
    $webhookUrl = route('webhooks.waha');

    Livewire::test(ConnectionManager::class)
        ->set('newInstanceName', $instanceName)
        ->set('newInstanceProvider', 'waha')
        ->call('createInstance');

    Http::assertSent(function (Request $request) use ($sessionName, $webhookUrl) {
        $data = $request->data();

        return $request->url() === 'http://waha.test/api/sessions'
            && $request->method() === 'POST'
            && ($data['name'] ?? null) === $sessionName
            && ($data['start'] ?? null) === true
            && ($data['config']['webhooks'][0]['url'] ?? null) === $webhookUrl;
    });

    Http::assertSent(function (Request $request) use ($webhookUrl, $instanceName) {
        $data = $request->data();

        return $request->url() === 'http://chatwoot.test/api/v1/accounts/1/inboxes'
            && $request->method() === 'POST'
            && ($data['name'] ?? null) === $instanceName
            && ($data['channel']['type'] ?? null) === 'api'
            && ($data['channel']['webhook_url'] ?? null) === $webhookUrl;
    });

    Http::assertSent(function (Request $request) use ($sessionName) {
        $data = $request->data();

        return $request->url() === 'http://waha.test/api/apps'
            && $request->method() === 'POST'
            && ($data['session'] ?? null) === $sessionName
            && ($data['app'] ?? null) === 'chatwoot'
            && ($data['config']['accountId'] ?? null) === 1
            && ($data['config']['inboxId'] ?? null) === 10
            && ($data['config']['accountToken'] ?? null) === 'chatwoot-token'
            && ($data['config']['inboxIdentifier'] ?? null) === 'identifier-xyz';
    });

    expect($fakeDb->webhookUpdates)->toHaveCount(1)
        ->and($fakeDb->webhookUpdates[0]['channel_id'])->toBe(20)
        ->and($fakeDb->webhookUpdates[0]['url'])->toBe("http://waha.test/api/webhooks/chatwoot/{$sessionName}/app-123");
});
