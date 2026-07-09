<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GiftCodeController extends Controller
{
    private const CODE_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const CODE_DIGITS = '0123456789';

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
            'expires_at' => ['nullable', 'date'],
        ]);

        GiftCode::query()->create([
            'code' => $this->generateUniqueCode(),
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

    private function generateUniqueCode(): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $code = $this->generateCode();

            if (!GiftCode::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw ValidationException::withMessages([
            'code' => 'Nije moguce generisati jedinstven gift kod. Pokusajte ponovo.',
        ]);
    }

    private function generateCode(): string
    {
        $characters = [];

        for ($i = 0; $i < 6; $i++) {
            $characters[] = self::CODE_LETTERS[random_int(0, strlen(self::CODE_LETTERS) - 1)];
            $characters[] = self::CODE_DIGITS[random_int(0, strlen(self::CODE_DIGITS) - 1)];
        }

        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }

        return implode('', $characters);
    }
}
