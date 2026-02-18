<?php

use App\Services\ConnectionNameHelper;
use App\Services\EvolutionService;
use App\Services\WahaService;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public array $instances = [];

    public string $provider = 'evolution';

    public bool $loading = false;

    public function mount()
    {
        $this->loadInstances();
    }

    private function getAccountId(): int
    {
        return config('services.chatwoot.account_id');
    }

    #[On('fetchInstances')]
    public function handleFetchInstances(string $provider): void
    {
        if ($this->provider === $provider) {
            $this->loadInstances();
        }
    }

    public function loadInstances(): void
    {
        $this->loading = true;
        $instances = [];

        $cacheKey = "instances_{$this->provider}_account_{$this->getAccountId()}";
        $cached = cache()->get($cacheKey);
        if ($cached) {
            $this->instances = $cached;
            $this->loading = false;
            return;
        }

        if($this->provider == 'waha') {
            try {
                $wahaSessions = app(WahaService::class)->listSessions();
    
                foreach ($wahaSessions as $session) {
                    $name = $session['name'] ?? '';
    
                    if (! ConnectionNameHelper::belongsToAccount($name, $this->getAccountId())) {
                        continue;
                    }
    
                    $status = $session['status'] ?? 'STOPPED';
    
                    $instances[] = [
                        'name' => $name,
                        'friendly_name' => ConnectionNameHelper::extractFriendlyName($name, $this->getAccountId()),
                        'provider' => 'waha',
                        'status' => $this->normalizeStatus($status),
                        'raw_status' => $status,
                    ];
                }
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Waha list failed', ['error' => $exception->getMessage()]);
            }
        }else if($this->provider == 'evolution') {
            try {
                $evolutionInstances = app(EvolutionService::class)->listInstances();

                foreach ($evolutionInstances as $instance) {
                    $name = $instance['name'] ?? '';

                    if (! ConnectionNameHelper::belongsToAccount($name, $this->getAccountId())) {
                        continue;
                    }

                    $status = $instance['instance']['status'] ?? ($instance['connectionStatus'] ?? 'unknown');

                    $instances[] = [
                        'name' => $name,
                        'friendly_name' => ConnectionNameHelper::extractFriendlyName($name, $this->getAccountId()),
                        'provider' => 'evolution',
                        'status' => $this->normalizeStatus($status),
                        'raw_status' => $status,
                    ];
                }
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Evolution list failed', ['error' => $exception->getMessage()]);
            }
        }

        $this->instances = $instances;
        $this->loading = false;

        // Cache por 30 segundos para evitar chamadas excessivas
        cache()->put($cacheKey, $instances, now()->addSeconds(30));
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
};
?>

@placeholder
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <div class="card p-4 space-y-3 animate-pulse min-h-[88px]">
        <div class="flex items-start justify-between">
            <div class="space-y-3">
                <div class="h-6 bg-zinc-300 dark:bg-zinc-700 rounded w-1/2"></div>
                <div class="flex items-center gap-2">
                    <div class="h-5 bg-zinc-300 dark:bg-zinc-700 rounded-lg w-14"></div>
                    <div class="h-5 bg-zinc-300 dark:bg-zinc-700 rounded-lg w-24"></div>
                </div>
            </div>
            <div class="flex gap-x-1">
                <div class="h-10 w-11 bg-zinc-300 dark:bg-zinc-700 rounded-lg"></div>
                <div class="h-10 w-11 bg-zinc-300 dark:bg-zinc-700 rounded-lg"></div>
                <div class="h-10 w-11 bg-zinc-300 dark:bg-zinc-700 rounded-lg"></div>
                <div class="h-10 w-11 bg-zinc-300 dark:bg-zinc-700 rounded-lg"></div>
            </div>
        </div>
    </div>
</div>
@endplaceholder

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($instances as $instance)
        <div class="card p-4 space-y-3" wire:key="instance-{{ $instance['provider'] }}-{{ $instance['name'] }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-base text-zinc-900 dark:text-zinc-100">{{ $instance['friendly_name'] }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="badge {{ $instance['provider'] === 'evolution' ? 'text-blue-700 dark:text-blue-300' : 'text-purple-700 dark:text-purple-300' }}">
                            {{ ucfirst($instance['provider']) }}
                        </span>
                        <span class="badge {{ match($instance['status']) { 
                            'connected' => 'text-green-700 dark:text-green-300', 
                            'disconnected' => 'text-red-700 dark:text-red-300', 
                            'qr_pending' => 'text-yellow-700 dark:text-yellow-300', 
                            default => 'text-zinc-700 dark:text-zinc-300' 
                        } }}">
                            {{ match($instance['status']) {
                                'connected' => 'Conectado',
                                'disconnected' => 'Desconectado',
                                'qr_pending' => 'QR Pendente',
                                default => 'Desconhecido',
                            } }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-1 *:hover:cursor-pointer">
                    @if ($instance['status'] !== 'connected')
                        <button type="button" 
                            wire:click="$dispatch('connectInstance', {'name': '{{ $instance['name'] }}', 'provider': '{{ $instance['provider'] }}'})"
                            class="btn btn-ghost btn-xs" title="Conectar (QR Code)">
                            <x-phosphor-qr-code class="size-5" />
                        </button>
                    @endif

                    @if ($instance['status'] === 'connected')
                        <button type="button" 
                            wire:click="$dispatch('disconnectInstance', ['name' => '{{ $instance['name'] }}'])"
                            wire:confirm="Tem certeza que deseja desconectar a instância '{{ $instance['friendly_name'] }}'?"
                            class="btn btn-ghost btn-xs text-red-600" title="Desconectar">
                            <x-phosphor-sign-out class="size-5" />
                        </button>
                    @endif

                    <button type="button" 
                        wire:click="$dispatch('restartInstance', {'name': '{{ $instance['name'] }}', 'provider': '{{ $instance['provider'] }}'})"
                        wire:confirm="Tem certeza que deseja reiniciar a instância '{{ $instance['friendly_name'] }}'? Isso irá desconectar e reconectar a instância, o que pode resolver problemas temporários."
                        class="btn btn-ghost btn-xs" title="Reiniciar">
                        <x-phosphor-arrows-clockwise class="size-5" />
                    </button>

                    <button type="button" 
                        wire:click="$dispatch('openSettingsModal', {name: '{{ $instance['name'] }}', provider: '{{ $instance['provider'] }}', friendly_name: '{{ $instance['friendly_name'] }}'})"
                        class="btn btn-ghost btn-xs" title="Configurações">
                        <x-phosphor-gear class="size-5" />
                    </button>

                    <button type="button" 
                        wire:click="$dispatch('deleteInstance', {'name': '{{ $instance['name'] }}', 'provider': '{{ $instance['provider'] }}', 'friendly_name': '{{ $instance['friendly_name'] }}'})"
                        class="btn btn-ghost btn-xs text-red-600" title="Excluir">
                        <x-phosphor-trash class="size-5" />
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>