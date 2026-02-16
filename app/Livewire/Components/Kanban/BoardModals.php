<?php

namespace App\Livewire\Components\Kanban;

use App\Models\Funnel;
use App\Models\Stage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.auth')]
class BoardModals extends Component
{
    public bool $showFunnelModal = false;
    public string $newFunnelName = '';
    public string $newFunnelColor = '#3B82F6';
    public ?int $editingFunnelId = null;

    public bool $showStageModal = false;
    public string $newStageName = '';
    public string $newStageColor = '#6B7280';
    public ?int $editingStageId = null;
    public ?int $addStageToFunnelId = null;

    private function getAccountId()
    {
        return config('services.chatwoot.account_id');
    }

    #[On('open-funnel-modal')]
    public function openFunnelModal(?int $funnelId = null): void
    {
        $this->editingFunnelId = $funnelId;

        if ($funnelId) {
            $funnel = Funnel::findOrFail($funnelId);
            $this->newFunnelName = $funnel->name;
            $this->newFunnelColor = $funnel->color;
        } else {
            $this->newFunnelName = '';
            $this->newFunnelColor = '#3B82F6';
        }

        $this->showFunnelModal = true;
    }

    public function saveFunnel(): void
    {
        $this->validate([
            'newFunnelName' => 'required|string|max:255',
            'newFunnelColor' => 'required|string|max:7',
        ]);

        if ($this->editingFunnelId) {
            $funnel = Funnel::findOrFail($this->editingFunnelId);
            $funnel->update([
                'name' => $this->newFunnelName,
                'color' => $this->newFunnelColor,
            ]);
        } else {
            $maxOrder = Funnel::where('account_id', $this->getAccountId())->max('order') ?? 0;

            $funnel = Funnel::create([
                'name' => $this->newFunnelName,
                'account_id' => $this->getAccountId(),
                'order' => $maxOrder + 1,
                'color' => $this->newFunnelColor,
            ]);

            $defaultStages = ['Novo', 'Em Andamento', 'Finalizado'];
            $colors = ['#22C55E', '#3B82F6', '#6B7280'];

            foreach ($defaultStages as $index => $stageName) {
                $funnel->stages()->create([
                    'name' => $stageName,
                    'order' => $index,
                    'color' => $colors[$index],
                ]);
            }
        }

        $this->showFunnelModal = false;
        $this->resetFunnelForm();
        $this->dispatch('funnel-saved', funnelId: $funnel->id ?? $this->editingFunnelId);
    }

    private function resetFunnelForm(): void
    {
        $this->reset('newFunnelName', 'newFunnelColor', 'editingFunnelId');
    }

    #[On('open-stage-modal')]
    public function openStageModal(?int $stageId = null, ?int $funnelId = null): void
    {
        $this->editingStageId = $stageId;
        $this->addStageToFunnelId = $funnelId ?? Funnel::where('account_id', $this->getAccountId())
            ->orderBy('order')
            ->value('id');

        if ($stageId) {
            $stage = Stage::findOrFail($stageId);
            $this->newStageName = $stage->name;
            $this->newStageColor = $stage->color;
            $this->addStageToFunnelId = $stage->funnel_id;
        } else {
            $this->newStageName = '';
            $this->newStageColor = '#6B7280';
        }

        $this->showStageModal = true;
    }

    public function saveStage(): void
    {
        $this->validate([
            'newStageName' => 'required|string|max:255',
            'newStageColor' => 'required|string|max:7',
        ]);

        if ($this->editingStageId) {
            $stage = Stage::findOrFail($this->editingStageId);
            $stage->update([
                'name' => $this->newStageName,
                'color' => $this->newStageColor,
            ]);
        } else {
            if (! $this->addStageToFunnelId) {
                return;
            }

            $maxOrder = Stage::where('funnel_id', $this->addStageToFunnelId)->max('order') ?? 0;

            $stage = Stage::create([
                'name' => $this->newStageName,
                'funnel_id' => $this->addStageToFunnelId,
                'order' => $maxOrder + 1,
                'color' => $this->newStageColor,
            ]);
        }

        $this->showStageModal = false;
        $this->resetStageForm();
        $this->dispatch('stage-saved', stageId: $stage->id ?? $this->editingStageId);
        $this->dispatch('notify', type: 'success', message: 'Etapa salva com sucesso!');
    }

    private function resetStageForm(): void
    {
        $this->reset('newStageName', 'newStageColor', 'editingStageId', 'addStageToFunnelId');
    }

    public function render(): View
    {
        return view('livewire.components.kanban.board-modals');
    }
}
