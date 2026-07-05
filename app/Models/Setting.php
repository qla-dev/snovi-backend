<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->value;
    }

    public static function setValue(string $key, mixed $value): self
    {
        $setting = static::query()->firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->save();

        return $setting;
    }

    public static function demoModeEnabled(): bool
    {
        $value = static::getValue('demo_mode', false);

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setDemoMode(bool $enabled): self
    {
        return static::setValue('demo_mode', $enabled ? '1' : '0');
    }
}
