<?php

namespace App\Livewire\Pages\Kanban;

use App\Models\Funnel;
use Livewire\Component;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use App\Livewire\Pages\Kanban\Concerns\LoadsKanbanBoardData;
use App\Livewire\Pages\Kanban\Concerns\HandlesKanbanBoardActions;

#[Layout('layouts.auth')]
class Board extends Component
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
        cache()->forget("kanban:chatwoot:conversations:{$this->getAccountId()}");
        unset($this->funnels, $this->activeFunnel, $this->stages, $this->stageCards);
    }

    #[On('card-moved')]
    public function handleCardMoved(): void
    {
        $this->refreshBoardData();
    }

    public function render(): View
    {
        return view('pages.kanban.board');
    }
}
