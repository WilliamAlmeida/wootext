<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledMessageJob;
use App\Models\ScheduledMessage;
use Illuminate\Console\Command;

class DispatchScheduledMessagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduled-messages:dispatch {--limit=20 : Quantidade máxima de mensagens por execução}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despacha mensagens agendadas pendentes para a fila';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = max((int) $this->option('limit'), 1);

        $messages = ScheduledMessage::query()
            ->select(['id'])
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        if ($messages->isEmpty()) {
            $this->info('Nenhuma mensagem pendente para enfileirar.');

            return self::SUCCESS;
        }

        $dispatched = 0;

        foreach ($messages as $message) {
            $updated = ScheduledMessage::query()
                ->whereKey($message->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'queued',
                    'error_message' => null,
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                continue;
            }

            SendScheduledMessageJob::dispatch($message->id);
            $dispatched++;
        }

        $this->info("{$dispatched} mensagem(ns) enfileirada(s) com sucesso.");

        return self::SUCCESS;
    }
}
