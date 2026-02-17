<?php

namespace App\Livewire\Pages\Kanban3\Concerns;

use App\Models\Card;
use App\Models\Funnel;
use App\Models\Stage;
use App\Services\ChatwootService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

trait LoadsKanbanBoardData
{
    #[Computed]
    public function funnels()
    {
        $accountId = $this->getAccountId();
        $cacheKey = $this->boardCacheKey($accountId, 'funnels');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($accountId) {
            return Funnel::query()
                ->where('account_id', $accountId)
                ->orderBy('order')
                ->get();
        });
    }

    #[Computed]
    public function activeFunnel(): ?Funnel
    {
        return Funnel::with(['stages' => static fn ($query) => $query->orderBy('order')])
            ->find($this->activeFunnelId);
    }

    #[Computed]
    public function stages()
    {
        if (! $this->activeFunnel) {
            return collect([]);
        }

        $accountId = $this->getAccountId();
        $cacheKey = $this->boardCacheKey($accountId, "stages:{$this->activeFunnel->id}");

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->activeFunnel->stages()
                ->withCount('cards')
                ->orderBy('order')
                ->get();
        });
    }

    #[Computed]
    public function stageCards(): array
    {
        if (! $this->activeFunnel) {
            return [];
        }

        $accountId = $this->getAccountId();
        $searchKey = md5(trim($this->search));
        $cacheKey = $this->boardCacheKey($accountId, "stage-cards:{$this->activeFunnel->id}:{$searchKey}");

        return Cache::remember($cacheKey, now()->addSeconds(12), function () use ($accountId) {
            $isSystemFunnel = (bool) $this->activeFunnel->is_system;
            $conversationMap = $this->getConversationMap($accountId);

            $cardsQuery = Card::query()
                ->where('account_id', $accountId);

            if (! $isSystemFunnel) {
                $cardsQuery->whereIn('stage_id', $this->activeFunnel->stages()->pluck('id'));
            }

            $cards = $cardsQuery
                ->orderBy('order')
                ->get();

            $statusStageMap = $this->activeFunnel->stages()
                ->whereNotNull('chatwoot_status')
                ->pluck('id', 'chatwoot_status')
                ->all();

            $fallbackStageId = $this->activeFunnel->stages()->value('id');
            $search = trim($this->search);
            $enriched = [];

            foreach ($cards as $card) {
                if (! $card instanceof Card) {
                    continue;
                }

                $chatwootData = $conversationMap[$card->conversation_id] ?? null;

                if (! empty($conversationMap) && ! $chatwootData) {
                    continue;
                }

                $stageId = $card->stage_id;

                if ($isSystemFunnel) {
                    $status = $chatwootData['status'] ?? 'open';
                    $stageId = $statusStageMap[$status] ?? $fallbackStageId;

                    if (! $stageId) {
                        continue;
                    }
                }

                $mapped = $this->enrichCard($card, $chatwootData, $stageId);

                if ($search !== '' && ! $this->cardMatchesSearch($mapped, $search)) {
                    continue;
                }

                $enriched[$stageId][] = $mapped;
            }

            foreach ($enriched as $stageId => $stageCards) {
                usort($enriched[$stageId], static fn (array $left, array $right) => ($left['order'] ?? 0) <=> ($right['order'] ?? 0));
            }

            return $enriched;
        });
    }

    private function enrichCard(Card $card, ?array $chatwootData, int $stageId): array
    {
        $contactName = $chatwootData['contact_name'] ?? null;
        $contactPhone = $chatwootData['contact_phone'] ?? null;
        $displayName = $card->custom_name
            ?: $contactName
            ?: $contactPhone
            ?: $card->phone_number
            ?: 'Conversa #'.$card->conversation_id;

        return [
            'id' => $card->id,
            'conversation_id' => $card->conversation_id,
            'stage_id' => $stageId,
            'order' => $card->order,
            'custom_name' => $card->custom_name,
            'display_name' => $displayName,
            'phone_number' => $card->phone_number ?? $contactPhone,
            'contact_name' => $contactName,
            'assignee' => $chatwootData['assignee'] ?? null,
            'chatwoot_status' => $chatwootData['status'] ?? null,
            'unread_count' => $chatwootData['unread_count'] ?? null,
            'labels' => $chatwootData['labels'] ?? [],
            'priority' => $chatwootData['priority'] ?? null,
            'inbox_id' => $chatwootData['inbox_id'] ?? null,
            'email' => $chatwootData['email'] ?? null,
            'image_url' => $chatwootData['image_url'] ?? null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getConversationMap(int $accountId): array
    {
        $cacheKey = "kanban:chatwoot:conversations:{$accountId}";

        return Cache::remember($cacheKey, now()->addSeconds(10), function () use ($accountId) {
            try {
                $response = app(ChatwootService::class)->getConversations(['status' => 'all']);
            } catch (\Throwable $exception) {
                Log::warning('Failed to fetch Chatwoot conversations', [
                    'error' => $exception->getMessage(),
                ]);

                return [];
            }

            $conversations = $this->normalizeChatwootConversations($response);
            $map = [];

            foreach ($conversations as $conversation) {
                $conversationId = $conversation['id'] ?? null;

                if (! $conversationId) {
                    continue;
                }

                $contact = data_get($conversation, 'meta.sender');
                $priority = $conversation['priority'] ?? null;

                $map[$conversationId] = [
                    'contact_name' => $contact['name'] ?? null,
                    'contact_phone' => $contact['phone_number'] ?? null,
                    'assignee' => data_get($conversation, 'meta.assignee.name')
                        ?? data_get($conversation, 'meta.assignee'),
                    'status' => $conversation['status'] ?? null,
                    'unread_count' => $conversation['unread_count'] ?? null,
                    'labels' => $conversation['labels'] ?? [],
                    'inbox_id' => $conversation['inbox_id'] ?? null,
                    'priority' => is_int($priority) ? ChatwootService::mapPriority($priority) : $priority,
                    'email' => $contact['email'] ?? null,
                    'image_url' => $contact['thumbnail'] ?? null,
                ];
            }

            return $map;
        });
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, array<string, mixed>>
     */
    private function normalizeChatwootConversations(array $response): array
    {
        $payload = data_get($response, 'data.payload');

        if (is_array($payload)) {
            return $payload;
        }

        $payload = data_get($response, 'payload');

        if (is_array($payload)) {
            return $payload;
        }

        $payload = data_get($response, 'data');

        if (is_array($payload) && array_is_list($payload)) {
            return $payload;
        }

        return array_is_list($response) ? $response : [];
    }

    /**
     * @param  array<string, mixed>  $card
     */
    private function cardMatchesSearch(array $card, string $search): bool
    {
        $needle = Str::lower($search);
        $haystacks = [
            $card['display_name'] ?? '',
            $card['custom_name'] ?? '',
            $card['phone_number'] ?? '',
            $card['contact_name'] ?? '',
            (string) ($card['conversation_id'] ?? ''),
        ];

        foreach ($haystacks as $haystack) {
            if ($haystack !== '' && Str::contains(Str::lower($haystack), $needle)) {
                return true;
            }
        }

        return false;
    }

    private function ensureSystemFunnel(int $accountId): Funnel
    {
        $funnel = Funnel::firstOrCreate(
            ['account_id' => $accountId, 'is_system' => true],
            ['name' => 'Status Chatwoot', 'order' => 0, 'color' => '#3B82F6', 'is_public' => true],
        );

        $systemStages = [
            ['name' => 'Aberto', 'chatwoot_status' => 'open', 'order' => 0, 'color' => '#22C55E'],
            ['name' => 'Pendente', 'chatwoot_status' => 'pending', 'order' => 1, 'color' => '#EAB308'],
            ['name' => 'Resolvido', 'chatwoot_status' => 'resolved', 'order' => 2, 'color' => '#6B7280'],
        ];

        foreach ($systemStages as $stageData) {
            Stage::firstOrCreate(
                ['funnel_id' => $funnel->id, 'chatwoot_status' => $stageData['chatwoot_status']],
                $stageData,
            );
        }

        return $funnel;
    }

    private function boardCacheVersion(int $accountId): int
    {
        return (int) Cache::get("kanban:board:version:{$accountId}", 1);
    }

    private function boardCacheKey(int $accountId, string $segment): string
    {
        $version = $this->boardCacheVersion($accountId);

        return "kanban:board:v{$version}:{$accountId}:{$segment}";
    }

    private function getAccountId()
    {
        return config('services.chatwoot.account_id');
    }
}
