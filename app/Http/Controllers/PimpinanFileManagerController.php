<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DataAttachment;
use App\Models\DatabaseOrang;
use App\Models\Unit1Ideology;
use App\Models\Unit2Economy;
use App\Models\Unit3Sosbud;
use App\Models\Unit4Security;
use App\Services\SecureMediaStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PimpinanFileManagerController extends Controller
{
    private const ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'ppt', 'pptx'];

    private array $units = [
        'unit1' => 'Ideologi Politik',
        'unit2' => 'Ekonomi',
        'unit3' => 'Sosial Budaya',
        'unit4' => 'Keamanan Negara',
    ];

    private array $recordModels = [
        'unit1_ideology' => [Unit1Ideology::class, 'nama_subjek'],
        'unit2_economy' => [Unit2Economy::class, 'nama_aset'],
        'unit3_sosbud' => [Unit3Sosbud::class, 'nama_organisasi'],
        'unit4_security_crim' => [Unit4Security::class, 'nama_kasus'],
        'database_orang' => [DatabaseOrang::class, 'nama'],
    ];

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();
        [$query, $filters] = $this->filteredQuery($request);
        $units = $this->units;

        // Kelompokkan berkas per rekam arsip (unit + record), bukan per file.
        $groupRows = (clone $query)
            ->selectRaw('unit, record_id, record_table, COUNT(*) as files_count, MAX(created_at) as latest_at')
            ->groupBy('unit', 'record_id', 'record_table')
            ->orderByRaw('MAX(created_at) DESC')
            ->get();

        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));
        $pageRows = $groupRows->forPage($page, $perPage)->values();

        $attachments = (clone $query)
            ->whereIn('record_id', $pageRows->pluck('record_id')->all())
            ->whereIn('unit', $pageRows->pluck('unit')->unique()->all())
            ->with('uploader')
            ->latest()
            ->get()
            ->groupBy(fn ($file) => $file->unit.'|'.$file->record_id);

        $meta = $this->recordMeta($pageRows);

        $groups = $pageRows->map(function ($row) use ($attachments, $meta) {
            $key = $row->unit.'|'.$row->record_id;
            $info = $meta[$row->record_table.'|'.$row->record_id] ?? [];

            return (object) [
                'unit' => $row->unit,
                'record_id' => (int) $row->record_id,
                'record_table' => $row->record_table,
                'files_count' => (int) $row->files_count,
                'latest_at' => $row->latest_at ? Carbon::parse($row->latest_at) : null,
                'title' => $info['title'] ?? '',
                'category' => $info['category'] ?? '',
                'files' => $attachments->get($key, collect()),
            ];
        });

        $groups = new LengthAwarePaginator($groups, $groupRows->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return view('pimpinan.file-manager', compact('groups', 'filters', 'units'));
    }

    private function recordMeta($pageRows): array
    {
        $meta = [];

        foreach ($pageRows->groupBy('record_table') as $table => $rows) {
            $modelInfo = $this->recordModels[$table] ?? null;
            if ($modelInfo === null) {
                continue;
            }

            [$model, $column] = $modelInfo;
            $records = $model::query()
                ->whereIn('id', $rows->pluck('record_id')->all())
                ->get(['id', $column, 'kategori']);

            foreach ($records as $record) {
                $meta[$table.'|'.$record->id] = [
                    'title' => (string) ($record->{$column} ?? ''),
                    'category' => (string) ($record->kategori ?? ''),
                ];
            }
        }

        return $meta;
    }

    public function export(Request $request, string $type)
    {
        $this->authorizeSuperAdmin();
        abort_unless($type === 'pdf', 404);
        [$query, $filters] = $this->filteredQuery($request);
        $attachments = $query->latest()->limit(1000)->get();
        $units = $this->units;

        AuditLog::record('GLOBAL_FILE_MANAGER_EXPORT_PDF', 'Export PDF File Manager Semua Unit total '.$attachments->count().' file.');

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        return Pdf::loadView('units.report-pdf', [
            'unit' => 'all',
            'module' => ['title' => 'File Manager Semua Unit'],
            'items' => collect(),
            'filters' => $filters,
            'attachmentCounts' => collect(),
            'globalAttachments' => $attachments,
            'units' => $units,
        ])->setPaper('a4', 'landscape')
            ->stream(Str::slug('BADIG file manager semua unit '.now()->format('Ymd His')).'.pdf');
    }

    public function downloadAttachment(int $attachment, SecureMediaStorage $storage)
    {
        $this->authorizeSuperAdmin();
        $file = DataAttachment::query()->findOrFail($attachment);
        AuditLog::record('DOWNLOAD_ATTACHMENT_SUPER_ADMIN', 'Download lampiran '.$file->original_name.' record #'.$file->record_id);

        return $storage->download($file->file_path, $file->original_name);
    }

    public function previewAttachment(int $attachment, SecureMediaStorage $storage)
    {
        $this->authorizeSuperAdmin();
        $file = DataAttachment::query()->findOrFail($attachment);

        abort_unless($file->is_viewable, 415, 'Lampiran tidak dapat ditampilkan.');
        AuditLog::record('PREVIEW_ATTACHMENT_SUPER_ADMIN', 'Menampilkan pratinjau lampiran '.$file->original_name.' record #'.$file->record_id);

        return $storage->inline($file->file_path);
    }

    public function deleteAttachment(int $attachment, SecureMediaStorage $storage)
    {
        $this->authorizeSuperAdmin();
        $file = DataAttachment::query()->findOrFail($attachment);
        $storage->delete($file->file_path);
        $file->delete();
        AuditLog::record('DELETE_ATTACHMENT_SUPER_ADMIN', 'Menghapus lampiran '.$file->original_name.' record #'.$file->record_id);

        return back()->with('success', 'File berhasil dihapus.');
    }

    private function filteredQuery(Request $request): array
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'unit' => ['nullable', 'string', 'in:all,unit1,unit2,unit3,unit4'],
            'type' => ['nullable', 'string', 'in:'.implode(',', self::ALLOWED_TYPES)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filters = [
            'q' => trim((string) ($filters['q'] ?? '')),
            'unit' => (string) ($filters['unit'] ?? 'all'),
            'type' => trim((string) ($filters['type'] ?? '')),
            'date_from' => (string) ($filters['date_from'] ?? ''),
            'date_to' => (string) ($filters['date_to'] ?? ''),
        ];

        $query = DataAttachment::query()->with('uploader');

        if ($filters['unit'] !== 'all') {
            $query->where('unit', $filters['unit']);
        }
        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $query->where(function ($sub) use ($q): void {
                $sub->where('original_name', 'like', '%'.$q.'%')
                    ->orWhere('extension', 'like', '%'.$q.'%');

                if (is_numeric($q)) {
                    $sub->orWhere('record_id', (int) $q);
                }
            });
        }
        if ($filters['type'] !== '') {
            $query->where('extension', strtolower($filters['type']));
        }
        if ($filters['date_from'] !== '') {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return [$query, $filters];
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Akses hanya untuk Super Admin.');
    }
}
