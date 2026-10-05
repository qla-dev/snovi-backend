<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use App\Services\RevenueCatSubscribers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class GiftCodeController extends Controller
{
    private function giftCodePayload(GiftCode $giftCode, string $message)
    {
        return response()->json([
            'message' => $message,
            'data' => [
                'id' => $giftCode->id,
                'code' => $giftCode->code,
                'email' => $giftCode->email,
                'used' => $giftCode->used,
                'used_date' => $giftCode->used_date,
                'expires_at' => $giftCode->expires_at,
            ],
        ]);
    }

    /**
     * A code issued for a web purchase lasts as long as the subscription: after a renewal its end
     * moves to the new expiry from RevenueCat. A failed lookup keeps the stored date.
     */
    private function refreshWebExpiry(GiftCode $giftCode): GiftCode
    {
        if (!$giftCode->isWebPurchase() || !RevenueCatSubscribers::configured()) {
            return $giftCode;
        }

        try {
            $subscription = app(RevenueCatSubscribers::class)->activeWebSubscription($giftCode->rc_app_user_id);
        } catch (Throwable $exception) {
            Log::warning("Gift code {$giftCode->id} expiry not refreshed: {$exception->getMessage()}");
            return $giftCode;
        }

        if ($subscription && (!$giftCode->expires_at || $subscription['expires_at']->gt($giftCode->expires_at))) {
            $giftCode->forceFill(['expires_at' => $subscription['expires_at']])->save();
        }

        return $giftCode;
    }

    private function findCode(string $codeValue): ?GiftCode
    {
        $giftCode = GiftCode::query()->where('code', $codeValue)->first();

        return $giftCode ? $this->refreshWebExpiry($giftCode) : null;
    }

    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:12', 'regex:/^[A-Z0-9]+$/i'],
        ]);

        $codeValue = strtoupper(trim($validated['code']));
        $this->findCode($codeValue);

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
                    'email' => $giftCode->email,
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
                'email' => $giftCode->email,
                'subscription' => 'customCode',
                'plan' => $giftCode->plan,
                'planLabel' => $giftCode->planLabel(),
                'ends' => $expiresAt->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
                'used' => $giftCode->used,
                'used_date' => $giftCode->used_date,
            ],
        ]);
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:12', 'regex:/^[A-Z0-9]+$/i'],
        ]);

        $giftCode = $this->findCode(strtoupper(trim($validated['code'])));

        if (!$giftCode) {
            return response()->json([
                'message' => 'Gift kod nije pronadjen.',
            ], 404);
        }

        if ($giftCode->used) {
            return response()->json([
                'message' => 'Gift kod je vec iskoristen.',
            ], 409);
        }

        if ($giftCode->expires_at && $giftCode->expires_at->isPast()) {
            return response()->json([
                'message' => 'Gift kod je istekao.',
            ], 410);
        }

        return response()->json([
            'message' => 'Gift kod je validan.',
            'data' => [
                'id' => $giftCode->id,
                'code' => $giftCode->code,
                'valid' => true,
                'plan' => $giftCode->plan,
                'planLabel' => $giftCode->planLabel(),
                'expires_at' => optional($giftCode->expires_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Current end of an already activated code, without using it up. The app calls this when its
     * stored end has passed, so a renewed monthly web subscription keeps working.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:12', 'regex:/^[A-Z0-9]+$/i'],
        ]);

        $giftCode = $this->findCode(strtoupper(trim($validated['code'])));

        if (!$giftCode) {
            return response()->json([
                'message' => 'Gift kod nije pronadjen.',
            ], 404);
        }

        $active = !$giftCode->expires_at || $giftCode->expires_at->isFuture();

        return response()->json([
            'message' => $active ? 'Gift kod je aktivan.' : 'Gift kod je istekao.',
            'data' => [
                'code' => $giftCode->code,
                'active' => $active,
                'used' => $giftCode->used,
                'plan' => $giftCode->plan,
                'planLabel' => $giftCode->planLabel(),
                'ends' => optional($giftCode->expires_at)->toIso8601String(),
                'expires_at' => optional($giftCode->expires_at)->toIso8601String(),
            ],
        ]);
    }

    public function email(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:12', 'regex:/^[A-Z0-9]+$/i'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        $codeValue = strtoupper(trim($validated['code']));
        $email = strtolower(trim($validated['email']));

        $giftCode = DB::transaction(function () use ($codeValue, $email) {
            $giftCode = GiftCode::query()
                ->where('code', $codeValue)
                ->lockForUpdate()
                ->first();

            if (!$giftCode || ($giftCode->expires_at && $giftCode->expires_at->isPast())) {
                return null;
            }

            $giftCode->forceFill([
                'email' => $email,
                'used' => true,
                'used_date' => $giftCode->used_date ?: now(),
            ])->save();

            return $giftCode;
        });

        if (!$giftCode) {
            return response()->json([
                'message' => 'Gift kod nije pronadjen ili je istekao.',
            ], 404);
        }

        $expiresAt = $giftCode->expires_at ?: now()->addYear();

        return response()->json([
            'message' => 'Gift kod je aktiviran.',
            'data' => [
                'id' => $giftCode->id,
                'code' => $giftCode->code,
                'email' => $giftCode->email,
                'subscription' => 'customCode',
                'plan' => $giftCode->plan,
                'planLabel' => $giftCode->planLabel(),
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
            'code' => ['required', 'string', 'size:12', 'regex:/^[A-Z0-9]+$/i'],
        ]);

        $codeValue = strtoupper(trim($validated['code']));

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
