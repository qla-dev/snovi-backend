<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Draws one carousel slide for Instagram and Facebook: the photo centre-cropped to 1080x1350 (4:5,
 * the tallest feed ratio; every slide of a carousel must share it), a soft shade at the top with the
 * snovi.fm logo in the top-left corner, and optionally a short line of text at the bottom.
 *
 * Plain GD, like geovizija. Sources are data URLs from the agent or https links.
 */
class SocialSlideRenderer
{
    public const WIDTH = 1080;
    public const HEIGHT = 1350;

    private const MAX_SOURCE_BYTES = 8 * 1024 * 1024;
    private const LOGO_WIDTH = 240;
    private const MARGIN = 44;
    private const FONT_SIZE = 50;
    private const LINE_HEIGHT = 74;
    private const MAX_LINES = 3;

    /** Renders the slide and returns its path under public/. */
    public function render(string $source, ?string $text, string $name): string
    {
        $photo = $this->load($source);
        $canvas = $this->cover($photo);
        imagedestroy($photo);

        imagealphablending($canvas, true);
        $this->shade($canvas, 0, 300, 70, true);
        $this->logo($canvas);

        $text = trim((string) $text);
        if ($text !== '') {
            $this->shade($canvas, self::HEIGHT - 520, self::HEIGHT, 115, false);
            $this->caption($canvas, $text);
        }

        $relative = 'media/social/'.now()->format('Ym').'/'.$name.'.jpg';
        File::ensureDirectoryExists(dirname(public_path($relative)));
        imageinterlace($canvas, true);
        if (! imagejpeg($canvas, public_path($relative), 88)) {
            throw new RuntimeException('Slajd nije spremljen.');
        }
        imagedestroy($canvas);

        return $relative;
    }

    private function load(string $source): GdImage
    {
        if (preg_match('#^data:image/(png|jpe?g|webp|gif);base64,#i', $source, $match)) {
            $bytes = base64_decode(substr($source, strlen($match[0])), true);
        } elseif (str_starts_with($source, 'https://')) {
            $response = Http::timeout(30)->get($source);
            $bytes = $response->successful() ? $response->body() : false;
        } else {
            throw new RuntimeException('Slika mora biti data:image/...;base64 ili https link.');
        }

        if ($bytes === false || $bytes === '' || strlen($bytes) > self::MAX_SOURCE_BYTES) {
            throw new RuntimeException('Slika nije učitana ili je veća od 8 MB.');
        }

        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            throw new RuntimeException('Format slike nije podržan.');
        }
        if (imagesx($image) < 800 || imagesy($image) < 800) {
            imagedestroy($image);
            throw new RuntimeException('Slika je premala (najmanje 800x800 px).');
        }

        return $image;
    }

    private function cover(GdImage $photo): GdImage
    {
        $sourceWidth = imagesx($photo);
        $sourceHeight = imagesy($photo);
        $ratio = self::WIDTH / self::HEIGHT;
        $cropWidth = min($sourceWidth, (int) round($sourceHeight * $ratio));
        $cropHeight = min($sourceHeight, (int) round($cropWidth / $ratio));

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled(
            $canvas, $photo, 0, 0,
            intdiv($sourceWidth - $cropWidth, 2), intdiv($sourceHeight - $cropHeight, 2),
            self::WIDTH, self::HEIGHT, $cropWidth, $cropHeight,
        );

        return $canvas;
    }

    /** Night-blue gradient, strongest at the top edge ($fromTop) or at the bottom edge. */
    private function shade(GdImage $canvas, int $y1, int $y2, int $strength, bool $fromTop): void
    {
        $span = max(1, $y2 - $y1);
        for ($y = $y1; $y < $y2; $y += 2) {
            $s = $fromTop ? 1 - ($y - $y1) / $span : ($y - $y1) / $span;
            $alpha = 127 - (int) round($strength * $s ** 1.6);
            imagefilledrectangle($canvas, 0, $y, self::WIDTH, $y + 1, imagecolorallocatealpha($canvas, 8, 10, 32, max(0, $alpha)));
        }
    }

    private function logo(GdImage $canvas): void
    {
        $logo = @imagecreatefrompng(resource_path('images/social-logo.png'));
        if (! $logo) {
            throw new RuntimeException('Logo (resources/images/social-logo.png) nije pronađen.');
        }
        imagepalettetotruecolor($logo);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);

        $height = (int) round(imagesy($logo) * self::LOGO_WIDTH / imagesx($logo));
        imagecopyresampled($canvas, $logo, self::MARGIN, self::MARGIN, 0, 0, self::LOGO_WIDTH, $height, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }

    private function caption(GdImage $canvas, string $text): void
    {
        $font = resource_path('fonts/Merriweather-Black.ttf');
        $maxWidth = self::WIDTH - 2 * 70;
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', $text) as $word) {
            $candidate = $line === '' ? $word : "{$line} {$word}";
            $box = imagettfbbox(self::FONT_SIZE, 0, $font, $candidate);
            if ($line !== '' && $box[2] - $box[0] > $maxWidth) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }
        }
        $lines[] = $line;

        if (count($lines) > self::MAX_LINES) {
            $lines = array_slice($lines, 0, self::MAX_LINES);
            $lines[self::MAX_LINES - 1] = rtrim($lines[self::MAX_LINES - 1], ' .,;:').'…';
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $shadow = imagecolorallocatealpha($canvas, 0, 0, 0, 80);
        $y = self::HEIGHT - 110 - (count($lines) - 1) * self::LINE_HEIGHT;

        foreach ($lines as $current) {
            $box = imagettfbbox(self::FONT_SIZE, 0, $font, $current);
            $x = (int) round((self::WIDTH - ($box[2] - $box[0])) / 2);
            imagettftext($canvas, self::FONT_SIZE, 0, $x + 2, $y + 3, $shadow, $font, $current);
            imagettftext($canvas, self::FONT_SIZE, 0, $x, $y, $white, $font, $current);
            $y += self::LINE_HEIGHT;
        }
    }
}
