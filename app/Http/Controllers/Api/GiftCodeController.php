<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftCodeController extends Controller
{
    private function giftCodePayload(GiftCode $giftCode, string $message)
    {
        return response()->json([
            'message' => $message,
            'data' => [
                'id' => $giftCode->id,
                'code' => $giftCode->code,
                'used' => $giftCode->used,
                'used_date' => $giftCode->used_date,
                'expires_at' => $giftCode->expires_at,
            ],
        ]);
    }

    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:12'],
        ]);

        $codeValue = trim($validated['code']);

        $giftCode = DB::transaction(function () use ($codeValue) {
            $giftCode = GiftCode::query()
                ->where('code', $codeValue)
                ->lockForUpdate()
                ->first();

            if (!$giftCode || $giftCode->used || ($giftCode->expires_at && $giftCode->expires_at->isPast())) {
                return $giftCode;
            }

            $giftCode->forceFill([
                'used' => true,
                'used_date' => now(),
            ])->save();

            return $giftCode;
        });

        if (!$giftCode) {
            return response()->json([
                'message' => 'Gift kod nije pronadjen.',
            ], 404);
        }

        if (!$giftCode->wasChanged('used') && $giftCode->used) {
            return response()->json([
                'message' => 'Gift kod je vec iskoristen.',
                'data' => [
                    'id' => $giftCode->id,
                    'code' => $giftCode->code,
                    'used' => $giftCode->used,
                    'used_date' => $giftCode->used_date,
                    'expires_at' => $giftCode->expires_at,
                ],
            ], 409);
        }

        if ($giftCode->expires_at && $giftCode->expires_at->isPast()) {
            return response()->json([
                'message' => 'Gift kod je istekao.',
            ], 410);
        }

        $expiresAt = $giftCode->expires_at ?: now()->addYear();

        return response()->json([
            'message' => 'Gift kod je iskoristen.',
            'data' => [
                'id' => $giftCode->id,
                'code' => $giftCode->code,
                'subscription' => 'customCode',
                'planLabel' => 'Godišnji plan',
                'ends' => $expiresAt->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
                'used' => $giftCode->used,
                'used_date' => $giftCode->used_date,
            ],
        ]);
    }

    public function revoke(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:12'],
        ]);

        $codeValue = trim($validated['code']);

        $giftCode = DB::transaction(function () use ($codeValue) {
            $giftCode = GiftCode::query()
                ->where('code', $codeValue)
                ->lockForUpdate()
                ->first();

            if (!$giftCode) {
                return null;
            }

            $giftCode->forceFill([
                'used' => false,
                'used_date' => null,
            ])->save();

            return $giftCode;
        });

        if (!$giftCode) {
            return response()->json([
                'message' => 'Gift kod nije pronadjen.',
            ], 404);
        }

        return $this->giftCodePayload($giftCode, 'Gift kod je povucen.');
    }
}
