<?php

namespace App\Services;

use App\Models\PushNotification;
use App\Models\PushToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushNotificationService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';
    private const CHUNK_SIZE = 100;

    public function sendBroadcast(PushNotification $notification): array
    {
        $tokens = PushToken::query()
            ->where('provider', 'expo')
            ->whereNull('disabled_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (PushToken $token) => ($token->preferences['app'] ?? true) !== false)
            ->values();

        $summary = [
            'recipient_count' => $tokens->count(),
            'success_count' => 0,
            'failure_count' => 0,
            'response' => [],
        ];

        if ($tokens->isEmpty()) {
            $notification->forceFill([
                'recipient_count' => 0,
                'sent_at' => now(),
                'response' => ['message' => 'No active push tokens.'],
            ])->save();

            return $summary;
        }

        foreach ($tokens->chunk(self::CHUNK_SIZE) as $chunk) {
            $chunkSummary = $this->sendChunk($chunk, $notification);
            $summary['success_count'] += $chunkSummary['success_count'];
            $summary['failure_count'] += $chunkSummary['failure_count'];
            $summary['response'][] = $chunkSummary['response'];
        }

        $notification->forceFill([
            'recipient_count' => $summary['recipient_count'],
            'success_count' => $summary['success_count'],
            'failure_count' => $summary['failure_count'],
            'response' => $summary['response'],
            'sent_at' => now(),
        ])->save();

        return $summary;
    }

    private function sendChunk(Collection $tokens, PushNotification $notification): array
    {
        $messages = $tokens
            ->map(fn (PushToken $token) => $this->messageFor($token, $notification))
            ->values()
            ->all();

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->post(self::EXPO_PUSH_URL, $messages);

            if (!$response->successful()) {
                $error = $response->body();
                $tokens->each(fn (PushToken $token) => $token->forceFill(['last_error' => $error])->save());

                Log::warning('[SNOVI][push] Expo push request failed', [
                    'notification_id' => $notification->id,
                    'status' => $response->status(),
                    'body' => $error,
                ]);

                return [
                    'success_count' => 0,
                    'failure_count' => $tokens->count(),
                    'response' => [
                        'status' => $response->status(),
                        'body' => $error,
                    ],
                ];
            }

            $results = $response->json('data') ?? [];
            $counts = $this->applyExpoResults($tokens->values(), $results);

            return [
                'success_count' => $counts['success_count'],
                'failure_count' => $counts['failure_count'],
                'response' => $results,
            ];
        } catch (\Throwable $error) {
            $tokens->each(fn (PushToken $token) => $token->forceFill(['last_error' => $error->getMessage()])->save());
            Log::warning('[SNOVI][push] Expo push send failed', [
                'notification_id' => $notification->id,
                'message' => $error->getMessage(),
            ]);

            return [
                'success_count' => 0,
                'failure_count' => $tokens->count(),
                'response' => ['error' => $error->getMessage()],
            ];
        }
    }

    private function messageFor(PushToken $token, PushNotification $notification): array
    {
        return array_filter([
            'to' => $token->token,
            'title' => $notification->body,
            'body' => $notification->description,
            'sound' => $token->sound ?: 'default',
            'channelId' => $token->notification_channel_id ?: 'snovi-notifications',
            'priority' => 'default',
            'data' => [
                'type' => 'broadcast',
                'notificationId' => $notification->id,
            ] + ($notification->data ?: []),
        ], fn ($value) => $value !== null);
    }

    private function applyExpoResults(Collection $tokens, array $results): array
    {
        $successCount = 0;
        $failureCount = 0;

        foreach ($tokens as $index => $token) {
            $result = $results[$index] ?? null;

            if (($result['status'] ?? null) === 'ok') {
                $successCount++;
                $token->forceFill([
                    'last_used_at' => now(),
                    'last_error' => null,
                ])->save();
                continue;
            }

            $failureCount++;
            $error = $result['message'] ?? $result['details']['error'] ?? 'Expo push error';
            $updates = ['last_error' => $error];

            if (($result['details']['error'] ?? null) === 'DeviceNotRegistered') {
                $updates['disabled_at'] = now();
            }

            $token->forceFill($updates)->save();
        }

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ];
    }
}
