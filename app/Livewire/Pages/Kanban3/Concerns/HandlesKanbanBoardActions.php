<?php

namespace App\Livewire\Pages\Kanban3\Concerns;

use App\Models\Card;
use App\Models\Funnel;
use App\Models\Stage;
use App\Services\ChatwootService;
use Illuminate\Support\Facades\Log;

trait HandlesKanbanBoardActions
{
    public function handleSort(int|string $cardId, int $position, int|string $stageId): void
    {
        $this->moveCardToStage((int) $cardId, $position, (int) $stageId);

        $this->dispatch('notify', type: 'success', message: 'Card movido com sucesso!');
    }

    protected function moveCardToStage(int $cardId, int $position, int $stageId): void
    {
        $card = Card::query()->with([
            'stage' => fn($query) => $query->with('funnel:id,is_system')->select('id', 'funnel_id'),
        ])->select('id', 'stage_id', 'conversation_id')->findOrFail($cardId);

        $targetStage = Stage::query()->with('funnel:id,is_system')->select('id', 'funnel_id', 'chatwoot_status')->findOrFail($stageId);

        // $oldStageId = $card->stage_id;
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

        /* Deprecated: Reordering is now handled in the frontend without re-saving all cards. The backend simply updates the moved card's stage and order, and the frontend takes care of the rest. This avoids unnecessary database updates and potential
            if ($shouldUpdateStage) {
                $this->reorderCardsInStage($targetStage->id, $cardId, $position);

                if ($oldStageId !== $targetStage->id) {
                    $this->reindexStageCards($oldStageId);
                }
            }
        */

        $this->dispatch('notify', type: 'success', message: 'Card movido com sucesso!', duration: 1000);

        $this->refreshBoardData();
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

        $cacheKey = $this->boardCacheKey($this->getAccountId(), "stages:{$this->activeFunnel->id}");
        cache()->forget($cacheKey);
    }

    public function deleteCard(int $cardId): void
    {
        $card = Card::query()->with(['stage.funnel'])->find($cardId);

        if (! $card) {
            return;
        }

        // If the card is already in a system funnel stage, delete it.
        if ($card->stage?->funnel && $card->stage->funnel->is_system) {
            Card::destroy($cardId);
            unset($this->stageCards, $this->stages);

            return;
        }

        // Otherwise, try to move it to the account's system funnel's first stage.
        $systemFunnel = Funnel::where('account_id', $this->getAccountId())->where('is_system', true)->first();

        if (! $systemFunnel) {
            // No system funnel found — fallback to delete to avoid orphaned cards.
            Card::destroy($cardId);
            unset($this->stageCards, $this->stages);

            return;
        }

        $systemStage = Stage::where('funnel_id', $systemFunnel->id)->orderBy('order')->first();

        if (! $systemStage) {
            // No stage in the system funnel — fallback to delete.
            Card::destroy($cardId);
            unset($this->stageCards, $this->stages);

            return;
        }

        $card->update([
            'stage_id' => $systemStage->id,
        ]);

        unset($this->stageCards, $this->stages);

        $accountId = $this->getAccountId();
        $searchKey = md5(trim($this->search));
        $cacheKey = $this->boardCacheKey($accountId, "stage-cards:{$this->activeFunnel->id}:{$searchKey}");
        cache()->forget($cacheKey);
    }

    public function openCardDetail(int $conversationId): void
    {
        $this->dispatch('open-card-detail', $conversationId);
    }

    public function closeCardDetail(): void
    {
        $this->dispatch('close-card-detail');
    }

    /**
     * Deprecated: Reordering is now handled in the frontend without re-saving all cards. This method is kept for reference and potential future use if server-side reordering is needed again.
     */
    private function reorderCardsInStage(int $stageId, int $movedCardId, int $newPosition): void
    {
        return;

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

    /**
     * Deprecated: This method is no longer used due to frontend handling of card ordering. Kept for reference.
     */
    private function reindexStageCards(int $stageId): void
    {
        return;

        $cards = Card::where('stage_id', $stageId)->orderBy('order')->get();

        foreach ($cards->values() as $index => $card) {
            if (! $card instanceof Card) {
                continue;
            }

            $card->update(['order' => $index]);
        }
    }
}
