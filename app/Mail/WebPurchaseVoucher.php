<?php

namespace App\Mail;

use App\Models\GiftCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Welcome email after a web subscription: the voucher code, its app link and a QR code. */
class WebPurchaseVoucher extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public GiftCode $giftCode)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Dobrodošli u snovi.fm 🌙 Vaš kod za aktivaciju');
    }

    public function content(): Content
    {
        $link = $this->giftCode->promoLink();

        return new Content(
            view: 'emails.web-purchase-voucher',
            with: [
                'code' => $this->giftCode->code,
                'codeGroups' => implode(' ', str_split($this->giftCode->code, 4)),
                'link' => $link,
                'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?format=png&size=360x360&margin=12&color=1e1b4b&data='.urlencode($link),
                'planLabel' => $this->giftCode->planLabel(),
                'expiresAt' => $this->giftCode->expires_at?->setTimezone('Europe/Sarajevo')->format('d.m.Y'),
                'appStoreUrl' => 'https://apps.apple.com/app/snovi-fm/id6758638251',
                'googlePlayUrl' => 'https://play.google.com/store/apps/details?id=snovi.qla.dev',
            ],
        );
    }
}
