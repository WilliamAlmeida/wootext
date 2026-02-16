<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WahaWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = $payload['event'] ?? 'unknown';
        $session = $payload['session'] ?? 'unknown';

        Log::info('WAHA webhook received', [
            'event' => $event,
            'session' => $session,
        ]);

        switch ($event) {
            case 'session.status':
                $this->handleSessionStatus($session, $payload);
                break;
            case 'message':
            case 'message.any':
                $this->handleMessage($session, $payload);
                break;
            default:
                Log::debug('Unhandled WAHA event', ['event' => $event]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleSessionStatus(string $session, array $payload): void
    {
        $status = $payload['payload']['status'] ?? 'unknown';
        Log::info("WAHA Session status update for {$session}: {$status}");
    }

    private function handleMessage(string $session, array $payload): void
    {
        Log::debug("WAHA Message received for {$session}");
        // Implement Kanban integration logic here
    }
}
