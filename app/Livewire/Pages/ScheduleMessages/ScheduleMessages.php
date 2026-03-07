<?php

namespace App\Livewire\Pages\ScheduleMessages;

use App\Models\Card;
use App\Models\ScheduledMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.auth')]
class ScheduleMessages extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $content = '';
    public ?string $datetime = null;
    public $attachment = null;

    public bool $showScheduleModal = false;

    public ?Card $card = null;

    public ?int $editingMessageId = null;
    public ?ScheduledMessage $editingMessage = null;

    public ?int $selectedConversationId = null;

    public $perPage = 20;

    #[Computed]
    public function getScheduledMessages(): LengthAwarePaginator
    {
        return ScheduledMessage::with('card')->orderBy('scheduled_at', 'desc')->paginate($this->perPage);
    }

    public function create(): void
    {
        $this->resetForm();

        $this->datetime = now()->addMinutes(5)->format('Y-m-d\TH:i');

        $this->showScheduleModal = true;
    }

    public ?string $filterCard = null;

    public function updatedFilterCard(): void
    {
        $this->selectedConversationId = null;
    }

    #[Computed]
    public function availableCards(): array
    {
        $query = Card::whereNotNull('phone_number');

        $filterCard = trim((string) $this->filterCard);

        if ($filterCard !== '') {
            $query->where(function (Builder $builder) use ($filterCard): void {
                $builder
                    ->where('custom_name', 'like', '%' . $filterCard . '%')
                    ->orWhere('phone_number', 'like', '%' . $filterCard . '%')
                    ->orWhere('conversation_id', 'like', '%' . $filterCard . '%');
            });
        }

        return $query->orderBy('custom_name')->orderBy('updated_at', 'desc')->limit(20)->get()->toArray();
    }

    public function edit(int $messageId): void
    {
        $message = ScheduledMessage::with('card')->findOrFail($messageId);

        if ($message->status !== 'pending') {
            $this->dispatch('notify', type: 'warning', message: 'Somente mensagens pendentes podem ser editadas.');
            return;
        }

        $this->card = $message->card;
        $this->editingMessageId = $message->id;
        $this->editingMessage = $message;
        $this->content = $message->message;
        $this->datetime = $message->scheduled_at->format('Y-m-d\TH:i');
        $this->attachment = null;

        $this->showScheduleModal = true;
    }

    public function delete(int $messageId): void
    {
        $message = ScheduledMessage::with('card')->findOrFail($messageId);

        if ($message->status !== 'pending') {
            $this->dispatch('notify', type: 'warning', message: 'Somente mensagens pendentes podem ser deletadas.');
            return;
        }

        $attachments = $this->normalizeAttachments($message->attachments);

        foreach ($attachments as $attachmentMeta) {
            $filePath = $attachmentMeta['filePath'] ?? null;
            $disk = $attachmentMeta['disk'] ?? 'public';

            if (is_string($filePath) && $filePath !== '' && is_string($disk) && Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
            }
        }

        $message->delete();

        $this->dispatch('notify', type: 'success', message: 'Mensagem agendada deletada com sucesso.');
    }

    public function save(): void
    {
        if (! $this->card) {
            // If no card is currently set, try to resolve from the selected conversation id.
            if (is_numeric($this->selectedConversationId)) {
                $this->card = Card::where('conversation_id', (int) $this->selectedConversationId)->firstOrFail();
            } else {
                $this->dispatch('notify', type: 'warning', message: 'Selecione uma conversa antes de agendar.');

                return;
            }
        }

        $dateTime = $this->datetime;
        $this->datetime = strlen($dateTime) === 16 ? $dateTime . ':00' : $dateTime;

        $this->validate([
            'content' => 'nullable|string|max:1000|required_without:attachment',
            'datetime' => 'required|date|before:now +1 year',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx', // max 10MB
        ]);

        try {
            $scheduledAt = now()->parse($this->datetime);

            if ($this->editingMessageId) {
                $message = $this->card->scheduledMessages()->findOrFail($this->editingMessageId);

                if ($message->status !== 'pending') {
                    $this->dispatch('notify', type: 'warning', message: 'Somente mensagens pendentes podem ser editadas.');

                    return;
                }

                $data = [
                    'message' => trim($this->content),
                    'scheduled_at' => $scheduledAt,
                    'error_message' => null,
                ];

                if ($this->attachment) {
                    $this->deleteStoredAttachments($message->attachments);
                    $data['attachments'] = $this->buildAttachmentsPayload();
                }

                $message->update($data);

                $this->dispatch('notify', type: 'success', message: 'Mensagem agendada atualizada com sucesso.');
            } else {
                $this->card->scheduledMessages()->create([
                    'conversation_id' => $this->card->conversation_id,
                    'account_id' => $this->card->account_id,
                    'message' => trim($this->content),
                    'scheduled_at' => $scheduledAt,
                    'status' => 'pending',
                    'created_by' => Auth::id() ?? 0,
                    'api_token' => config('services.chatwoot.api_token'),
                    'attachments' => $this->buildAttachmentsPayload(),
                ]);

                $this->dispatch('notify', type: 'success', message: 'Mensagem agendada com sucesso.');
            }

            $this->resetForm();

        } catch (\Throwable $th) {
            $this->datetime = $dateTime;

            throw $th;
        }
    }

    private function resetForm(): void
    {
        $this->content = '';
        $this->attachment = null;
        $this->card = null;
        $this->editingMessageId = null;
        $this->editingMessage = null;
        $this->datetime = now()->addMinutes(5)->format('Y-m-d\TH:i');
        $this->selectedConversationId = null;
        $this->filterCard = null;
        $this->showScheduleModal = false;
    }

    private function buildAttachmentsPayload(): ?array
    {
        if (! $this->attachment) {
            return null;
        }

        $storedPath = $this->attachment->store('scheduled_attachments', 'public');

        return [[
            'disk' => 'public',
            'filePath' => $storedPath,
            'fileName' => basename($storedPath),
            'originalName' => $this->attachment->getClientOriginalName(),
            'fileSize' => $this->attachment->getSize(),
            'fileType' => $this->attachment->getMimeType(),
        ]];
    }

    private function normalizeAttachments(array|string|null $attachments): array
    {
        if (is_array($attachments)) {
            return $attachments;
        }

        if (! is_string($attachments) || $attachments === '') {
            return [];
        }

        $decoded = json_decode($attachments, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function deleteStoredAttachments(array|string|null $attachments): void
    {
        foreach ($this->normalizeAttachments($attachments) as $attachmentMeta) {
            $filePath = $attachmentMeta['filePath'] ?? null;
            $disk = $attachmentMeta['disk'] ?? 'public';

            if (is_string($filePath) && $filePath !== '' && is_string($disk) && Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
            }
        }
    }

    public function render(): View
    {
        return view('pages.schedule-messages.index');
    }
}
