<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Funnel;
use App\Models\Stage;
use App\Services\ChatwootService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatwootWebhookController extends Controller
{
    public function __construct(private ChatwootService $chatwootService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $event = $request->input('event');
        $data = $request->all();

        Log::info('Chatwoot webhook received', ['event' => $event]);

        return match ($event) {
            'conversation_created' => $this->handleConversationCreated($data),
            'conversation_status_changed' => $this->handleConversationStatusChanged($data),
            'conversation_updated' => $this->handleConversationUpdated($data),
            'message_created' => $this->handleMessageCreated($data),
            default => response()->json(['status' => 'ignored']),
        };
    }

    private function handleConversationCreated(array $data): JsonResponse
    {
        $conversation = $data['conversation'] ?? $data;
        $conversationId = data_get($conversation, 'id') ?? data_get($data, 'id');
        $accountId = data_get($conversation, 'messages.0.account_id') ?? data_get($data, 'account_id') ?? $this->chatwootService->getAccountId();
        $contactId = data_get($data, 'contact_inbox.contact_id');
        $status = data_get($conversation, 'status', 'open');
        $phoneNumber = data_get($conversation, 'meta.sender.phone_number');
        $customName = data_get($conversation, 'meta.sender.name');

        if (! $conversationId) {
            return response()->json(['error' => 'Missing conversation ID'], 422);
        }

        $systemFunnel = $this->ensureSystemFunnel($accountId);
        $targetStage = $this->findStageByStatus($systemFunnel, $status);

        if (! $targetStage) {
            return response()->json(['error' => 'Stage not found'], 404);
        }

        // Check if card already exists
        $card = Card::query()
            ->where('conversation_id', $conversationId)
            ->where('account_id', $accountId)
            ->first();

        if ($card) {
            // Card already exists, just update stage and phone if needed
            $updates = ['stage_id' => $targetStage->id];

            if ($phoneNumber && ! $card->phone_number) {
                $updates['phone_number'] = $phoneNumber;
            }

            $card->update($updates);
            Log::info('Card already exists, updated', ['card_id' => $card->id]);
        } else {
            // Create new card
            $maxOrder = Card::query()
                ->where('stage_id', $targetStage->id)
                ->max('order') ?? 0;

            $card = Card::query()->create([
                'conversation_id' => $conversationId,
                'account_id' => $accountId,
                'contact_id' => $contactId,
                'stage_id' => $targetStage->id,
                'order' => $maxOrder + 1,
                'phone_number' => $phoneNumber,
                'custom_name' => $customName,
            ]);

            Log::info('Card created', ['card_id' => $card->id, 'conversation_id' => $conversationId]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleConversationStatusChanged(array $data): JsonResponse
    {
        $conversation = $data['conversation'] ?? $data;
        $conversationId = data_get($conversation, 'id') ?? data_get($data, 'id');
        $accountId = data_get($conversation, 'messages.0.account_id', $this->chatwootService->getAccountId());
        $contactId = data_get($conversation, 'contact_inbox.contact_id');
        $status = data_get($conversation, 'status');
        $phoneNumber = data_get($conversation, 'meta.sender.phone_number');

        if (! $conversationId || ! $status) {
            return response()->json(['error' => 'Missing data'], 422);
        }

        $systemFunnel = $this->ensureSystemFunnel($accountId);
        $targetStage = $this->findStageByStatus($systemFunnel, $status);

        if (! $targetStage) {
            return response()->json(['error' => 'Stage not found'], 404);
        }

        $card = Card::query()
            ->where('conversation_id', $conversationId)
            ->where('account_id', $accountId)
            ->first();

        if ($card) {
            // Card exists: move to target stage if in system funnel
            $currentFunnel = $card->stage->funnel;

            if ($currentFunnel->is_system) {
                $updates = ['stage_id' => $targetStage->id];

                if ($phoneNumber && ! $card->phone_number) {
                    $updates['phone_number'] = $phoneNumber;
                }

                $card->update($updates);
            }
        } else {
            // Card doesn't exist: create new card
            $maxOrder = Card::query()
                ->where('stage_id', $targetStage->id)
                ->max('order') ?? 0;

            Card::query()->create([
                'conversation_id' => $conversationId,
                'account_id' => $accountId,
                'contact_id' => $contactId,
                'stage_id' => $targetStage->id,
                'order' => $maxOrder + 1,
                'phone_number' => $phoneNumber,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleConversationUpdated(array $data): JsonResponse
    {
        $conversation = $data['conversation'] ?? $data;
        $conversationId = data_get($conversation, 'id') ?? data_get($data, 'id');
        $accountId = data_get($conversation, 'messages.0.account_id', $this->chatwootService->getAccountId());
        $contactId = data_get($conversation, 'contact_inbox.contact_id');
        $phoneNumber = data_get($conversation, 'meta.sender.phone_number');
        $changedAttributes = data_get($data, 'changed_attributes', []);

        if (! $conversationId) {
            return response()->json(['error' => 'Missing conversation ID'], 422);
        }

        // Only update existing cards (don't create)
        $card = Card::query()
            ->where('conversation_id', $conversationId)
            ->where('account_id', $accountId)
            ->first();

        if ($card) {
            $updates = [];

            // Update phone number if available and not already set
            if ($phoneNumber && ! $card->phone_number) {
                $updates['phone_number'] = $phoneNumber;
            }
            // Update contact_id if available and not already set
            if($contactId && ! $card->contact_id) {
                $updates['contact_id'] = $contactId;
            }

            if (! empty($updates)) {
                $card->update($updates);
                Log::info('Card updated', ['card_id' => $card->id, 'updates' => $updates]);
            }
        } else {
            // If the conversation exists in Chatwoot but no card in the system, create it.
            // Determine target stage from conversation status (fallback to 'open')
            $status = data_get($conversation, 'status', 'open');
            $systemFunnel = $this->ensureSystemFunnel($accountId);
            $targetStage = $this->findStageByStatus($systemFunnel, $status);

            if ($targetStage) {
                $maxOrder = Card::query()
                    ->where('stage_id', $targetStage->id)
                    ->max('order') ?? 0;

                $card = Card::query()->create([
                    'conversation_id' => $conversationId,
                    'account_id' => $accountId,
                    'contact_id' => $contactId,
                    'stage_id' => $targetStage->id,
                    'order' => $maxOrder + 1,
                    'phone_number' => $phoneNumber,
                ]);

                Log::info('Card created from conversation_updated', ['card_id' => $card->id, 'conversation_id' => $conversationId]);
            } else {
                Log::warning('Unable to create card: target stage not found', ['conversation_id' => $conversationId, 'account_id' => $accountId]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleMessageCreated(array $data): JsonResponse
    {
        Log::info('Message created', [
            'conversation_id' => $data['conversation']['id'] ?? null,
            'message_type' => $data['message_type'] ?? null,
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function ensureSystemFunnel(int $accountId): Funnel
    {
        return Funnel::query()->firstOrCreate(
            ['account_id' => $accountId, 'is_system' => true],
            ['name' => 'Status Chatwoot', 'order' => 0, 'color' => '#3B82F6', 'is_public' => true],
        );
    }

    private function findStageByStatus(Funnel $funnel, string $status): ?Stage
    {
        $statusMap = [
            'open' => 'Aberto',
            'pending' => 'Pendente',
            'resolved' => 'Resolvido',
        ];

        $stageName = $statusMap[$status] ?? null;
        $chatwootStatus = $status;

        if (! $stageName) {
            return $funnel->stages()->first();
        }

        return Stage::query()->firstOrCreate(
            ['funnel_id' => $funnel->id, 'chatwoot_status' => $chatwootStatus],
            [
                'name' => $stageName,
                'order' => match ($status) {
                    'open' => 0,
                    'pending' => 1,
                    'resolved' => 2,
                    default => 99,
                },
                'color' => match ($status) {
                    'open' => '#22C55E',
                    'pending' => '#EAB308',
                    'resolved' => '#6B7280',
                    default => '#6B7280',
                },
            ],
        );
    }
}
