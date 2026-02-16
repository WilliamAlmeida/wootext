<?php

namespace App\Livewire\Pages\Kanban\Concerns;

use App\Models\Card;
use App\Models\Stage;
use App\Models\Funnel;
use App\Services\ChatwootService;
use Illuminate\Support\Facades\Log;

trait HandlesKanbanBoardActions
{
    public function handleSort(int|string $cardId, int $position, int|string $stageId): void
    {
        $card = Card::query()->with('stage.funnel')->findOrFail($cardId);
        $targetStage = Stage::query()->with('funnel')->findOrFail($stageId);

        $oldStageId = $card->stage_id;
        $shouldUpdateStage = true;

        if ($targetStage->chatwoot_status && $card->stage?->funnel && ! $card->stage->funnel->is_system) {
            $shouldUpdateStage = false;
        }

        $updateData = ['order' => $position];

        if ($shouldUpdateStage) {
            $updateData['stage_id'] = $targetStage->id;
        }

        $card->update($updateData);

        if ($targetStage->chatwoot_status) {
            try {
                app(ChatwootService::class)->updateConversationStatus(
                    $card->conversation_id,
                    $targetStage->chatwoot_status,
                );
            } catch (\Throwable $exception) {
                Log::warning('Failed to sync Chatwoot status', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        if ($shouldUpdateStage) {
            $this->reorderCardsInStage($targetStage->id, $cardId, $position);

            if ($oldStageId !== $targetStage->id) {
                $this->reindexStageCards($oldStageId);
            }
        }

        unset($this->stageCards, $this->stages);
        $this->refreshBoardData();

        $this->dispatch('notify', type: 'success', message: 'Card movido com sucesso!');
    }

    public function selectFunnel(int $funnelId): void
    {
        $this->activeFunnelId = $funnelId;
        unset($this->activeFunnel, $this->stages, $this->stageCards);
    }

    public function deleteFunnel(int $funnelId): void
    {
        $funnel = Funnel::findOrFail($funnelId);

        if ($funnel->is_system) {
            return;
        }

        $funnel->delete();

        if ($this->activeFunnelId === $funnelId) {
            $this->activeFunnelId = Funnel::where('account_id', $this->getAccountId())
                ->orderBy('order')
                ->value('id');
        }

        $this->refreshBoardData();
    }

    public function deleteStage(int $stageId): void
    {
        $stage = Stage::withCount('cards')->findOrFail($stageId);

        if ($stage->cards_count > 0) {
            $this->dispatch('notify', type: 'error', message: 'Não é possível excluir uma etapa com cards.');

            return;
        }

        $stage->delete();
        unset($this->stages, $this->stageCards);
    }

    public function deleteCard(int $cardId): void
    {
        Card::destroy($cardId);
        unset($this->stageCards, $this->stages);
    }

    public function openCardDetail(int $conversationId): void
    {
        $this->dispatch('open-card-detail', $conversationId);
    }

    public function closeCardDetail(): void
    {
        $this->dispatch('close-card-detail');
    }

    private function reorderCardsInStage(int $stageId, int $movedCardId, int $newPosition): void
    {
        $cards = Card::where('stage_id', $stageId)
            ->where('id', '!=', $movedCardId)
            ->orderBy('order')
            ->get();

        $order = 0;
        foreach ($cards as $card) {
            if ($order === $newPosition) {
                $order++;
            }
            $card->update(['order' => $order]);
            $order++;
        }
    }

    private function reindexStageCards(int $stageId): void
    {
        $cards = Card::where('stage_id', $stageId)->orderBy('order')->get();

        foreach ($cards->values() as $index => $card) {
            $card->update(['order' => $index]);
        }
    }
}
