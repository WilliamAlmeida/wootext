<?php

use App\Livewire\Pages\Connections\ConnectionManager;
use App\Services\EvolutionService;
use App\Services\WahaService;
use Livewire\Livewire;

use function Pest\Laravel\mock;

it('renders empty state when no instances exist', function () {
    mock(EvolutionService::class, function ($mock): void {
        $mock->shouldReceive('listInstances')->once()->andReturn([]);
    });

    mock(WahaService::class, function ($mock): void {
        $mock->shouldReceive('listSessions')->once()->andReturn([]);
    });

    Livewire::test(ConnectionManager::class)
        ->assertSee('Nenhuma conexão encontrada');
});

it('opens the create modal', function () {
    mock(EvolutionService::class, function ($mock): void {
        $mock->shouldReceive('listInstances')->once()->andReturn([]);
    });

    mock(WahaService::class, function ($mock): void {
        $mock->shouldReceive('listSessions')->once()->andReturn([]);
    });

    Livewire::test(ConnectionManager::class)
        ->call('openCreateModal')
        ->assertSet('showCreateModal', true)
        ->assertSee('Nova Conexão');
});
