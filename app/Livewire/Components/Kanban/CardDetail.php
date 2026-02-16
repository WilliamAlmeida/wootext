<?php

namespace App\Livewire\Components\Kanban;

use App\Models\Card;
use App\Models\Task;
use App\Services\ChatwootService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.auth')]
class CardDetail extends Component
{
    public ?int $conversationId = null;

    public ?array $conversation = null;

    public ?Card $card = null;

    public string $newTaskTitle = '';

    public string $noteContent = '';

    public bool $showMoveModal = false;

    public array $notes = [];

    public function mount(?int $conversationId = null): void
    {
        $this->conversationId = $conversationId;

        if ($conversationId) {
            $this->openFromEvent($conversationId);
        }
        // $this->openFromEvent(117);
    }

    #[On('open-card-detail')]
    public function openFromEvent(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->loadCard();
        $this->loadConversation();
        $this->loadNotes();
    }

    #[On('close-card-detail')]
    public function closeFromEvent(): void
    {
        $this->conversationId = null;
        $this->card = null;
        $this->conversation = null;
    }

    public function render(): View
    {
        return view('livewire.components.kanban.card-detail');
    }

    #[On('refresh-card-detail')]
    public function loadCard(): void
    {
        $this->card = Card::query()
            ->where('conversation_id', $this->conversationId)
            ->with(['stage.funnel', 'customFieldValues.customField'])
            ->first();
    }

    public function loadConversation(): void
    {
        try {
            $data = app(ChatwootService::class)->getConversation($this->conversationId);
            $this->conversation = is_array($data) ? $data : null;
        } catch (\Throwable) {
            $this->conversation = null;
        }
    }

    public function loadNotes(): void
    {
        try {
            $data = app(ChatwootService::class)->getNotes($this->card->contact_id);
            $this->notes = collect(is_array($data) ? $data : [])->transform(function ($note) {
                $note['created_at_formatted'] = now()->parse($note['created_at'])->diffForHumans();
                return $note;
            })->toArray();  

        } catch (\Throwable $e) {
            $this->notes = [];
        }
    }

    public function getTasks()
    {
        if (! $this->card) {
            return collect();
        }

        return Task::where('conversation_id', $this->conversationId)
            ->where('account_id', $this->card->account_id)
            ->orderBy('created_at')
            ->get();
    }

    public function addTask(): void
    {
        $this->validate(['newTaskTitle' => 'required|string|max:255']);

        Task::create([
            'conversation_id' => $this->conversationId,
            'account_id' => $this->card?->account_id ?? 0,
            'title' => $this->newTaskTitle,
            'completed' => false,
            'created_by' => auth()->id() ?? 0,
        ]);

        $this->newTaskTitle = '';
    }

    public function toggleTask(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $task->update(['completed' => ! $task->completed]);
    }

    public function deleteTask(int $taskId): void
    {
        Task::destroy($taskId);
    }

    public function sendNote(): void
    {
        $this->validate(['noteContent' => 'required|string']);

        try {
            app(ChatwootService::class)->createNote(
                $this->card->contact_id,
                $this->noteContent,
            );

            $this->noteContent = '';

            $this->loadNotes();

            $this->skipRender();

        } catch (\Throwable $exception) {
            Log::error('Failed to send note', ['error' => $exception->getMessage()]);
        }
    }

    public function deleteNote($noteId): void
    {
        try {
            app(ChatwootService::class)->deleteNote($this->card->contact_id, $noteId);
            $this->loadNotes();
            $this->skipRender();
        } catch (\Throwable $exception) {
            Log::error('Failed to delete note', ['error' => $exception->getMessage()]);
        }
    }
}
