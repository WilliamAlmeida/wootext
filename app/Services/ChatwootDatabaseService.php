<?php

namespace App\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class ChatwootDatabaseService
{
    private function db(): ConnectionInterface
    {
        return DB::connection('chatwoot');
    }

    /**
     * Busca o identifier de um channel_api pelo channel_id ou inbox_id
     */
    public function getChannelApiIdentifier(int $channelId): ?string
    {
        return $this->db()
            ->table('channel_api')
            ->where('id', $channelId)
            ->value('identifier');
    }

    /**
     * Busca o channel_id de uma inbox pelo ID
     */
    public function getChannelIdByInboxId(int $inboxId): ?int
    {
        return $this->db()
            ->table('inboxes')
            ->where('id', $inboxId)
            ->value('channel_id');
    }

    /**
     * Atualiza a webhook_url de um channel_api
     */
    public function updateChannelApiWebhook(int $channelId, string $webhookUrl): bool
    {
        $updated = $this->db()
            ->table('channel_api')
            ->where('id', $channelId)
            ->update([
                'webhook_url' => $webhookUrl,
                'updated_at' => now(),
            ]);

        return $updated > 0;
    }

    /**
     * Busca inbox pelo nome e account_id
     */
    public function getInboxByName(int $accountId, string $name): ?object
    {
        return $this->db()
            ->table('inboxes as i')
            ->leftJoin('channel_api as c', function ($join) {
                $join->on('c.id', '=', 'i.channel_id')
                    ->where('i.channel_type', '=', 'Channel::Api');
            })
            ->where('i.account_id', $accountId)
            ->whereRaw('LOWER(i.name) = LOWER(?)', [$name])
            ->select('i.*', 'c.identifier as channel_identifier')
            ->first();
    }
}
