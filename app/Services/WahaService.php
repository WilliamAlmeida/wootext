<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WahaService
{
    private string $baseUrl;

    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.waha.url', ''), '/');
        $this->apiKey = config('services.waha.api_key', '');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['X-Api-Key' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);
    }

    // ── Sessions ──

    public function listSessions(): array
    {
        $response = $this->client()->get('/api/sessions');

        return $response->json() ?? [];
    }

    public function createSession(string $name, ?array $config = null): array
    {
        $data = array_filter([
            'name' => $name,
            'start' => true,
            'config' => $config,
        ]);

        $response = $this->client()->post('/api/sessions', $data);

        return $response->json() ?? [];
    }

    public function getSession(string $name): array
    {
        $response = $this->client()->get("/api/sessions/{$name}");

        return $response->json() ?? [];
    }

    public function startSession(string $name): array
    {
        $response = $this->client()->post("/api/sessions/{$name}/start");

        return $response->json() ?? [];
    }

    public function stopSession(string $name): array
    {
        $response = $this->client()->post("/api/sessions/{$name}/stop");

        return $response->json() ?? [];
    }

    public function deleteSession(string $name): array
    {
        $response = $this->client()->delete("/api/sessions/{$name}");

        return $response->json() ?? [];
    }

    public function restartSession(string $name): array
    {
        $response = $this->client()->post("/api/sessions/{$name}/restart");

        return $response->json() ?? [];
    }

    public function updateSessionConfig(string $name, array $config): array
    {
        $response = $this->client()->put("/api/sessions/{$name}", [
            'config' => $config,
        ]);

        return $response->json() ?? [];
    }

    // ── Auth / QR ──

    public function getQRCode(string $name): array
    {
        $response = $this->client()->get("/api/sessions/{$name}/auth/qr", [
            'format' => 'raw',
        ]);

        if ($response->failed()) {
            $response = $this->client()->get("/api/{$name}/auth/qr", [
                'format' => 'raw',
            ]);
        }

        return $response->json() ?? [];
    }

    public function getQRCodeImage(string $name): ?string
    {
        $response = $this->client()->get("/api/sessions/{$name}/auth/qr", [
            'format' => 'image',
        ]);

        if ($response->failed()) {
            $response = $this->client()->get("/api/{$name}/auth/qr", [
                'format' => 'image',
            ]);
        }

        if ($response->successful()) {
            return $response->json('data');
        }

        return null;
    }

    public function logout(string $name): array
    {
        $response = $this->client()->post("/api/sessions/{$name}/auth/logout");

        return $response->json() ?? [];
    }

    // ── Messages ──

    public function sendTextMessage(string $session, string $chatId, string $text): array
    {
        $response = $this->client()->post('/api/sendText', [
            'session' => $session,
            'chatId' => $chatId,
            'text' => $text,
        ]);

        return $response->json() ?? [];
    }

    public function sendImage(string $session, string $chatId, string $imageUrl, ?string $caption = null): array
    {
        $response = $this->client()->post('/api/sendImage', [
            'session' => $session,
            'chatId' => $chatId,
            'file' => ['url' => $imageUrl],
            'caption' => $caption ?? '',
        ]);

        return $response->json() ?? [];
    }

    public function sendFile(string $session, string $chatId, string $fileUrl, ?string $caption = null): array
    {
        $response = $this->client()->post('/api/sendFile', [
            'session' => $session,
            'chatId' => $chatId,
            'file' => ['url' => $fileUrl],
            'caption' => $caption ?? '',
        ]);

        return $response->json() ?? [];
    }

    public function markAsRead(string $session, string $chatId): array
    {
        $response = $this->client()->post('/api/sendSeen', [
            'session' => $session,
            'chatId' => $chatId,
        ]);

        return $response->json() ?? [];
    }

    // ── Contacts ──

    public function listContacts(string $session): array
    {
        $response = $this->client()->get('/api/contacts', [
            'session' => $session,
        ]);

        return $response->json() ?? [];
    }

    public function checkNumber(string $session, string $phone): array
    {
        $response = $this->client()->get('/api/contacts/check-exists', [
            'session' => $session,
            'phone' => $phone,
        ]);

        return $response->json() ?? [];
    }

    public function getProfilePicture(string $session, string $chatId): array
    {
        $response = $this->client()->get('/api/contacts/profile-picture', [
            'session' => $session,
            'contactId' => $chatId,
        ]);

        return $response->json() ?? [];
    }

    // ── Webhooks ──

    public function setWebhook(string $session, string $url, array $events = []): array
    {
        $response = $this->client()->put("/api/sessions/{$session}", [
            'config' => [
                'webhooks' => [
                    [
                        'url' => $url,
                        'events' => $events ?: ['message', 'session.status'],
                    ],
                ],
            ],
        ]);

        return $response->json() ?? [];
    }

    // ── Chatwoot App ──

    public function createChatwootApp(string $session, array $config): array
    {
        $payload = [
            'id' => '',
            'session' => $session,
            'app' => 'chatwoot',
            'config' => [
                'url' => $config['url'] ?? '',
                'accountId' => (int) ($config['accountId'] ?? 0),
                'inboxId' => (int) ($config['inboxId'] ?? 0),
                'accountToken' => $config['accountToken'] ?? $config['token'] ?? '',
                'inboxIdentifier' => $config['inboxIdentifier'] ?? $config['identifier'] ?? '',
                'locale' => $config['locale'] ?? 'pt-BR',
            ],
        ];

        $response = $this->client()->post('/api/apps', $payload);

        if ($response->failed()) {
            Log::warning('WAHA createChatwootApp failed', [
                'session' => $session,
                'account_id' => $payload['config']['accountId'],
                'inbox_id' => $payload['config']['inboxId'],
                'has_token' => $payload['config']['accountToken'] !== '',
                'identifier' => $payload['config']['inboxIdentifier'],
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);
        }

        return $response->throw()->json() ?? [];
    }

    public function listApps(string $session): array
    {
        $response = $this->client()->get('/api/apps', [
            'session' => $session,
        ]);

        return $response->json() ?? [];
    }

    public function removeChatwootApp(string $session, string $appId): array
    {
        $response = $this->client()->delete("/api/apps/{$appId}", [
            'session' => $session,
        ]);

        return $response->json() ?? [];
    }

    // ── Groups ──

    public function listGroups(string $session): array
    {
        $response = $this->client()->get('/api/groups', [
            'session' => $session,
        ]);

        return $response->json() ?? [];
    }
}
