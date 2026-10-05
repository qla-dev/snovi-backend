<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WebPurchaseVoucher;
use App\Models\GiftCode;
use App\Services\RevenueCatSubscribers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Called by snovi.fm/pretplata right after a RevenueCat Billing purchase. The purchase is confirmed
 * with RevenueCat (RevenueCatSubscribers), then the buyer gets a voucher code valid until the end of
 * the paid period: in the response (shown on the page with a QR code) and by email. Calling it again
 * for the same purchase returns the same code, so a page refresh never issues a second one.
 */
class WebPurchaseController extends Controller
{
    public function claim(Request $request, RevenueCatSubscribers $subscribers): JsonResponse
    {
        $data = $request->validate([
            'appUserId' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));

        try {
            $subscription = $subscribers->activeWebSubscription($data['appUserId']);
        } catch (Throwable $exception) {
            return response()->json(['message' => 'Kupovinu trenutno ne možemo provjeriti. Pokušajte ponovo za minut.'], 503);
        }

        if (!$subscription) {
            return response()->json(['message' => 'Aktivna web pretplata za ovu kupovinu nije pronađena.'], 404);
        }

        $giftCode = DB::transaction(function () use ($data, $email, $subscription) {
            $giftCode = GiftCode::query()
                ->where('rc_app_user_id', $data['appUserId'])
                ->where('rc_product_id', $subscription['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$giftCode) {
                return GiftCode::query()->create([
                    'code' => GiftCode::generateUniqueCode(),
                    'email' => $email,
                    'source' => 'web',
                    'plan' => $subscription['plan'],
                    'rc_app_user_id' => $data['appUserId'],
                    'rc_product_id' => $subscription['product_id'],
                    'used' => false,
                    'used_date' => null,
                    'expires_at' => $subscription['expires_at'],
                ]);
            }

            $giftCode->forceFill([
                'expires_at' => $subscription['expires_at'],
                'email' => $giftCode->email ?: $email,
            ])->save();

            return $giftCode;
        });

        // One email per voucher; a refresh of the success page must not send it again.
        $emailSent = (bool) $giftCode->voucher_sent_at;
        if (!$emailSent) {
            try {
                Mail::to($giftCode->email)->send(new WebPurchaseVoucher($giftCode));
                $giftCode->forceFill(['voucher_sent_at' => now()])->save();
                $emailSent = true;
            } catch (Throwable $exception) {
                Log::error("Voucher email for gift code {$giftCode->id} failed: {$exception->getMessage()}");
            }
        }

        return response()->json([
            'message' => $emailSent ? 'Kod je poslan na email.' : 'Kod je izdat, ali email nije poslan.',
            'data' => [
                'code' => $giftCode->code,
                'link' => $giftCode->promoLink(),
                'plan' => $giftCode->plan,
                'planLabel' => $giftCode->planLabel(),
                'email' => $giftCode->email,
                'emailSent' => $emailSent,
                'expiresAt' => $giftCode->expires_at?->toIso8601String(),
            ],
        ], 201);
    }
}
