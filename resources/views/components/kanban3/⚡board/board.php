<?php

use App\Models\Funnel;
use App\Models\Stage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Json;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Livewire\Pages\Kanban3\Concerns\LoadsKanbanBoardData;
use App\Livewire\Pages\Kanban3\Concerns\HandlesKanbanBoardActions;

new #[Layout('layouts.auth')] class extends Component
{
    use HandlesKanbanBoardActions;
    use LoadsKanbanBoardData;

    #[Url]
    public ?int $activeFunnelId = null;

    #[Url(except: '')]
    public string $search = '';

    public function mount(): void
    {
        $accountId = $this->getAccountId();

        $this->ensureSystemFunnel($accountId);

        if (! $this->activeFunnelId) {
            $this->activeFunnelId = Funnel::query()
                ->where('account_id', $accountId)
                ->orderBy('order')
                ->value('id');
        }
    }

    #[On('funnel-saved')]
    public function handleFunnelSaved(?int $funnelId = null): void
    {
        if ($funnelId) {
            $this->activeFunnelId = $funnelId;
        }

        $this->refreshBoardData();
        $this->dispatch('notify', type: 'success', message: 'Funil salvo com sucesso!');
    }

    #[On('stage-saved')]
    public function handleStageSaved(): void
    {
        $this->refreshBoardData();
    }

    public function refreshBoardData(): void
    {
        $versionKey = "kanban:board:version:{$this->getAccountId()}";

        if (! cache()->has($versionKey)) {
            cache()->forever($versionKey, 1);
        }

        cache()->increment($versionKey);
        cache()->forget("kanban:chatwoot:conversations:{$this->getAccountId()}");
        unset($this->funnels, $this->activeFunnel, $this->stages, $this->stageCards);
    }

    #[On('card-moved')]
    public function handleCardMoved(): void
    {
        $this->refreshBoardData();
    }

    #[Json]
    public function getBoardData(int $cardsPerStage = 10): array
    {
        $cardsPerStage = max(1, $cardsPerStage);

        return [
            'activeFunnelId' => $this->activeFunnelId,
            'search' => $this->search,
            'funnels' => $this->funnels->map(static fn (Funnel $funnel) => [
                'id' => $funnel->id,
                'name' => $funnel->name,
                'color' => $funnel->color,
                'is_system' => (bool) $funnel->is_system,
            ])->values()->all(),
            'activeFunnel' => $this->activeFunnel
                ? [
                    'id' => $this->activeFunnel->id,
                    'name' => $this->activeFunnel->name,
                    'is_system' => (bool) $this->activeFunnel->is_system,
                    'account_id' => $this->activeFunnel->account_id,
                ]
                : null,
            'stages' => $this->buildStagePayload($cardsPerStage),
        ];
    }

    #[Json]
    public function selectFunnelJson(int $funnelId, int $cardsPerStage = 10): array
    {
        $this->selectFunnel($funnelId);

        return $this->getBoardData($cardsPerStage);
    }

    #[Json]
    public function updateSearch(string $search = '', int $cardsPerStage = 10): array
    {
        $this->search = trim($search);
        unset($this->stageCards);

        return $this->getBoardData($cardsPerStage);
    }

    #[Json]
    public function refreshBoard(int $cardsPerStage = 10): array
    {
        $this->refreshBoardData();

        return $this->getBoardData($cardsPerStage);
    }

    #[Json]
    public function moveCard(int $cardId, int $toStageId, int $position, int $cardsPerStage = 10): array
    {
        $this->moveCardToStage($cardId, max(0, $position), $toStageId);

        return $this->getBoardData($cardsPerStage);
    }

    #[Json]
    public function loadMoreCards(int $stageId, int $offset, int $limit = 10): array
    {
        $limit = max(1, $limit);
        $offset = max(0, $offset);

        $stage = $this->stages->firstWhere('id', $stageId);

        if (! $stage instanceof Stage) {
            return [
                'stageId' => $stageId,
                'cards' => [],
                'nextOffset' => $offset,
                'hasMore' => false,
                'total' => 0,
            ];
        }

        $cards = $this->stageCards[$stageId] ?? [];
        $nextCards = array_slice($cards, $offset, $limit);
        $nextOffset = $offset + count($nextCards);

        return [
            'stageId' => $stageId,
            'cards' => $nextCards,
            'nextOffset' => $nextOffset,
            'hasMore' => $nextOffset < count($cards),
            'total' => count($cards),
        ];
    }

    #[Json]
    public function removeCard(int $cardId, int $cardsPerStage = 10): array
    {
        $this->deleteCard($cardId);

        return $this->getBoardData($cardsPerStage);
    }

    #[Json]
    public function removeStage(int $stageId, int $cardsPerStage = 10): array
    {
        $this->deleteStage($stageId);

        return $this->getBoardData($cardsPerStage);
    }

    #[Json]
    public function removeFunnel(int $funnelId, int $cardsPerStage = 10): array
    {
        $this->deleteFunnel($funnelId);

        return $this->getBoardData($cardsPerStage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildStagePayload(int $cardsPerStage): array
    {
        return $this->stages->map(function (Stage $stage) use ($cardsPerStage): array {
            $cards = $this->stageCards[$stage->id] ?? [];
            $visibleCards = array_slice($cards, 0, $cardsPerStage);

            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'color' => $stage->color,
                'chatwoot_status' => $stage->chatwoot_status,
                'cards' => $visibleCards,
                'total_cards' => count($cards),
                'has_more' => count($cards) > count($visibleCards),
            ];
        })->values()->all();
    }
};
