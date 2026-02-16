<?php

namespace App\Livewire\Components;

use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.auth')]
class ScheduleMessagesModal extends Component
{
    use WithFileUploads;

    public string $content = '';
    public ?string $datetime = null;
    public $attachment = null;

    public bool $showScheduleModal = false;

    public ?Card $card = null;

    public ?int $editingMessageId = null;

    #[Computed]
    public function getScheduledMessages()
    {
        if (! $this->card) {
            return collect([]);
        }

        return $this->card->scheduledMessages()->orderBy('scheduled_at', 'desc')->get();
    }

    #[On('open-schedule-modal')]
    public function openScheduleModal($conversationId = null): void
    {
        if (is_array($conversationId)) {
            $conversationId = $conversationId['conversationId'] ?? ($conversationId[0] ?? null);
        }

        if (! is_numeric($conversationId)) {
            return;
        }

        $this->card = Card::where('conversation_id', (int) $conversationId)->firstOrFail();

        $this->resetForm();

        $this->datetime = now()->addMinutes(5)->format('Y-m-d\TH:i');

        $this->showScheduleModal = true;
    }

    public function editSchedule(int $messageId): void
    {
        if (! $this->card) {
            return;
        }

        $message = $this->card->scheduledMessages()->findOrFail($messageId);

        if ($message->status !== 'pending') {
            $this->dispatch('notify', type: 'warning', message: 'Somente mensagens pendentes podem ser editadas.');

            return;
        }

        $this->editingMessageId = $message->id;
        $this->content = $message->message;
        $this->datetime = $message->scheduled_at->format('Y-m-d\TH:i');
        $this->attachment = null;

        $this->showScheduleModal = true;
    }

    public function deleteSchedule(int $messageId): void
    {
        if (! $this->card) {
            return;
        }

        $message = $this->card->scheduledMessages()->findOrFail($messageId);

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

    public function saveSchedule()
    {
        if (! $this->card) {
            return;
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

    public function render(): View
    {
        return view('livewire.components.schedule-messages-modal');
    }
}
