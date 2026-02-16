<?php

namespace App\Services;

use App\Models\ScheduledMessage;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ChatwootService
{
    private string $baseUrl;

    private string $apiToken;

    private int $accountId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.chatwoot.url', ''), '/');
        $this->apiToken = config('services.chatwoot.api_token', '');
        $this->accountId = (int) config('services.chatwoot.account_id');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['api_access_token' => $this->apiToken])
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Build a request client with optional token overrides.
     */
    private function clientWithAuth(?string $apiToken = null, array $headers = []): PendingRequest
    {
        $resolvedToken = $apiToken ?? $this->apiToken;

        return Http::baseUrl($this->baseUrl)
            ->withHeaders(array_filter([
                'api_access_token' => $resolvedToken,
                ...$headers,
            ], fn ($value) => $value !== null && $value !== ''))
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Authenticate with Chatwoot and retrieve tokens.
     *
     * @param  string  $email
     * @param  string  $password
     * @return array<string, mixed>
     */
    public function authenticate(string $email, string $password): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->post('/auth/sign_in', [
                'email' => $email,
                'password' => $password,
            ]);

        if($response->json('data')) {
            $response = $response->json();
            $response['success'] = true;
            return $response;
        }

        return $response->json() ?? [];
    }

    /**
     * Get all conversations with optional filters.
     *
     * @param  array{status?: string, page?: int, assignee_type?: string}  $params
     * @return array<string, mixed>
     */
    public function getConversations(array $params = []): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/conversations", $params);

        return $response->json() ?? [];
    }

    /**
     * Get a single conversation by ID.
     *
     * @return array<string, mixed>
     */
    public function getConversation(int $conversationId): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}");

        return $response->json() ?? [];
    }

    /**
     * Update conversation status.
     */
    public function updateConversationStatus(int $conversationId, string $status): array
    {
        $response = $this->client()
            ->post("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/toggle_status", [
                'status' => $status,
            ]);

        return $response->json() ?? [];
    }

    /**
     * Send a message to a conversation.
     */
    public function sendMessage(int $conversationId, string $content, string $messageType = 'outgoing', ?string $attachmentPath = null): array
    {
        $request = $this->client();

        if ($attachmentPath) {
            $request = $request->attach('attachments[]', file_get_contents($attachmentPath), basename($attachmentPath));

            $response = $request->post("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages", [
                'content' => $content,
                'message_type' => $messageType,
            ]);
        } else {
            $response = $request->post("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages", [
                'content' => $content,
                'message_type' => $messageType,
            ]);
        }

        return $response->json() ?? [];
    }

    /**
     * Send one queued scheduled message.
     */
    public function sendScheduledMessage(ScheduledMessage $scheduledMessage, ?string $attachmentPath = null): bool
    {
        $jwtHeaders = $this->resolveJwtHeaders($scheduledMessage);
        $apiToken = $scheduledMessage->api_token ?: $this->apiToken;

        if ($apiToken === '' && $jwtHeaders === []) {
            return false;
        }

        $request = $this->clientWithAuth($apiToken, $jwtHeaders);

        if ($attachmentPath) {
            $request = $request->attach('attachments[]', file_get_contents($attachmentPath), basename($attachmentPath));
        }

        $response = $request->post("/api/v1/accounts/{$scheduledMessage->account_id}/conversations/{$scheduledMessage->conversation_id}/messages", [
            'content' => $scheduledMessage->message,
            'message_type' => 'outgoing',
        ]);

        return $response->successful();
    }

    /**
     * Resolve JWT headers from scheduled message credentials.
     *
     * @return array<string, string>
     */
    private function resolveJwtHeaders(ScheduledMessage $scheduledMessage): array
    {
        if (
            ! $scheduledMessage->jwt_access_token
            || ! $scheduledMessage->jwt_client
            || ! $scheduledMessage->jwt_uid
            || ! $scheduledMessage->jwt_expiry
            || ! $scheduledMessage->jwt_token_type
        ) {
            return [];
        }

        return [
            'access-token' => $scheduledMessage->jwt_access_token,
            'client' => $scheduledMessage->jwt_client,
            'uid' => $scheduledMessage->jwt_uid,
            'expiry' => $scheduledMessage->jwt_expiry,
            'token-type' => $scheduledMessage->jwt_token_type,
        ];
    }

    /**
     * Get conversation messages.
     */
    public function getConversationMessages(int $conversationId): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages");

        return $response->json() ?? [];
    }

    /**
     * List agents for the account.
     */
    public function getAgents(): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/agents");

        return $response->json() ?? [];
    }

    /**
     * Assign agent to a conversation.
     */
    public function assignAgent(int $conversationId, int $agentId): array
    {
        $response = $this->client()
            ->post("/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/assignments", [
                'assignee_id' => $agentId,
            ]);

        return $response->json() ?? [];
    }

    /**
     * List inboxes.
     */
    public function getInboxes(): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/inboxes");

        return $response->json()['payload'] ?? [];
    }

    /**
     * Create an API inbox.
     */
    public function createInbox(string $name, ?string $webhookUrl = null): array
    {
        $data = [
            'name' => $name,
            'channel' => [
                'type' => 'api',
                'webhook_url' => $webhookUrl ?? '',
            ],
        ];

        $response = $this->client()
            ->post("/api/v1/accounts/{$this->accountId}/inboxes", $data);

        return $response->json() ?? [];
    }

    /**
     * Delete an inbox.
     */
    public function deleteInbox(int $inboxId): bool
    {
        $response = $this->client()
            ->delete("/api/v1/accounts/{$this->accountId}/inboxes/{$inboxId}");

        return $response->successful();
    }

    /**
     * List account labels.
     */
    public function getLabels(): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/labels");

        return $response->json()['payload'] ?? [];
    }

    /**
     * Create a webhook subscription.
     */
    public function createWebhook(string $url, array $subscriptions = []): array
    {
        if (empty($subscriptions)) {
            $subscriptions = [
                'conversation_created',
                'conversation_status_changed',
                'conversation_updated',
                'message_created',
            ];
        }

        $response = $this->client()
            ->post("/api/v1/accounts/{$this->accountId}/webhooks", [
                'webhook' => [
                    'url' => $url,
                    'subscriptions' => $subscriptions,
                ],
            ]);

        return $response->json() ?? [];
    }

    /**
     * List registered webhooks.
     */
    public function listWebhooks(): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/webhooks");

        return $response->json()['payload'] ?? [];
    }

    /**
     * Search contacts by query.
     */
    public function searchContacts(string $query): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/contacts/search", [
                'q' => $query,
            ]);

        return $response->json()['payload'] ?? [];
    }

    /**
     * Get account ID.
     */
    public function getAccountId(): int
    {
        return $this->accountId;
    }

    /**
     * Get notes for a contact.
     */
    public function getNotes(int $contactId): array
    {
        $response = $this->client()
            ->get("/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/notes");

        return $response->json() ?? [];
    }

    /**
     * Create a note for a contact.
     */
    public function createNote(int $contactId, string $content): array
    {
        $response = $this->client()
            ->post("/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/notes", [
                'content' => $content,
            ]);

        return $response->json() ?? [];
    }

    /**
     * Delete a note from a contact.
     */
    public function deleteNote(int $contactId, int $noteId): bool
    {
        $response = $this->client()
            ->delete("/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/notes/{$noteId}");

        return $response->successful();
    }

    /**
     * Map Chatwoot priority number to label.
     */
    public static function mapPriority(?int $priority): string
    {
        return match ($priority) {
            0 => 'none',
            1 => 'low',
            2 => 'medium',
            3 => 'high',
            4 => 'urgent',
            default => 'none',
        };
    }
}
