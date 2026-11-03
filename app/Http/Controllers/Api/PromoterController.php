<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promoter;

class PromoterController extends Controller
{
    /** snovi.fm/promoters/{username}: the profile with the codes that can still be activated. */
    public function show(string $username)
    {
        $promoter = Promoter::query()
            ->where('username', strtolower($username))
            ->where('is_active', true)
            ->first();

        if (!$promoter) {
            return response()->json([
                'message' => 'Promoter nije pronadjen.',
            ], 404);
        }

        $codes = $promoter->giftCodes()->orderBy('id')->get();
        $available = $codes->filter(fn ($giftCode) => !$giftCode->used && !$giftCode->isExpired());

        return response()->json([
            'data' => [
                ...$promoter->publicPayload(),
                'codes' => $available->values()->map(fn ($giftCode) => [
                    'code' => $giftCode->code,
                    'url' => $giftCode->promoLink(),
                    'expires_at' => optional($giftCode->expires_at)->toIso8601String(),
                ]),
                'codes_total' => $codes->count(),
                'codes_used' => $codes->where('used', true)->count(),
            ],
        ]);
    }
}
