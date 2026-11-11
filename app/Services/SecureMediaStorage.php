<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureMediaStorage
{
    public const PRIVATE_DISK = 'local';

    public const LEGACY_PUBLIC_DISK = 'public';

    public function storeAs(string $directory, string $filename, UploadedFile $file): string
    {
        $path = $this->normalizePath(trim($directory, '/').'/'.$filename);
        abort_unless($path, 400, 'Lokasi file tidak valid.');

        return $file->storeAs(dirname($path), basename($path), self::PRIVATE_DISK);
    }

    public function put(string $path, string $contents): bool
    {
        $path = $this->normalizePath($path);
        abort_unless($path, 400, 'Lokasi file tidak valid.');

        return Storage::disk(self::PRIVATE_DISK)->put($path, $contents);
    }

    public function exists(?string $path): bool
    {
        $path = $this->normalizePath($path);

        return $path !== null && $this->locateNormalized($path) !== null;
    }

    public function delete(?string $path): bool
    {
        $path = $this->normalizePath($path);
        if ($path === null) {
            return false;
        }

        $deleted = false;
        foreach ([self::PRIVATE_DISK, self::LEGACY_PUBLIC_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                $deleted = Storage::disk($disk)->delete($path) || $deleted;
            }
        }

        return $deleted;
    }

    public function get(string $path): string
    {
        [$disk, $path] = $this->resolve($path);

        return Storage::disk($disk)->get($path);
    }

    public function mimeType(string $path): string
    {
        [$disk, $path] = $this->resolve($path);

        return Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
    }

    public function download(string $path, string $downloadName): StreamedResponse
    {
        [$disk, $path] = $this->resolve($path);

        return Storage::disk($disk)->download(
            $path,
            $this->safeDownloadName($downloadName),
            [
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'no-store, private, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function inline(string $path): Response
    {
        [$disk, $path] = $this->resolve($path);
        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $safeInlineMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];

        abort_unless(in_array(strtolower($mime), $safeInlineMimes, true), 415, 'File tidak dapat ditampilkan.');

        return response(Storage::disk($disk)->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function locate(?string $path): ?string
    {
        $path = $this->normalizePath($path);

        return $path === null ? null : $this->locateNormalized($path);
    }

    public function safeDownloadName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', '', $name) ?: 'download';
        $name = trim($name, " .\t\n\r\0\x0B");

        return mb_substr($name !== '' ? $name : 'download', 0, 180);
    }

    private function resolve(string $path): array
    {
        $path = $this->normalizePath($path);
        abort_unless($path, 404, 'File tidak ditemukan.');

        $disk = $this->locateNormalized($path);
        abort_unless($disk, 404, 'File tidak ditemukan.');

        return [$disk, $path];
    }

    private function locateNormalized(string $path): ?string
    {
        foreach ([self::PRIVATE_DISK, self::LEGACY_PUBLIC_DISK] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    private function normalizePath(?string $path): ?string
    {
        if (! is_string($path) || $path === '' || str_contains($path, "\0")) {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path)) {
            return null;
        }

        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        return implode('/', $segments);
    }
}
