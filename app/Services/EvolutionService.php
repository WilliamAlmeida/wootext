<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class EvolutionService
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.evolution.url', ''), '/');
        $this->apiKey = config('services.evolution.api_key', '');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['apikey' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);
    }

    // ── Instances ──

    public function listInstances(): array
    {
        $response = $this->client()->get('/instance/fetchInstances');

        return $response->json() ?? [];
    }

    public function createInstance(string $instanceName, ?string $webhookUrl = null): array
    {
        $data = [
            'instanceName' => $instanceName,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
            'rejectCall' => true,
            'msgCall' => 'Infelizmente não aceitamos ligações',
            'groupsIgnore' => false,
            'alwaysOnline' => false,
            'readMessages' => true,
            'readStatus' => true,
            'syncFullHistory' => false,
        ];

        if ($webhookUrl) {
            $data['webhook'] = [
                'enabled' => true,
                'url' => $webhookUrl,
                'webhookByEvents' => true,
                'base64' => true,
                'events' => [
                    'QRCODE_UPDATED',
                    'CONNECTION_UPDATE',
                    'MESSAGES_UPSERT',
                    'MESSAGES_UPDATE',
                    'MESSAGES_DELETE',
                    'SEND_MESSAGE',
                    'CALL',
                ],
            ];
        }

        $response = $this->client()->post('/instance/create', $data);

        return $response->json() ?? [];
    }

    public function connectInstance(string $instanceName): array
    {
        $response = $this->client()->get("/instance/connect/{$instanceName}");

        return $response->json() ?? [];
    }

    public function getConnectionStatus(string $instanceName): array
    {
        $response = $this->client()->get("/instance/connectionState/{$instanceName}");

        return $response->json() ?? [];
    }

    public function logoutInstance(string $instanceName): array
    {
        $response = $this->client()->delete("/instance/logout/{$instanceName}");

        return $response->json() ?? [];
    }

    public function deleteInstance(string $instanceName): array
    {
        $response = $this->client()->delete("/instance/delete/{$instanceName}");

        return $response->json() ?? [];
    }

    public function restartInstance(string $instanceName): array
    {
        $response = $this->client()->put("/instance/restart/{$instanceName}");

        return $response->json() ?? [];
    }

    // ── Messages ──

    public function sendTextMessage(string $instanceName, string $number, string $text): array
    {
        $response = $this->client()->post("/message/sendText/{$instanceName}", [
            'number' => $number,
            'text' => $text,
        ]);

        return $response->json() ?? [];
    }

    public function sendMedia(string $instanceName, string $number, string $mediaUrl, string $mediaType, ?string $caption = null): array
    {
        $response = $this->client()->post("/message/sendMedia/{$instanceName}", [
            'number' => $number,
            'mediatype' => $mediaType,
            'media' => $mediaUrl,
            'caption' => $caption ?? '',
        ]);

        return $response->json() ?? [];
    }

    // ── Webhooks ──

    public function setWebhook(string $instanceName, string $url, array $events = []): array
    {
        $response = $this->client()->post("/webhook/set/{$instanceName}", [
            'url' => $url,
            'webhook_by_events' => false,
            'webhook_base64' => true,
            'events' => $events,
        ]);

        return $response->json() ?? [];
    }

    public function getWebhook(string $instanceName): array
    {
        $response = $this->client()->get("/webhook/find/{$instanceName}");

        return $response->json() ?? [];
    }

    // ── Chatwoot Integration ──

    public function setChatwootIntegration(string $instanceName, array $config): array
    {
        $response = $this->client()->post("/chatwoot/set/{$instanceName}", $config);

        return $response->json() ?? [];
    }

    public function getChatwootIntegration(string $instanceName): array
    {
        $response = $this->client()->get("/chatwoot/find/{$instanceName}");

        return $response->json() ?? [];
    }

    // ── Settings ──

    public function getInstanceSettings(string $instanceName): array
    {
        $response = $this->client()->get("/settings/find/{$instanceName}");

        return $response->json() ?? [];
    }

    public function updateInstanceSettings(string $instanceName, array $settings): array
    {
        $response = $this->client()->post("/settings/set/{$instanceName}", $settings);

        return $response->json() ?? [];
    }

    // ── Profile ──

    public function fetchProfile(string $instanceName, string $number): array
    {
        $response = $this->client()->post("/chat/fetchProfile/{$instanceName}", [
            'number' => $number,
        ]);

        return $response->json() ?? [];
    }

    public function checkNumber(string $instanceName, array $numbers): array
    {
        $response = $this->client()->post("/chat/whatsappNumbers/{$instanceName}", [
            'numbers' => $numbers,
        ]);

        return $response->json() ?? [];
    }

    // ── Groups ──

    public function listGroups(string $instanceName): array
    {
        $response = $this->client()->get("/group/fetchAllGroups/{$instanceName}");

        return $response->json() ?? [];
    }
}
