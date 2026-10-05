<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use Illuminate\Support\Facades\Response;

class GiftCodeController extends Controller
{
    public function index()
    {
        $giftCodes = GiftCode::query()
            ->orderByDesc('id')
            ->get();

        return view('admin.gift-codes.index', compact('giftCodes'));
    }

    public function store()
    {
        GiftCode::query()->create([
            'code' => GiftCode::generateUniqueCode(),
            'used' => false,
            'used_date' => null,
            'expires_at' => now()->addYear(),
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

    /** Printable voucher (A4), the same look as snovi.fm/promo-code; ?print=1 opens the print/PDF dialog. */
    public function voucher(GiftCode $giftCode)
    {
        return view('admin.gift-codes.voucher', compact('giftCode'));
    }

    public function qr(GiftCode $giftCode)
    {
        $promoLink = 'https://snovi.fm/promo-code/' . $giftCode->code;
        $qrSource = 'https://api.qrserver.com/v1/create-qr-code/?format=svg&size=2048x2048&ecc=H&margin=64&data=' . urlencode($promoLink);
        $escapedSource = e($qrSource);
        $escapedCode = e($giftCode->code);
        $escapedLink = e($promoLink);

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="2048" height="2048" viewBox="0 0 2048 2048" role="img" aria-label="QR kod {$escapedCode}">
  <rect width="2048" height="2048" fill="#ffffff"/>
  <image href="{$escapedSource}" x="0" y="0" width="2048" height="2048"/>
  <metadata>
    <code>{$escapedCode}</code>
    <link>{$escapedLink}</link>
  </metadata>
</svg>
SVG;

        return Response::make($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="snovi-' . $giftCode->code . '-qr.svg"',
        ]);
    }
}
