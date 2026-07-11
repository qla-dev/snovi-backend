<?php

namespace App\Support;

use Illuminate\Support\Str;

class MediaUrl
{
    public static function resolve(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) ? $path : $value;
        $normalizedPath = '/'.ltrim($path, '/');

        if (Str::contains($normalizedPath, '/storage/')) {
            $relativePath = Str::after($normalizedPath, '/storage/');

            return rtrim((string) config('media.storage_url'), '/').'/'.ltrim($relativePath, '/');
        }

        if (!Str::startsWith($value, ['http://', 'https://', '//'])) {
            return rtrim((string) config('media.storage_url'), '/').'/'.ltrim($value, '/');
        }

        return $value;
    }
}
