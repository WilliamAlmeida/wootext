<?php

namespace App\Livewire\Pages\ScheduleMessages;

use App\Models\Card;
use App\Models\ScheduledMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
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
    public array $availableCards = [];

    public $perPage = 20;

    #[Computed]
    public function getScheduledMessages(): LengthAwarePaginator
    {
        return ScheduledMessage::with('card')->orderBy('scheduled_at', 'desc')->paginate($this->perPage);
    }

    public function create()
    {
        $this->availableCards = Card::orderBy('updated_at', 'desc')->limit(20)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'conversation_id' => $c->conversation_id,
                'custom_name' => $c->custom_name,
            ];
        })->toArray();

        $this->resetForm();

        $this->datetime = now()->addMinutes(5)->format('Y-m-d\TH:i');

        $this->showScheduleModal = true;
    }

    public function edit(int $messageId)
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

    public function delete(int $messageId)
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

    public function save()
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
                    'created_by' => auth()->id() ?? 0,
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
        $this->editingMessageId = null;
        $this->datetime = now()->addMinutes(5)->format('Y-m-d\TH:i');
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

    public function render()
    {
        return view('pages.schedule-messages.index');
    }
}
