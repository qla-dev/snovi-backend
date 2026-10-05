<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class GiftCode extends Model
{
    use HasFactory;

    public $timestamps = false;

    private const CODE_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const CODE_DIGITS = '0123456789';

    protected $fillable = [
        'code',
        'email',
        'source',
        'plan',
        'rc_app_user_id',
        'rc_product_id',
        'voucher_sent_at',
        'used',
        'used_date',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'used_date' => 'datetime',
            'expires_at' => 'datetime',
            'voucher_sent_at' => 'datetime',
        ];
    }

    public function isWebPurchase(): bool
    {
        return $this->source === 'web' && filled($this->rc_app_user_id);
    }

    public function planLabel(): string
    {
        return $this->plan === 'monthly' ? 'Mjesečni plan' : 'Godišnji plan';
    }

    /** The universal link that opens the app with this code (falls back to the web voucher page). */
    public function promoLink(): string
    {
        return 'https://snovi.fm/promo-code/'.$this->code;
    }

    public static function generateUniqueCode(): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $code = self::generateCode();

            if (!self::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw ValidationException::withMessages([
            'code' => 'Nije moguce generisati jedinstven gift kod. Pokusajte ponovo.',
        ]);
    }

    /** 6 letters and 6 digits, shuffled. */
    private static function generateCode(): string
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
