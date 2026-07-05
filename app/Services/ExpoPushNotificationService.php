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

        Log::info('[SNOVI][push] Starting Expo broadcast', [
            'notification_id' => $notification->id,
            'recipient_count' => $tokens->count(),
            'tokens' => $tokens->map(fn (PushToken $token) => [
                'id' => $token->id,
                'token' => $this->maskToken($token->token),
                'platform' => $token->platform,
                'disabled_at' => $token->disabled_at?->toIso8601String(),
                'last_error' => $token->last_error,
            ])->values()->all(),
        ]);

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

    private function sendChunk(Collection $tokens, PushNotification $notification, bool $allowExperienceSplit = true): array
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
                $json = $response->json();
                if ($allowExperienceSplit && is_array($json) && $this->hasMixedExperienceError($json)) {
                    Log::warning('[SNOVI][push] Expo push request has mixed projects; splitting batch', [
                        'notification_id' => $notification->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return $this->sendMixedExperienceChunks($tokens, $notification, $json);
                }

                $error = $response->body();
                $tokens->each(fn (PushToken $token) => $token->forceFill(['last_error' => $error])->save());

                Log::warning('[SNOVI][push] Expo push request failed', [
                    'notification_id' => $notification->id,
                    'status' => $response->status(),
                    'body' => $error,
                    'recipient_count' => $tokens->count(),
                    'recipients' => $tokens->map(fn (PushToken $token) => [
                        'id' => $token->id,
                        'token' => $this->maskToken($token->token),
                        'platform' => $token->platform,
                        'disabled_at' => $token->disabled_at?->toIso8601String(),
                        'last_error' => $token->last_error,
                    ])->values()->all(),
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
            $counts = $this->applyExpoResults($tokens->values(), $results, $notification->id);

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
                'recipient_count' => $tokens->count(),
                'recipients' => $tokens->map(fn (PushToken $token) => [
                    'id' => $token->id,
                    'token' => $this->maskToken($token->token),
                    'platform' => $token->platform,
                    'disabled_at' => $token->disabled_at?->toIso8601String(),
                    'last_error' => $token->last_error,
                ])->values()->all(),
            ]);

            return [
                'success_count' => 0,
                'failure_count' => $tokens->count(),
                'response' => ['error' => $error->getMessage()],
            ];
        }
    }

    private function sendMixedExperienceChunks(Collection $tokens, PushNotification $notification, array $response): array
    {
        $groups = $this->groupsFromMixedExperienceResponse($tokens, $response);

        if ($groups->isEmpty()) {
            $groups = $tokens->map(fn (PushToken $token) => collect([$token]));
        }

        $summary = [
            'success_count' => 0,
            'failure_count' => 0,
            'response' => [
                'split_reason' => 'PUSH_TOO_MANY_EXPERIENCE_IDS',
                'groups' => [],
            ],
        ];

        foreach ($groups as $key => $group) {
            $group = $group->values();
            if ($group->isEmpty()) {
                continue;
            }

            $groupSummary = $this->sendChunk($group, $notification, false);
            $summary['success_count'] += $groupSummary['success_count'];
            $summary['failure_count'] += $groupSummary['failure_count'];
            $summary['response']['groups'][] = [
                'group' => is_string($key) ? $key : 'single-token',
                'recipient_count' => $group->count(),
                'response' => $groupSummary['response'],
            ];
        }

        return $summary;
    }

    private function hasMixedExperienceError(array $response): bool
    {
        foreach ($response['errors'] ?? [] as $error) {
            if (($error['code'] ?? null) === 'PUSH_TOO_MANY_EXPERIENCE_IDS') {
                return true;
            }
        }

        return false;
    }

    private function groupsFromMixedExperienceResponse(Collection $tokens, array $response): Collection
    {
        $tokenByValue = $tokens->keyBy('token');
        $usedTokenValues = [];
        $groups = collect();

        foreach ($response['errors'] ?? [] as $error) {
            if (($error['code'] ?? null) !== 'PUSH_TOO_MANY_EXPERIENCE_IDS') {
                continue;
            }

            foreach (($error['details'] ?? []) as $experienceId => $tokenValues) {
                if (!is_array($tokenValues)) {
                    continue;
                }

                $group = collect($tokenValues)
                    ->map(fn ($tokenValue) => is_string($tokenValue) ? $tokenByValue->get($tokenValue) : null)
                    ->filter()
                    ->values();

                if ($group->isEmpty()) {
                    continue;
                }

                foreach ($group as $token) {
                    $usedTokenValues[$token->token] = true;
                }

                $groups->put((string) $experienceId, $group);
            }
        }

        $tokens
            ->reject(fn (PushToken $token) => isset($usedTokenValues[$token->token]))
            ->each(fn (PushToken $token) => $groups->push(collect([$token])));

        return $groups;
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

    private function maskToken(?string $token): ?string
    {
        if (!$token || strlen($token) <= 8) {
            return $token;
        }

        return substr($token, 0, 4) . '...' . substr($token, -4);
    }

    private function applyExpoResults(Collection $tokens, array $results, ?int $notificationId = null): array
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

            Log::warning('[SNOVI][push] Expo recipient failed', [
                'notification_id' => $notificationId,
                'token_id' => $token->id,
                'token' => $this->maskToken($token->token),
                'platform' => $token->platform,
                'result' => $result,
                'error' => $error,
                'updates' => $updates,
            ]);

            $token->forceFill($updates)->save();
        }

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ];
    }
}
