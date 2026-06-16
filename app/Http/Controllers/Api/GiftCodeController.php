<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftCodeController extends Controller
{
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

            if (!$giftCode || $giftCode->used) {
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
                ],
            ], 409);
        }

        return response()->json([
            'message' => 'Gift kod je iskoristen.',
            'data' => [
                'id' => $giftCode->id,
                'code' => $giftCode->code,
                'used' => $giftCode->used,
                'used_date' => $giftCode->used_date,
            ],
        ]);
    }
}
