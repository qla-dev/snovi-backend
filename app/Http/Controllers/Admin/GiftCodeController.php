<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class GiftCodeController extends Controller
{
    public function index()
    {
        $giftCodes = GiftCode::query()
            ->orderByDesc('id')
            ->get();

        return view('admin.gift-codes.index', compact('giftCodes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:12', 'unique:gift_codes,code'],
            'expires_at' => ['nullable', 'date'],
        ]);

        GiftCode::query()->create([
            'code' => $data['code'],
            'used' => false,
            'used_date' => null,
            'expires_at' => !empty($data['expires_at'])
                ? Carbon::parse($data['expires_at'])->endOfDay()
                : now()->addYear(),
        ]);

        return redirect()
            ->route('admin.gift-codes.index')
            ->with('status', 'Gift kod je dodan.');
    }

    public function expire(GiftCode $giftCode)
    {
        if (!$giftCode->used) {
            $giftCode->forceFill([
                'used' => true,
                'used_date' => now(),
            ])->save();
        }

        return redirect()
            ->route('admin.gift-codes.index')
            ->with('status', 'Gift kod je istekao.');
    }
}
