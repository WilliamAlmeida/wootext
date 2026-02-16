<?php

namespace App\Livewire\Pages\Dashboard;

use App\Models\Card;
use App\Models\Funnel;
use App\Services\ConnectionNameHelper;
use App\Services\EvolutionService;
use App\Services\WahaService;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.auth')]
class Dashboard extends Component
{
    #[Computed]
    public function cardsCount(): int
    {
        return Card::count();
    }

    #[Computed]
    public function funnelsCount(): int
    {
        return Funnel::count();
    }

    #[Computed(persist: true, seconds: 600, cache: true, key: 'dashboard_connections_count')]
    public function connectionsCount(): int
    {
        $evolutionInstancesCount = 0;
        $wahaSessionsCount = 0;

        $accountId = auth()->user()->account_id;

        try {
            $evolutionInstances = $this->onlyMyInstances(app(EvolutionService::class)->listInstances(), $accountId);
            $evolutionInstancesCount = count($evolutionInstances);
        } catch (\Throwable $e) {
            // Ignora erro na listagem
        }

        try {
            $wahaSessions = $this->onlyMyInstances(app(WahaService::class)->listSessions(), $accountId);
            $wahaSessionsCount = count($wahaSessions);
        } catch (\Throwable $e) {
            // Ignora erro na listagem
        }

        return $evolutionInstancesCount + $wahaSessionsCount;
    }

    private function onlyMyInstances($instances, $accountId): array
    {
        return array_filter($instances, function ($instance) use ($accountId) {
            $friendlyName = ConnectionNameHelper::belongsToAccount($instance['name'] ?? '', $accountId);
            return !empty($friendlyName);
        });
    }

    public function render(): View
    {
        return view('pages.dashboard.dashboard');
    }
}
