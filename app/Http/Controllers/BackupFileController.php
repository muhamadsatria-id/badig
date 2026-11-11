<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupFileController extends Controller
{
    private const DIRECTORY = 'backups';

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    private function allowedFilename(string $filename): bool
    {
        return $filename === basename($filename)
            && (bool) preg_match('/^[A-Za-z0-9._-]+\.(sql|sql\.gz|gz)$/', $filename);
    }

    private function relativePath(string $filename): ?string
    {
        if (! $this->allowedFilename($filename)) {
            return null;
        }

        $relative = self::DIRECTORY.'/'.$filename;

        return Storage::disk('local')->exists($relative) ? $relative : null;
    }

    public function index()
    {
        $this->authorizeSuperAdmin();

        $files = collect(Storage::disk('local')->files(self::DIRECTORY))
            ->filter(fn (string $path) => $this->allowedFilename(basename($path)))
            ->map(function (string $path) {
                $bytes = (int) Storage::disk('local')->size($path);

                return [
                    'name' => basename($path),
                    'path_label' => 'storage/app/private/'.$path,
                    'size' => $this->humanSize($bytes),
                    'modified_at' => date('d/m/Y H:i:s', Storage::disk('local')->lastModified($path)),
                    'timestamp' => Storage::disk('local')->lastModified($path),
                ];
            })
            ->sortByDesc('timestamp')
            ->values();

        return view('admin.backup-files', compact('files'));
    }

    public function download(string $filename): BinaryFileResponse
    {
        $this->authorizeSuperAdmin();
        $path = $this->relativePath($filename);
        abort_unless($path, 404, 'File backup tidak ditemukan.');

        AuditLog::record('DOWNLOAD_BACKUP', 'Mengunduh backup '.basename($path).'.');

        return response()->download(Storage::disk('local')->path($path), basename($path), [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function delete(string $filename): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $path = $this->relativePath($filename);

        if (! $path) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk('local')->delete($path);
        AuditLog::record('DELETE_BACKUP', 'Menghapus backup '.basename($path).'.');

        return back()->with('success', 'File backup berhasil dihapus.');
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = max(0, $bytes);
        $index = 0;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return number_format($size, $index === 0 ? 0 : 2).' '.$units[$index];
    }
}
