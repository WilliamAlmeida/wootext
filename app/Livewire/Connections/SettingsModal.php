<?php

namespace App\Livewire\Connections;

use App\Services\ConnectionNameHelper;
use App\Services\EvolutionService;
use App\Services\WahaService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class SettingsModal extends Component
{
    public bool $showSettingsModal = false;

    public string $instanceName = '';

    public string $provider = '';

    public ?string $friendlyName = null;

    public ?string $rawStatus = null;

    public array $settings = [];

    public bool $loading = false;

    public bool $saving = false;

    public ?string $errorMessage = null;

    public ?string $webhookUrl = null;

    public ?string $webhookToken = null;

    public array $sessionDetails = [];

    public function mount(): void
    {
        $this->settings = $this->defaultSettings();
    }

    #[On('openSettingsModal')]
    public function openSettingsModal(string $name, string $provider = null, string $friendly_name = null): void
    {
        $this->provider = $provider;
        $this->instanceName = $name;
        $this->friendlyName = $friendly_name ?? ConnectionNameHelper::extractFriendlyName($name, config('services.chatwoot.account_id'));

        $this->rawStatus = null;
        $this->sessionDetails = [];
        $this->settings = $this->defaultSettings();
        $this->errorMessage = null;
        $this->webhookUrl = route('webhooks.waha');
        $this->webhookToken = 'sec_'.substr(md5($name), 0, 10);
        $this->showSettingsModal = true;

        $this->loadData();
    }

    public function close(): void
    {
        $this->showSettingsModal = false;
    }

    private function loadData(): void
    {
        $this->loading = true;

        try {
            if ($this->provider === 'evolution') {
                $this->loadEvolutionSettings();
            } else {
                $this->loadWahaDetails();
            }
        } catch (\Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    private function loadEvolutionSettings(): void
    {
        $response = app(EvolutionService::class)->getInstanceSettings($this->instanceName);

        $settings = $response['settings'] ?? $response;

        $this->settings = array_merge($this->defaultSettings(), [
            'signMsg' => (bool) ($settings['signMsg'] ?? $settings['signMessages'] ?? $this->settings['signMsg']),
            'groupsIgnore' => (bool) ($settings['groupsIgnore'] ?? $this->settings['groupsIgnore']),
            'rejectCall' => (bool) ($settings['rejectCall'] ?? $this->settings['rejectCall']),
            'msgCall' => (string) ($settings['msgCall'] ?? $this->settings['msgCall']),
            'alwaysOnline' => (bool) ($settings['alwaysOnline'] ?? $this->settings['alwaysOnline']),
            'readMessages' => (bool) ($settings['readMessages'] ?? $this->settings['readMessages']),
            'readStatus' => (bool) ($settings['readStatus'] ?? $this->settings['readStatus']),
        ]);

        $this->rawStatus = $response['status'] ?? $this->rawStatus;
    }

    private function loadWahaDetails(): void
    {
        $session = app(WahaService::class)->getSession($this->instanceName);
        $this->sessionDetails = is_array($session) ? $session : [];
        $this->rawStatus = $this->sessionDetails['status'] ?? $this->rawStatus;

        $config = $this->sessionDetails['config'] ?? [];
        $ignore = $config['ignore'] ?? [];
        $noweb = $config['noweb'] ?? [];

        $this->settings = array_merge($this->defaultWahaSettings(), [
            'groupsIgnore'  => (bool) ($ignore['groups'] ?? false),
            'alwaysOnline'  => (bool) ($noweb['markOnline'] ?? true),
            'ignoreStatus'  => (bool) ($ignore['status'] ?? false),
        ]);
    }

    public function toggleSetting(string $key): void
    {
        if (! array_key_exists($key, $this->settings)) {
            return;
        }

        $this->settings[$key] = ! $this->settings[$key];

        if ($this->provider === 'evolution') {
            $this->persistEvolutionSettings();
        } else {
            $this->persistWahaSettings();
        }
    }

    public function saveCallMessage(): void
    {
        if ($this->provider !== 'evolution') {
            return;
        }

        $this->persistEvolutionSettings();
    }

    private function persistWahaSettings(): void
    {
        $this->saving = true;

        try {
            $currentSession = app(WahaService::class)->getSession($this->instanceName);
            $currentConfig = $currentSession['config'] ?? [];

            $currentConfig['ignore'] = array_merge($currentConfig['ignore'] ?? [], [
                'groups' => (bool) ($this->settings['groupsIgnore'] ?? false),
                'status' => (bool) ($this->settings['ignoreStatus'] ?? false),
            ]);

            $currentConfig['noweb'] = array_merge($currentConfig['noweb'] ?? [], [
                'markOnline' => (bool) ($this->settings['alwaysOnline'] ?? true),
            ]);

            // Preserve existing webhooks
            if (! isset($currentConfig['webhooks'])) {
                $currentConfig['webhooks'] = [];
            }

            app(WahaService::class)->updateSessionConfig($this->instanceName, $currentConfig);

            $this->dispatch('notify', type: 'success', message: 'Configurações salvas.');
        } catch (\Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar: '.$exception->getMessage());
        } finally {
            $this->saving = false;
        }
    }

    public function disconnectInstance(): void
    {
        if ($this->provider !== 'evolution') {
            return;
        }

        try {
            app(EvolutionService::class)->logoutInstance($this->instanceName);
            $this->dispatch('notify', type: 'success', message: 'Instância desconectada.');
            $this->dispatch('fetchInstances', $this->provider);
            $this->close();
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao desconectar: '.$exception->getMessage());
        }
    }

    public function applyWahaWebhook(): void
    {
        if ($this->provider !== 'waha') {
            return;
        }

        try {
            $url = $this->webhookUrl ?? route('webhooks.waha');

            $currentSession = app(WahaService::class)->getSession($this->instanceName);
            $currentConfig = $currentSession['config'] ?? [];

            $currentConfig['webhooks'] = [
                [
                    'url'    => $url,
                    'events' => ['message', 'session.status'],
                    'hmac'   => null,
                    'retries' => null,
                    'customHeaders' => null,
                ],
            ];

            app(WahaService::class)->updateSessionConfig($this->instanceName, $currentConfig);

            $this->dispatch('notify', type: 'success', message: 'Webhook atualizado com sucesso.');
        } catch (\Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->dispatch('notify', type: 'error', message: 'Erro ao atualizar webhook: '.$exception->getMessage());
        }
    }

    private function persistEvolutionSettings(): void
    {
        $this->saving = true;

        try {
            app(EvolutionService::class)->updateInstanceSettings($this->instanceName, [
                'signMsg' => (bool) ($this->settings['signMsg'] ?? false),
                'groupsIgnore' => (bool) ($this->settings['groupsIgnore'] ?? true),
                'rejectCall' => (bool) ($this->settings['rejectCall'] ?? false),
                'msgCall' => (string) ($this->settings['msgCall'] ?? ''),
                'alwaysOnline' => (bool) ($this->settings['alwaysOnline'] ?? false),
                'readMessages' => (bool) ($this->settings['readMessages'] ?? false),
                'readStatus' => (bool) ($this->settings['readStatus'] ?? false),
            ]);

            $this->dispatch('notify', type: 'success', message: 'Configurações salvas.');
        } catch (\Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar: '.$exception->getMessage());
        } finally {
            $this->saving = false;
        }
    }

    private function defaultSettings(): array
    {
        return [
            'signMsg'      => true,
            'groupsIgnore' => true,
            'rejectCall'   => true,
            'msgCall'      => 'Infelizmente não aceitamos ligações',
            'alwaysOnline' => false,
            'readMessages' => true,
            'readStatus'   => true,
        ];
    }

    private function defaultWahaSettings(): array
    {
        return [
            'groupsIgnore' => false,
            'alwaysOnline' => true,
            'ignoreStatus' => false,
        ];
    }

    private function statusLabel(): string
    {
        if (! $this->rawStatus) {
            return 'Desconhecido';
        }

        $status = strtolower($this->rawStatus);

        return match (true) {
            str_contains($status, 'open') || str_contains($status, 'connected') || $status === 'working' => 'Conectado',
            str_contains($status, 'close') || str_contains($status, 'disconnected') || $status === 'stopped' => 'Desconectado',
            str_contains($status, 'qr') || str_contains($status, 'scan') => 'QR Pendente',
            default => 'Desconhecido',
        };
    }

    public function render(): View
    {
        return view('livewire.connections.settings-modal', [
            'statusLabel' => $this->statusLabel(),
        ]);
    }
}
