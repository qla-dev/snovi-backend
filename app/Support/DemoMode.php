<?php

namespace App\Support;

use App\Models\Story;

class DemoMode
{
    private static bool $enabled = false;

    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    public static function setEnabled(bool $enabled): void
    {
        self::$enabled = $enabled;
    }

    public static function shouldUnlockStory(Story $story, int $position): bool
    {
        if (self::$enabled) {
            return true;
        }

        return ! $story->locked || $position < 5;
    }
}
