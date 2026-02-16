<?php

namespace App\Jobs;

use App\Models\ScheduledMessage;
use App\Services\ChatwootService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SendScheduledMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $scheduledMessageId) {}

    /**
     * Execute the job.
     */
    public function handle(ChatwootService $chatwootService): void
    {
        $scheduledMessage = ScheduledMessage::query()->find($this->scheduledMessageId);

        if (! $scheduledMessage) {
            return;
        }

        if (! in_array($scheduledMessage->status, ['queued', 'pending'], true)) {
            return;
        }

        try {
            $attachmentPath = $this->resolveAttachmentPath($scheduledMessage);

            $sent = $chatwootService->sendScheduledMessage($scheduledMessage, $attachmentPath);

            if ($sent) {
                $scheduledMessage->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                return;
            }

            $scheduledMessage->update([
                'status' => 'failed',
                'error_message' => 'Falha ao enviar via Chatwoot API',
            ]);
        } catch (Throwable $exception) {
            $scheduledMessage->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveAttachmentPath(ScheduledMessage $scheduledMessage): ?string
    {
        $attachments = $scheduledMessage->attachments;

        if (is_string($attachments)) {
            $decoded = json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($attachments) || $attachments === []) {
            return null;
        }

        $firstAttachment = $attachments[0] ?? [];
        $relativePath = $firstAttachment['filePath'] ?? null;
        $disk = $firstAttachment['disk'] ?? 'local';

        if (! is_string($relativePath) || ! is_string($disk) || $relativePath === '') {
            return null;
        }

        if (! Storage::disk($disk)->exists($relativePath)) {
            return null;
        }

        return Storage::disk($disk)->path($relativePath);
    }
}
