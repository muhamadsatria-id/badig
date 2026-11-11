<?php

namespace App\Services;

class DocumentMediaService
{
    public function __construct(private readonly SecureMediaStorage $storage) {}

    public function storageDataUri(?string $path, string $disk = SecureMediaStorage::PRIVATE_DISK, int $maxDimension = 1000): ?string
    {
        if (! $path || ! $this->storage->exists($path)) {
            return null;
        }

        @ini_set('memory_limit', '512M');

        try {
            $mime = strtolower($this->storage->mimeType($path));
            $safeMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (! in_array($mime, $safeMimes, true)) {
                return null;
            }

            $binary = $this->storage->get($path);

            if ($binary === '') {
                return null;
            }

            if (extension_loaded('gd') && strlen($binary) > 250000) {
                $optimized = $this->resizeImageBinary($binary, $maxDimension);
                if ($optimized) {
                    return 'data:image/jpeg;base64,'.base64_encode($optimized);
                }
            }

            return 'data:'.$mime.';base64,'.base64_encode($binary);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve the intrinsic pixel dimensions of a base64 image data URI.
     *
     * @return array{0:int,1:int}|null
     */
    public function dataUriDimensions(?string $dataUri): ?array
    {
        if (! $dataUri || ! preg_match('#^data:[^;]+;base64,(.*)$#s', $dataUri, $matches)) {
            return null;
        }

        $binary = base64_decode($matches[1], true);

        if ($binary === false || $binary === '') {
            return null;
        }

        $info = @getimagesizefromstring($binary);

        if (! $info || $info[0] <= 0 || $info[1] <= 0) {
            return null;
        }

        return [(int) $info[0], (int) $info[1]];
    }

    private function resizeImageBinary(string $binary, int $maxDimension): ?string
    {
        try {
            $src = @imagecreatefromstring($binary);
            if (! $src) {
                return null;
            }

            $width = imagesx($src);
            $height = imagesy($src);

            if ($width <= 0 || $height <= 0) {
                imagedestroy($src);

                return null;
            }

            $ratio = min(1, $maxDimension / $width, $maxDimension / $height);
            $newWidth = (int) max(1, round($width * $ratio));
            $newHeight = (int) max(1, round($height * $ratio));
            $dst = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagejpeg($dst, null, 82);
            $resized = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            return $resized ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}
