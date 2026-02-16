<?php

namespace App\Livewire\Pages\Connections;

use Livewire\Component;
use Illuminate\View\View;
use Livewire\Attributes\On;
use App\Services\WahaService;
use Livewire\Attributes\Layout;
use App\Services\EvolutionService;
use App\Services\ConnectionNameHelper;

#[Layout('layouts.auth')]
class ConnectionManager extends Component
{
    public array $instances = [];

    public bool $showCreateModal = false;

    public string $newInstanceName = '';

    public string $newInstanceProvider = 'waha';

    public ?string $qrCode = null;

    public ?string $qrInstanceName = null;

    public bool $showQrModal = false;

    public bool $loading = false;

    public bool $showSettingsModal = false;

    public ?string $selectedWebhookUrl = null;

    public ?string $selectedToken = null;

    private function getAccountId(): int
    {
        return config('services.chatwoot.account_id');
    }

    #[On('openSettingsModal')]
    public function openSettingsModal(string $instanceName): void
    {
        $this->selectedWebhookUrl = config('app.url')."/api/webhook/{$instanceName}";
        $this->selectedToken = 'sec_'.substr(md5($instanceName), 0, 10);

        $this->showSettingsModal = true;
    }

    public function closeSettingsModal(): void
    {
        $this->showSettingsModal = false;
        $this->selectedWebhookUrl = null;
        $this->selectedToken = null;
    }

    public function render(): View
    {
        return view('pages.connections.connection-manager');
    }

    public function openCreateModal(): void
    {
        $this->reset('newInstanceName', 'newInstanceProvider');
        $this->showCreateModal = true;
    }

    public function createInstance(): void
    {
        $this->validate([
            'newInstanceName' => 'required|string|max:50|alpha_dash',
            'newInstanceProvider' => 'required|in:evolution,waha',
        ]);

        $providerName = ConnectionNameHelper::buildProviderName($this->newInstanceName, $this->getAccountId());

        try {
            if ($this->newInstanceProvider === 'evolution') {
                app(EvolutionService::class)->createInstance($providerName, route('webhooks.evolution'));

                app(EvolutionService::class)->setChatwootIntegration($providerName, [
                    'enabled' => true,
                    'accountId' => $this->getAccountId(),
                    'token' => config('services.chatwoot.api_token'),
                    'url' => config('services.chatwoot.url'),
                    'signMsg' => true,
                    'nameInbox' => $this->newInstanceName,
                    'reopenConversation' => true,
                    'conversationPending' => false,
                    'importContacts' => false,
                    'importMessages' => false,
                    'autoCreate' => true,
                ]);
            } else {
                // 1. URL do Webhook para o WAHA (receber eventos no Laravel)
                $webhookUrl = route('webhooks.waha');

                // 2. Criar a sessão no WAHA com o webhook já configurado
                app(WahaService::class)->createSession($providerName, [
                    'webhooks' => [
                        [
                            'url' => $webhookUrl,
                            'events' => ['session.status'],
                        ],
                    ],
                ]);

                // 3. Integração com Chatwoot
                $chatwootService = app(\App\Services\ChatwootService::class);
                $chatwootUrl = config('services.chatwoot.url');
                $chatwootToken = config('services.chatwoot.api_token');

                if ($chatwootToken && $chatwootUrl) {
                    try {
                        // 3.1. Criar Inbox API no Chatwoot
                        $inbox = $chatwootService->createInbox($this->newInstanceName, $webhookUrl);

                        if (isset($inbox['id'])) {
                            // 3.0 Create Chatwoot webhook to forward selected events to our backend
                            try {
                                $backendWebhookUrl = route('webhooks.chatwoot');

                                $chatwootService->createWebhook($backendWebhookUrl, [
                                    'conversation_created',
                                    'conversation_status_changed',
                                    'conversation_updated',
                                ]);
                            } catch (\Throwable $hookError) {
                                \Illuminate\Support\Facades\Log::warning('Failed to create Chatwoot webhook', ['error' => $hookError->getMessage()]);
                            }

                            // Instanciar serviço de banco do Chatwoot
                            $chatwootDb = app(\App\Services\ChatwootDatabaseService::class);

                            // 3.2. Obter Identifier do Channel API direto do banco do Chatwoot
                            $channelId = $inbox['channel_id'] ?? null;

                            if (! $channelId) {
                                $channelId = $chatwootDb->getChannelIdByInboxId($inbox['id']);
                            }

                            if ($channelId) {
                                $identifier = $chatwootDb->getChannelApiIdentifier($channelId);

                                if ($identifier) {
                                    // 3.3. Configurar o "App" no WAHA
                                    \Illuminate\Support\Facades\Log::info('Creating WAHA Chatwoot app', [
                                        'session' => $providerName,
                                        'account_id' => $this->getAccountId(),
                                        'inbox_id' => $inbox['id'],
                                    ]);

                                    app(WahaService::class)->createChatwootApp($providerName, [
                                        'url' => $chatwootUrl,
                                        'accountId' => $this->getAccountId(),
                                        'inboxId' => $inbox['id'],
                                        'token' => $chatwootToken,
                                        'identifier' => $identifier,
                                    ]);

                                    // 3.4. Buscar o ID do App criado no WAHA
                                    $apps = app(WahaService::class)->listApps($providerName);
                                    $createdApp = collect($apps)->first(function ($app) {
                                        $type = strtolower((string) ($app['app'] ?? $app['name'] ?? $app['type'] ?? ''));

                                        return $type === 'chatwoot';
                                    });

                                    $appId = $createdApp['id'] ?? $createdApp['_id'] ?? ($createdApp['config']['id'] ?? null);

                                    if ($appId) {
                                        $wahaWebhookForChatwoot = config('services.waha.url')."/api/webhooks/chatwoot/{$providerName}/{$appId}";

                                        // 3.5. Atualizar a URL do Webhook no banco do Chatwoot
                                        $chatwootDb->updateChannelApiWebhook($channelId, $wahaWebhookForChatwoot);

                                        \Illuminate\Support\Facades\Log::info('Integração WAHA <-> Chatwoot configurada com sucesso via serviço de banco.');
                                    } else {
                                        \Illuminate\Support\Facades\Log::warning('WAHA Chatwoot app not found after creation', [
                                            'session' => $providerName,
                                            'apps_count' => count($apps),
                                        ]);
                                    }
                                } else {
                                    \Illuminate\Support\Facades\Log::error("Identifier do channel_api não encontrado para channel_id: {$channelId}");
                                }
                            } else {
                                \Illuminate\Support\Facades\Log::error("Channel ID não encontrado para inbox: {$inbox['id']}");
                            }
                        }
                    } catch (\Throwable $cwError) {
                        \Illuminate\Support\Facades\Log::error('Erro ao configurar Chatwoot DB para WAHA: '.$cwError->getMessage());
                        $this->dispatch('notify', type: 'warning', message: 'Sessão criada, mas erro na integração DB Chatwoot.');
                    }
                }
            }

            $this->showCreateModal = false;
            $this->dispatch('fetchInstances', $this->newInstanceProvider);
            $this->dispatch('notify', type: 'success', message: 'Conexão criada com sucesso!');
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao criar: '.$exception->getMessage());
        }
    }

    #[On('connectInstance')]
    public function connectInstance(string $name, string $provider): void
    {
        try {
            if ($provider === 'evolution') {
                $result = app(EvolutionService::class)->connectInstance($name);
                $base64 = $result['base64'] ?? null;

                if ($base64) {
                    $this->qrCode = $base64;
                    $this->qrInstanceName = ConnectionNameHelper::extractFriendlyName($name, $this->getAccountId());
                    $this->showQrModal = true;
                }
            } else {
                $base64 = app(WahaService::class)->getQRCodeImage($name);

                if (! $base64) {
                    $result = app(WahaService::class)->getQRCode($name);
                    $base64 = $result['value'] ?? null;
                }

                if ($base64 && ! str_starts_with($base64, 'data:image')) {
                    $base64 = 'data:image/png;base64,'.$base64;
                }

                if ($base64) {
                    $this->qrCode = $base64;
                    $this->qrInstanceName = ConnectionNameHelper::extractFriendlyName($name, $this->getAccountId());
                    $this->showQrModal = true;
                }
            }
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao obter QR: '.$exception->getMessage());
        }
    }

    public function disconnectInstance(string $name, string $provider): void
    {
        try {
            if ($provider === 'evolution') {
                app(EvolutionService::class)->logoutInstance($name);
            } else {
                app(WahaService::class)->logout($name);
            }

            $this->dispatch('fetchInstances', $provider);
            $this->dispatch('notify', type: 'success', message: 'Desconectado com sucesso.');
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: 'Erro: '.$exception->getMessage());
        }
    }

    #[On('restartInstance')]
    public function restartInstance(string $name, string $provider): void
    {
        try {
            if ($provider === 'evolution') {
                app(EvolutionService::class)->restartInstance($name);
            } else {
                app(WahaService::class)->restartSession($name);
            }

            $this->dispatch('fetchInstances', $provider);
            $this->fetchInstancesTimeout($provider, 5000);
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: 'Erro: '.$exception->getMessage());
        }
    }

    public bool $showDeleteModal = false;

    public array $deleteTarget = [];

    public bool $deleteInboxChecked = false;

    #[On('deleteInstance')]
    public function deleteInstance(string $name, string $provider, ?string $friendly_name = null): void
    {
        $this->deleteTarget = ['name' => $name, 'provider' => $provider, 'friendly_name' => $friendly_name];
        $this->deleteInboxChecked = false;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        $name = $this->deleteTarget['name'];
        $provider = $this->deleteTarget['provider'];

        try {
            if ($provider === 'evolution') {
                app(EvolutionService::class)->deleteInstance($name);
            } else {
                app(WahaService::class)->deleteSession($name);
            }

            if ($this->deleteInboxChecked) {
                $chatwootService = app(\App\Services\ChatwootService::class);
                $inboxes = $chatwootService->getInboxes();

                // Find inbox by name (assuming name matches or is part of it)
                // In old project: friendlyName matched inbox name
                $friendlyName = ConnectionNameHelper::extractFriendlyName($name, $this->getAccountId());

                $targetInbox = collect($inboxes)->first(function ($inbox) use ($friendlyName) {
                    return $inbox['name'] === $friendlyName;
                });

                if ($targetInbox) {
                    $chatwootService->deleteInbox($targetInbox['id']);
                }
            }

            $this->dispatch('fetchInstances', $provider);
            $this->dispatch('notify', type: 'success', message: 'Conexão removida.');
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: 'Erro: '.$exception->getMessage());
        } finally {
            $this->showDeleteModal = false;
            $this->deleteTarget = [];
        }
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTarget = [];
        $this->deleteInboxChecked = false;
    }

    public function closeQrModal(): void
    {
        $this->showQrModal = false;
        $this->qrCode = null;
        $this->qrInstanceName = null;
        $this->dispatch('fetchInstances', $this->newInstanceProvider);
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower($status);

        return match (true) {
            str_contains($status, 'open') || str_contains($status, 'connected') || $status === 'working' => 'connected',
            str_contains($status, 'close') || str_contains($status, 'disconnected') || $status === 'stopped' => 'disconnected',
            str_contains($status, 'qr') || str_contains($status, 'scan') => 'qr_pending',
            default => 'unknown',
        };
    }

    private function fetchInstancesTimeout(string $provider, int $delay = 5000): void
    {
        $this->js("setTimeout(() => Livewire.dispatch('fetchInstances', {provider: '{$provider}'}), {$delay});"); 
    }
}
