<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores a resident's profile photo.
 *
 * Two things this deliberately does not do:
 *
 *  - It never keeps the uploaded bytes. Every upload is decoded and re-encoded
 *    as a plain JPEG, which drops EXIF (including GPS coordinates from a phone
 *    camera) and neutralises files that are a valid image and a valid script at
 *    once. What lands on disk is only ever what GD wrote.
 *  - It never writes inside public/. On the live Hostinger host the root
 *    .htaccess returns 403 for anything under /storage, so the usual
 *    `storage:link` route to serving these would be dead on arrival. The files
 *    sit on the private disk and ProfileController::photo() streams them, which
 *    also keeps resident photos off the open web.
 */
class ProfilePhoto
{
    /** Stored square edge, in pixels. Displayed at 128px at most, so this is 2x for retina. */
    private const EDGE = 512;

    /** Refuse absurd source dimensions before decoding — a 30k x 30k PNG is small on disk and huge in memory. */
    private const MAX_SOURCE_PIXELS = 40_000_000;

    private const QUALITY = 82;

    /**
     * Process and store the upload, returning its path on the "local" disk.
     * Any previous photo for the user is removed.
     *
     * @throws \RuntimeException when the file is not an image GD can read
     */
    public static function store(UploadedFile $file, User $user): string
    {
        $image = self::decode($file);

        try {
            $square = self::squareThumbnail($image);

            try {
                ob_start();
                imagejpeg($square, null, self::QUALITY);
                $jpeg = ob_get_clean();
            } finally {
                imagedestroy($square);
            }
        } finally {
            imagedestroy($image);
        }

        // A random name per upload, so a replaced photo can never be served
        // from a browser or proxy cache under its old URL.
        $path = "avatars/{$user->id}/" . Str::random(32) . '.jpg';

        Storage::disk('local')->put($path, $jpeg);

        self::delete($user->avatar_path);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * @return \GdImage
     */
    private static function decode(UploadedFile $file)
    {
        $size = @getimagesize($file->getRealPath());

        if ($size === false) {
            throw new \RuntimeException('That file is not an image we can read.');
        }

        if ($size[0] * $size[1] > self::MAX_SOURCE_PIXELS) {
            throw new \RuntimeException('That image is too large. Please use one under 40 megapixels.');
        }

        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if ($image === false) {
            throw new \RuntimeException('That file is not an image we can read.');
        }

        return self::applyExifOrientation($image, $file, $size[2]);
    }

    /**
     * Phone cameras record rotation in EXIF rather than rotating the pixels, so
     * a portrait selfie arrives sideways unless this is applied. The exif
     * extension is not guaranteed on shared hosting, hence the guard.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private static function applyExifOrientation($image, UploadedFile $file, int $type)
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $rotation = match ($exif['Orientation'] ?? 1) {
            3       => 180,
            6       => -90,
            8       => 90,
            default => 0,
        };

        if ($rotation === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $rotation, 0);

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    /**
     * Centre-crop to a square and scale to EDGE, so portrait and landscape
     * uploads both come out as a clean circle in the top bar.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private static function squareThumbnail($image)
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $edge   = min($width, $height);

        $canvas = imagecreatetruecolor(self::EDGE, self::EDGE);

        // JPEG has no alpha channel: fill first so a transparent PNG comes out
        // on white rather than black.
        imagefilledrectangle($canvas, 0, 0, self::EDGE, self::EDGE, imagecolorallocate($canvas, 255, 255, 255));

        imagecopyresampled(
            $canvas, $image,
            0, 0,
            intdiv($width - $edge, 2), intdiv($height - $edge, 2),
            self::EDGE, self::EDGE,
            $edge, $edge,
        );

        return $canvas;
    }
}
