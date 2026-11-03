<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promoter;
use Illuminate\Http\Request;

class PromoterController extends Controller
{
    /**
     * snovi.fm/promoters/{username}: the profile. With a PIN set, the codes come only from unlock,
     * so the page and its link preview show the promoter without giving the codes away.
     */
    public function show(string $username)
    {
        $promoter = $this->findPromoter($username);

        if (!$promoter) {
            return $this->notFound();
        }

        return response()->json(['data' => $this->profile($promoter, !$promoter->hasPin())]);
    }

    /** The profile with the codes, for the PIN the admin gave the promoter. */
    public function unlock(Request $request, string $username)
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'max:16'],
        ]);

        $promoter = $this->findPromoter($username);

        if (!$promoter) {
            return $this->notFound();
        }

        if ($promoter->hasPin() && !hash_equals((string) $promoter->pin, $validated['pin'])) {
            return response()->json([
                'message' => 'Pogresan PIN.',
            ], 403);
        }

        return response()->json(['data' => $this->profile($promoter, true)]);
    }

    private function findPromoter(string $username): ?Promoter
    {
        return Promoter::query()
            ->where('username', strtolower($username))
            ->where('is_active', true)
            ->first();
    }

    private function notFound()
    {
        return response()->json([
            'message' => 'Promoter nije pronadjen.',
        ], 404);
    }

    private function profile(Promoter $promoter, bool $withCodes): array
    {
        $codes = $promoter->giftCodes()->orderBy('id')->get();
        $available = $codes->filter(fn ($giftCode) => !$giftCode->used && !$giftCode->isExpired())->values();

        return [
            ...$promoter->publicPayload(),
            'locked' => !$withCodes,
            'pin_length' => $promoter->hasPin() ? strlen((string) $promoter->pin) : null,
            'codes' => $withCodes
                ? $available->map(fn ($giftCode) => [
                    'code' => $giftCode->code,
                    'url' => $giftCode->promoLink(),
                    'expires_at' => optional($giftCode->expires_at)->toIso8601String(),
                ])
                : [],
            'codes_available' => $available->count(),
            'codes_total' => $codes->count(),
            'codes_used' => $codes->where('used', true)->count(),
        ];
    }
}
