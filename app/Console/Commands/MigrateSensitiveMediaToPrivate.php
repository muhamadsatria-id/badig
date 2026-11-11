<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSensitiveMediaToPrivate extends Command
{
    protected $signature = 'security:migrate-private-files {--dry-run : Show changes without moving files}';

    protected $description = 'Move sensitive BADIG operational media from public storage to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $prefixes = ['badig/arsip', 'badig/database-buronan', 'badig/profile-photos'];
        $moved = 0;
        $skipped = 0;

        foreach ($prefixes as $prefix) {
            foreach ($public->allFiles($prefix) as $path) {
                if ($private->exists($path)) {
                    $sameSize = (int) $private->size($path) === (int) $public->size($path);
                    $sameHash = $sameSize
                        && hash_file('sha256', $private->path($path)) === hash_file('sha256', $public->path($path));

                    if (! $sameHash) {
                        $this->warn("Konflik isi; file publik dipertahankan untuk pemeriksaan manual: {$path}");
                        $skipped++;

                        continue;
                    }

                    $this->line(($this->option('dry-run') ? '[DRY-RUN] ' : '')."Bersihkan duplikat publik: {$path}");
                    if (! $this->option('dry-run') && ! $public->delete($path)) {
                        $this->warn("File privat sudah ada, tetapi duplikat publik gagal dihapus: {$path}");
                        $skipped++;

                        continue;
                    }

                    $moved++;

                    continue;
                }

                $this->line(($this->option('dry-run') ? '[DRY-RUN] ' : '')."Pindahkan: {$path}");

                if ($this->option('dry-run')) {
                    $moved++;

                    continue;
                }

                $stream = $public->readStream($path);
                if (! is_resource($stream)) {
                    $this->error("Gagal membaca: {$path}");

                    continue;
                }

                try {
                    if (! $private->writeStream($path, $stream)) {
                        $this->error("Gagal menulis: {$path}");

                        continue;
                    }
                } finally {
                    fclose($stream);
                }

                if (! $public->delete($path)) {
                    $this->warn("File tersalin tetapi file publik belum terhapus: {$path}");
                }

                $moved++;
            }
        }

        $this->info("Selesai. Dipindahkan: {$moved}; dilewati: {$skipped}.");
        $this->warn('Setelah verifikasi, pastikan web server tidak menyajikan storage/app/private dan hapus salinan sensitif lama dari public/storage.');

        return self::SUCCESS;
    }
}
