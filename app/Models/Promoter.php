<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promoter extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'headline',
        'bio',
        'photo_url',
        'instagram',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // gift_codes.promoter_id has no foreign key, so its codes are released here.
        static::deleting(fn (Promoter $promoter) => $promoter->giftCodes()->update(['promoter_id' => null]));
    }

    public function giftCodes()
    {
        return $this->hasMany(GiftCode::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function pageLink(): string
    {
        return 'https://snovi.fm/promoters/'.$this->username;
    }

    /** Public card for snovi.fm: the promoter page and "a gift from ..." on the voucher page. */
    public function publicPayload(): array
    {
        return [
            'username' => $this->username,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->fullName(),
            'headline' => $this->headline,
            'bio' => $this->bio,
            'photo_url' => $this->photo_url,
            'instagram' => $this->instagram,
            'url' => $this->pageLink(),
        ];
    }
}
