<?php

use App\Livewire\Pages\Kanban\BoardModals;
use App\Models\Funnel;
use App\Models\Stage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('board modals can create funnels with default stages', function () {
    config(['services.chatwoot.account_id' => 1]);

    Livewire::test(BoardModals::class)
        ->set('newFunnelName', 'Vendas')
        ->set('newFunnelColor', '#123456')
        ->call('saveFunnel');

    $funnel = Funnel::where('name', 'Vendas')->firstOrFail();

    expect($funnel->color)->toBe('#123456');
    expect($funnel->account_id)->toBe(1);
    expect(Stage::where('funnel_id', $funnel->id)->count())->toBe(3);
});

test('board modals can create a stage for a funnel', function () {
    config(['services.chatwoot.account_id' => 1]);

    $funnel = Funnel::create([
        'name' => 'Suporte',
        'account_id' => 1,
        'order' => 0,
        'color' => '#0EA5E9',
    ]);

    Livewire::test(BoardModals::class)
        ->call('openStageModal', null, $funnel->id)
        ->set('newStageName', 'Qualificacao')
        ->set('newStageColor', '#111111')
        ->call('saveStage');

    expect(Stage::where('funnel_id', $funnel->id)->count())->toBe(1);
    $stage = Stage::where('funnel_id', $funnel->id)->firstOrFail();

    expect($stage->name)->toBe('Qualificacao');
    expect($stage->color)->toBe('#111111');
});
