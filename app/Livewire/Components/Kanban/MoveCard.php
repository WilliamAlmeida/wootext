<?php

namespace App\Livewire\Components\Kanban;

use App\Models\Card;
use App\Models\Funnel;
use App\Models\Stage;
use App\Services\ChatwootService;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.auth')]
class MoveCard extends Component
{
    public bool $showMoveModal = false;

    public ?Card $card = null;

    public ?int $moveFunnelId = null;
    public ?int $moveStageId = null;

    #[On('open-move-card-modal')]
    public function openMoveCardModal($conversationId = null): void
    {
        if (is_array($conversationId)) {
            $conversationId = $conversationId['conversationId'] ?? ($conversationId[0] ?? null);
        }

        if (! is_numeric($conversationId)) {
            return;
        }

        $this->card = Card::where('conversation_id', (int) $conversationId)->firstOrFail();

        $this->moveFunnelId = $this->card->stage?->funnel_id;
        $this->moveStageId = $this->card->stage_id;
        $this->showMoveModal = true;
    }
    
    public function moveCard(): void
    {
        if (! $this->card || ! $this->moveStageId) {
            return;
        }

        $this->card->load('stage.funnel');
        $targetStage = Stage::with('funnel')->findOrFail($this->moveStageId);
        $shouldUpdateStage = true;

        if ($targetStage->chatwoot_status && $this->card->stage?->funnel && ! $this->card->stage->funnel->is_system) {
            $shouldUpdateStage = false;
        }

        if ($shouldUpdateStage) {
            $maxOrder = Card::where('stage_id', $targetStage->id)->max('order') ?? 0;

            $this->card->update([
                'stage_id' => $targetStage->id,
                'order' => $maxOrder + 1,
            ]);
        }

        if ($targetStage->chatwoot_status) {
            try {
                app(ChatwootService::class)->updateConversationStatus(
                    $this->card->conversation_id,
                    $targetStage->chatwoot_status,
                );
            } catch (\Throwable $exception) {
                logger()->warning('Failed to sync status on move', ['error' => $exception->getMessage()]);
            }
        }

        $this->showMoveModal = false;
        $this->dispatch('refresh-card-detail');
        $this->dispatch('card-moved');
        $this->dispatch('notify', type: 'success', message: 'Card movido com sucesso!');
    }

    #[Computed]
    public function availableFunnels()
    {
        return Funnel::orderBy('order')->get();
    }

    #[Computed]
    public function availableStages()
    {
        if (! $this->moveFunnelId) {
            return collect();
        }

        return Stage::where('funnel_id', $this->moveFunnelId)->orderBy('order')->get();
    }

    public function render(): View
    {
        return view('livewire.components.kanban.move-card');
    }
}
