<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PushTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'provider' => ['sometimes', 'string', Rule::in(['expo'])],
            'platform' => ['sometimes', 'nullable', 'string', 'max:32'],
            'notification_channel_id' => ['sometimes', 'nullable', 'string', 'max:128'],
            'sound' => ['sometimes', 'nullable', 'string', 'max:128'],
            'preferences' => ['sometimes', 'array'],
            'preferences.app' => ['sometimes', 'boolean'],
        ]);

        Log::info('[SNOVI][push] Push token received', [
            'token' => substr($validated['token'], 0, 12) . '...' . substr($validated['token'], -6),
            'platform' => $validated['platform'] ?? null,
            'notification_channel_id' => $validated['notification_channel_id'] ?? null,
            'preferences' => $validated['preferences'] ?? ['app' => true],
        ]);

        $token = PushToken::query()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'provider' => $validated['provider'] ?? 'expo',
                'platform' => $validated['platform'] ?? null,
                'notification_channel_id' => $validated['notification_channel_id'] ?? null,
                'sound' => $validated['sound'] ?? null,
                'preferences' => $validated['preferences'] ?? ['app' => true],
                'disabled_at' => null,
                'last_error' => null,
                'last_used_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Push token je sacuvan.',
            'data' => [
                'id' => $token->id,
                'provider' => $token->provider,
                'platform' => $token->platform,
                'last_used_at' => $token->last_used_at,
            ],
        ]);
    }
}
