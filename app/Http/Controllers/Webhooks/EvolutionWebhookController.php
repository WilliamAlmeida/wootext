<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvolutionWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $event = $request->input('event');
        $instance = $request->input('instance');
        $data = $request->all();

        Log::info('Evolution webhook received', [
            'event' => $event,
            'instance' => $instance,
        ]);

        match ($event) {
            'QRCODE_UPDATED' => $this->handleQrCodeUpdated($instance, $data),
            'CONNECTION_UPDATE' => $this->handleConnectionUpdate($instance, $data),
            'MESSAGES_UPDATE' => $this->handleMessagesUpdate($instance, $data),
            'MESSAGES_DELETE' => $this->handleMessagesDelete($instance, $data),
            'SEND_MESSAGE' => $this->handleSendMessage($instance, $data),
            'CALL' => $this->handleCall($instance, $data),
            default => Log::debug('Unhandled Evolution event', ['event' => $event]),
        };

        return response()->json(['status' => 'ok']);
    }

    private function handleQrCodeUpdated(string $instance, array $data): void
    {
        Log::info("QR code updated for instance: {$instance}");
        // With WebSocket this would emit to frontend.
        // With wire:poll, the Connections page polls for QR status.
    }

    private function handleConnectionUpdate(string $instance, array $data): void
    {
        $state = $data['data']['state'] ?? 'unknown';
        Log::info("Connection update for {$instance}: {$state}");
    }

    private function handleMessagesUpdate(string $instance, array $data): void
    {
        Log::debug("Messages update for {$instance}");
    }

    private function handleMessagesDelete(string $instance, array $data): void
    {
        Log::debug("Messages delete for {$instance}");
    }

    private function handleSendMessage(string $instance, array $data): void
    {
        Log::debug("Send message confirmed for {$instance}");
    }

    private function handleCall(string $instance, array $data): void
    {
        Log::debug("Call event for {$instance}");
    }
}
