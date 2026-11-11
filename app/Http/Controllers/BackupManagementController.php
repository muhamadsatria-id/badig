<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class BackupManagementController extends Controller
{
    private const DIRECTORY = 'backups';

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function index()
    {
        $this->authorizeSuperAdmin();
        $backups = SystemBackup::latest()->paginate(10);

        return view('admin.backups', compact('backups'));
    }

    public function trigger(): RedirectResponse
    {
        $this->authorizeSuperAdmin();
        $filename = 'backup-'.now()->format('Y-m-d-His').'-'.Str::lower(Str::random(6)).'.sql';
        $relative = self::DIRECTORY.'/'.$filename;
        Storage::disk('local')->makeDirectory(self::DIRECTORY);

        $record = SystemBackup::create([
            'nama_file' => $filename,
            'ukuran' => 0,
            'status' => 'PENDING',
            'operator_id' => auth()->id(),
        ]);

        try {
            $this->createSqlDump(Storage::disk('local')->path($relative));
            $size = (int) Storage::disk('local')->size($relative);

            if ($size < 20) {
                throw new \RuntimeException('Backup kosong.');
            }

            $record->update(['ukuran' => $size, 'status' => 'SUCCESS']);
            AuditLog::record('CREATE_BACKUP', 'Membuat backup database '.$filename.'.');

            return back()->with('success', 'Backup database berhasil dibuat.');
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($relative);
            $record->update(['status' => 'FAILED']);
            Log::error('Database backup failed.', [
                'backup_id' => $record->id,
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            return back()->with('error', 'Backup gagal dibuat. Periksa log aplikasi dan izin penyimpanan.');
        }
    }

    private function createSqlDump(string $absolute): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $pdo = $connection->getPdo();

        $tables = match ($driver) {
            'mysql' => collect($connection->select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"))
                ->map(fn ($row) => array_values((array) $row)[0] ?? null)->filter()->values()->all(),
            'sqlite' => collect($connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
                ->pluck('name')->all(),
            default => throw new \RuntimeException('Driver database tidak didukung.'),
        };

        $handle = fopen($absolute, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('File backup tidak dapat dibuat.');
        }

        try {
            fwrite($handle, "-- BADIG SQL backup\n-- ".now()->toIso8601String()."\n\n");
            fwrite($handle, $driver === 'mysql'
                ? "SET FOREIGN_KEY_CHECKS=0;\n\n"
                : "PRAGMA foreign_keys=OFF;\n\n");

            foreach ($tables as $table) {
                $quotedTable = $this->quoteIdentifier((string) $table, $driver);

                if ($driver === 'mysql') {
                    $create = (array) $connection->selectOne('SHOW CREATE TABLE '.$quotedTable);
                    $createSql = $create['Create Table'] ?? array_values($create)[1] ?? null;
                    if ($createSql) {
                        fwrite($handle, 'DROP TABLE IF EXISTS '.$quotedTable.";\n".$createSql.";\n");
                    }
                } elseif ($driver === 'sqlite') {
                    $schema = $connection->selectOne(
                        "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ? LIMIT 1",
                        [$table],
                    );
                    $createSql = $schema?->sql ?? null;
                    if ($createSql) {
                        fwrite($handle, 'DROP TABLE IF EXISTS '.$quotedTable.";\n".$createSql.";\n");
                    }
                }

                foreach ($connection->table($table)->orderByRaw('1')->cursor() as $row) {
                    $values = (array) $row;
                    if ($values === []) {
                        continue;
                    }

                    $columns = array_map(fn ($column) => $this->quoteIdentifier((string) $column, $driver), array_keys($values));
                    $encoded = array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), array_values($values));
                    fwrite($handle, 'INSERT INTO '.$quotedTable.' ('.implode(', ', $columns).') VALUES ('.implode(', ', $encoded).");\n");
                }

                fwrite($handle, "\n");
            }

            if ($driver === 'sqlite') {
                $schemaObjects = $connection->select(
                    "SELECT sql FROM sqlite_master WHERE type IN ('index', 'trigger') AND sql IS NOT NULL ORDER BY type, name"
                );

                foreach ($schemaObjects as $schemaObject) {
                    if (! empty($schemaObject->sql)) {
                        fwrite($handle, rtrim((string) $schemaObject->sql, ";\r\n").";\n");
                    }
                }
            }

            fwrite($handle, $driver === 'mysql'
                ? "\nSET FOREIGN_KEY_CHECKS=1;\n"
                : "\nPRAGMA foreign_keys=ON;\n");
        } finally {
            fclose($handle);
        }
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        return $driver === 'sqlite'
            ? '"'.str_replace('"', '""', $identifier).'"'
            : '`'.str_replace('`', '``', $identifier).'`';
    }
}
