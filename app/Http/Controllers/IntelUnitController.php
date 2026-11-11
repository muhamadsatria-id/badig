<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DataAttachment;
use App\Models\District;
use App\Models\Unit;
use App\Models\Unit1Ideology;
use App\Models\Unit2Economy;
use App\Models\Unit3Sosbud;
use App\Models\Unit4Security;
use App\Models\Village;
use App\Services\DocumentMediaService;
use App\Services\SecureMediaStorage;
use App\Services\StaticMapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class IntelUnitController extends Controller
{
    private array $modules = [
        'unit1' => [
            'id' => 1,
            'code' => 'IDPOL',
            'title' => 'Ideologi Politik',
            'model' => Unit1Ideology::class,
            'table' => 'unit1_ideology',
            'main' => 'nama_subjek',
            'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_subjek', 'district_id', 'village_id', 'pengikut', 'status_legal', 'catatan_rawan', 'koordinat_lat', 'koordinat_lng'],
        ],
        'unit2' => [
            'id' => 2,
            'code' => 'EKON',
            'title' => 'Ekonomi',
            'model' => Unit2Economy::class,
            'table' => 'unit2_economy',
            'main' => 'nama_aset',
            'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_aset', 'district_id', 'village_id', 'alamat', 'kapasitas', 'koordinat_lat', 'koordinat_lng'],
        ],
        'unit3' => [
            'id' => 3,
            'code' => 'SOSBUD',
            'title' => 'Sosial Budaya',
            'model' => Unit3Sosbud::class,
            'table' => 'unit3_sosbud',
            'main' => 'nama_organisasi',
            'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_organisasi', 'pimpinan', 'district_id', 'village_id', 'total_anggota', 'legalitas', 'catatan_aktivitas', 'koordinat_lat', 'koordinat_lng'],
        ],
        'unit4' => [
            'id' => 4,
            'code' => 'KAMNEG',
            'title' => 'Keamanan Negara',
            'model' => Unit4Security::class,
            'table' => 'unit4_security_crim',
            'main' => 'nama_kasus',
            'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_kasus', 'district_id', 'village_id', 'modus', 'koordinat_lat', 'koordinat_lng', 'status_kasus'],
        ],
    ];

    private array $allowedAttachmentMimes = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt',
        'ppt', 'pptx',
    ];

    private function module(string $unit): array
    {
        abort_unless(isset($this->modules[$unit]), 404);
        $module = $this->modules[$unit];

        try {
            if (Schema::hasTable('units')) {
                $unitRow = Unit::find($module['id']);
                if ($unitRow) {
                    $module['title'] = $unitRow->nama_unit;
                    $module['code'] = $unitRow->kode ?: $module['code'];
                }
            }
        } catch (\Throwable $e) {
            // Keep fallback title.
        }

        return $module;
    }

    private function authorizeUnit(string $unit, bool $write = false): void
    {
        $module = $this->module($unit);
        $user = auth()->user();

        $allowed = $write
            ? $user->canWriteUnitId((int) $module['id'])
            : $user->canAccessUnitId((int) $module['id']);

        abort_unless($allowed, 403, 'Unit ini berada di luar otorisasi Anda.');
    }

    private function authorizeRecord(object $record, array $module, bool $write = false): void
    {
        $recordUnitId = (int) ($record->unit_id ?: $module['id']);
        $allowed = $write
            ? auth()->user()->canWriteUnitId($recordUnitId)
            : auth()->user()->canAccessUnitId($recordUnitId);

        abort_unless($allowed, 403, 'Data ini berada di luar unit kerja Anda.');
    }

    private function attachmentQueryFor(string $unit, array $module)
    {
        return DataAttachment::query()
            ->where('unit', $unit)
            ->where('unit_id', (int) $module['id'])
            ->where('record_table', $module['table']);
    }

    private function attachmentsFor(string $unit, array $module, int $recordId)
    {
        return $this->attachmentQueryFor($unit, $module)
            ->where('record_id', $recordId)
            ->where(function ($q) {
                $q->whereNull('attachment_role')
                    ->orWhere('attachment_role', '!=', 'report_pdf');
            })
            ->with('uploader')
            ->latest()
            ->get();
    }

    private function applyUnitScope($query, array $module)
    {
        if (Schema::hasColumn($module['table'], 'unit_id')) {
            $query->where('unit_id', (int) $module['id']);
        }

        return $query;
    }

    public function index(Request $request, string $unit)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $model = $module['model'];

        $filters = $this->validatedListFilters($request);

        $query = $this->applyUnitScope($model::with(['district', 'village', 'operator']), $module);
        if ($filters['date_from'] !== '') {
            $query->whereDate('tanggal_pembuatan', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('tanggal_pembuatan', '<=', $filters['date_to']);
        }
        if ($filters['q'] !== '') {
            $needle = $filters['q'];
            $searchable = array_values(array_filter($module['fields'], fn ($field) => ! in_array($field, ['district_id', 'village_id', 'koordinat_lat', 'koordinat_lng'], true)));
            $query->where(function ($q) use ($searchable, $needle) {
                foreach ($searchable as $field) {
                    $q->orWhere($field, 'like', '%'.$needle.'%');
                }
                if (is_numeric($needle)) {
                    $q->orWhere('id', (int) $needle);
                }
            });
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $ids = $items->getCollection()->pluck('id')->all();
        $attachmentCounts = $this->attachmentQueryFor($unit, $module)
            ->whereIn('record_id', $ids)
            ->selectRaw('record_id, COUNT(*) as total')
            ->groupBy('record_id')
            ->pluck('total', 'record_id');

        return view('units.index', compact('unit', 'module', 'items', 'attachmentCounts', 'filters'));
    }

    public function create(string $unit)
    {
        $this->authorizeUnit($unit, true);
        $module = $this->module($unit);
        $districts = District::with('villages')->orderBy('name')->get();
        $villages = Village::orderBy('name')->get();
        $attachments = collect();

        return view('units.form', compact('unit', 'module', 'districts', 'villages', 'attachments'));
    }

    public function store(Request $request, string $unit)
    {
        $this->authorizeUnit($unit, true);
        $module = $this->module($unit);
        $this->validateAttachments($request);
        $item = DB::transaction(function () use ($request, $unit, $module) {
            $this->resolveManualLocations($request);
            $data = $this->validated($request, $unit);
            $data['unit_id'] = (int) $module['id'];
            $data['operator_id'] = auth()->id();

            return $module['model']::create($data);
        });
        $uploaded = $this->storeAttachments($request, $unit, $module, $item->id);
        $this->syncReportPdf($unit, $module, $item);

        AuditLog::record('CREATE_'.$unit, 'Menambah arsip '.$module['title'].' ID '.$item->id.' dengan '.$uploaded.' lampiran. Pelapor: '.$item->pelapor_nama.' / '.$item->pelapor_nrp);

        return redirect()->route('units.show', [$unit, $item->id])->with('success', 'Data arsip berhasil disimpan dan langsung aktif. Lampiran tersimpan: '.$uploaded.' file.');
    }

    public function show(string $unit, int $id)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $item = $module['model']::with(['district', 'village', 'operator'])->findOrFail($id);
        $this->authorizeRecord($item, $module);
        $attachments = $this->attachmentsFor($unit, $module, $id);

        return view('units.show', compact('unit', 'module', 'item', 'attachments'));
    }

    public function edit(string $unit, int $id)
    {
        $this->authorizeUnit($unit, true);
        $module = $this->module($unit);
        $item = $module['model']::findOrFail($id);
        $this->authorizeRecord($item, $module, true);
        $districts = District::with('villages')->orderBy('name')->get();
        $villages = Village::orderBy('name')->get();
        $attachments = $this->attachmentsFor($unit, $module, $id);

        return view('units.form', compact('unit', 'module', 'item', 'districts', 'villages', 'attachments'));
    }

    public function update(Request $request, string $unit, int $id)
    {
        $this->authorizeUnit($unit, true);
        $module = $this->module($unit);
        $this->validateAttachments($request);
        $item = $module['model']::findOrFail($id);
        $this->authorizeRecord($item, $module, true);
        DB::transaction(function () use ($request, $unit, $module, $item): void {
            $this->resolveManualLocations($request);
            $data = $this->validated($request, $unit);
            $data['unit_id'] = (int) $module['id'];
            $item->update($data);
        });
        $uploaded = $this->storeAttachments($request, $unit, $module, $item->id);
        $this->syncReportPdf($unit, $module, $item);

        AuditLog::record('UPDATE_'.$unit, 'Mengubah arsip '.$module['title'].' ID '.$item->id.' dan menambah '.$uploaded.' lampiran.');

        return redirect()->route('units.show', [$unit, $item->id])->with('success', 'Data arsip berhasil diperbarui. Lampiran baru: '.$uploaded.' file.');
    }

    public function destroy(string $unit, int $id)
    {
        $this->authorizeUnit($unit, true);
        $module = $this->module($unit);
        $item = $module['model']::findOrFail($id);
        $this->authorizeRecord($item, $module, true);
        $attachments = $this->attachmentQueryFor($unit, $module)
            ->where('record_id', $id)
            ->get();
        foreach ($attachments as $attachment) {
            app(SecureMediaStorage::class)->delete($attachment->file_path);
            $attachment->delete();
        }
        $item->delete();
        AuditLog::record('DELETE_'.$unit, 'Menghapus arsip '.$module['title'].' ID '.$id.' beserta '.$attachments->count().' lampiran.');

        return redirect()->route('units.index', $unit)->with('success', 'Data arsip dan lampirannya berhasil dihapus.');
    }

    public function fileManager(Request $request, string $unit)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $validatedFilters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'type' => ['nullable', 'string', Rule::in($this->allowedAttachmentMimes)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $filters = [
            'q' => trim((string) ($validatedFilters['q'] ?? '')),
            'type' => trim((string) ($validatedFilters['type'] ?? '')),
            'date_from' => (string) ($validatedFilters['date_from'] ?? ''),
            'date_to' => (string) ($validatedFilters['date_to'] ?? ''),
        ];

        $buildQuery = function () use ($unit, $module, $filters) {
            $query = $this->attachmentQueryFor($unit, $module);

            if ($filters['q'] !== '') {
                $q = $filters['q'];
                $query->where(function ($sub) use ($q) {
                    $sub->where('original_name', 'like', '%'.$q.'%')
                        ->orWhere('extension', 'like', '%'.$q.'%')
                        ->orWhere('record_id', is_numeric($q) ? (int) $q : 0);
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

            return $query;
        };

        // Kelompokkan berkas per rekam arsip (record), bukan per file, agar satu arsip
        // tampil sebagai satu data walau lampirannya banyak.
        $groupRows = $buildQuery()
            ->selectRaw('record_id, COUNT(*) as files_count, MAX(created_at) as latest_at')
            ->groupBy('record_id')
            ->orderByRaw('MAX(created_at) DESC')
            ->get();

        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));
        $pageRows = $groupRows->forPage($page, $perPage)->values();
        $recordIds = $pageRows->pluck('record_id')->all();

        $attachments = $buildQuery()
            ->whereIn('record_id', $recordIds)
            ->with('uploader')
            ->latest()
            ->get()
            ->groupBy('record_id');

        $records = $this->recordsByIds($module, $recordIds);

        $groups = $pageRows->map(function ($row) use ($attachments, $records, $module) {
            $record = $records->get($row->record_id);

            return (object) [
                'record_id' => (int) $row->record_id,
                'files_count' => (int) $row->files_count,
                'latest_at' => $row->latest_at ? Carbon::parse($row->latest_at) : null,
                'record' => $record,
                'title' => $record ? (string) ($record->{$module['main']} ?? '') : '',
                'files' => $attachments->get($row->record_id, collect()),
            ];
        });

        $groups = new LengthAwarePaginator($groups, $groupRows->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return view('units.file-manager', compact('unit', 'module', 'groups', 'filters'));
    }

    private function recordsByIds(array $module, array $ids)
    {
        if (empty($ids)) {
            return collect();
        }

        $model = $module['model'];

        return $model::query()
            ->whereIn('id', $ids)
            ->get(['id', $module['main']])
            ->keyBy('id');
    }

    public function downloadAttachment(string $unit, int $attachment)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $file = $this->attachmentQueryFor($unit, $module)->findOrFail($attachment);
        AuditLog::record('DOWNLOAD_ATTACHMENT', 'Download lampiran '.$file->original_name.' dari '.$module['title'].' record #'.$file->record_id);

        return app(SecureMediaStorage::class)->download($file->file_path, $file->original_name);
    }

    public function previewAttachment(string $unit, int $attachment, SecureMediaStorage $storage)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $file = $this->attachmentQueryFor($unit, $module)->findOrFail($attachment);

        abort_unless($file->is_viewable, 415, 'Lampiran tidak dapat ditampilkan.');
        AuditLog::record('PREVIEW_ATTACHMENT', 'Menampilkan pratinjau lampiran dari '.$module['title'].' record #'.$file->record_id);

        return $storage->inline($file->file_path);
    }

    public function deleteAttachment(string $unit, int $attachment)
    {
        $this->authorizeUnit($unit, true);
        $module = $this->module($unit);
        $file = $this->attachmentQueryFor($unit, $module)->findOrFail($attachment);
        app(SecureMediaStorage::class)->delete($file->file_path);
        $file->delete();
        AuditLog::record('DELETE_ATTACHMENT', 'Menghapus lampiran '.$file->original_name.' dari '.$module['title'].' record #'.$file->record_id);

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function exportSelectedPdf(Request $request, string $unit)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        $model = $module['model'];
        $items = $this->applyUnitScope($model::with(['district', 'village', 'operator']), $module)
            ->whereIn('id', $data['ids'])
            ->orderBy('id')
            ->get();

        abort_if($items->isEmpty(), 404, 'Data arsip tidak ditemukan.');

        $attachments = $this->attachmentQueryFor($unit, $module)
            ->whereIn('record_id', $items->pluck('id')->all())
            ->latest()
            ->get()
            ->groupBy('record_id');

        AuditLog::record('EXPORT_SELECTED_PDF', 'Export '.$items->count().' arsip '.$module['title'].' menjadi satu PDF.');

        $pdf = Pdf::loadView('units.export-selected-pdf', compact('unit', 'module', 'items', 'attachments'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream(Str::slug('BADIG '.$module['title'].' export terpilih '.now()->format('Ymd His')).'.pdf');
    }

    public function reportPdf(Request $request, string $unit)
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $filters = $this->validatedListFilters($request);

        $model = $module['model'];
        $query = $this->applyUnitScope($model::with(['district', 'village', 'operator']), $module);
        if ($filters['date_from'] !== '') {
            $query->whereDate('tanggal_pembuatan', '>=', $filters['date_from']);
        }
        if ($filters['date_to'] !== '') {
            $query->whereDate('tanggal_pembuatan', '<=', $filters['date_to']);
        }
        if ($filters['q'] !== '') {
            $needle = $filters['q'];
            $query->where(function ($q) use ($module, $needle) {
                $q->where('kategori', 'like', '%'.$needle.'%')
                    ->orWhere($module['main'], 'like', '%'.$needle.'%')
                    ->orWhere('pelapor_nama', 'like', '%'.$needle.'%')
                    ->orWhere('pelapor_nrp', 'like', '%'.$needle.'%');
            });
        }

        $items = $query->latest()->limit(500)->get();
        $attachmentCounts = $this->attachmentQueryFor($unit, $module)
            ->whereIn('record_id', $items->pluck('id')->all())
            ->selectRaw('record_id, COUNT(*) as total')
            ->groupBy('record_id')
            ->pluck('total', 'record_id');

        AuditLog::record('REPORT_PDF', 'Mencetak report PDF '.$module['title'].' total '.$items->count().' arsip.');

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $pdf = Pdf::loadView('units.report-pdf', compact('unit', 'module', 'items', 'filters', 'attachmentCounts'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream(Str::slug('BADIG report '.$module['title'].' '.now()->format('Ymd His')).'.pdf');
    }

    private function validateAttachments(Request $request): void
    {
        if ($request->hasFile('attachments')) {
            $files = array_filter((array) $request->file('attachments'), function ($file) {
                return $file instanceof UploadedFile && $file->isValid() && $file->getSize() > 0;
            });
            $request->files->set('attachments', array_values($files));
        }

        $request->validate([
            'attachments' => ['nullable', 'array', 'max:30'],
            'attachments.*' => ['file', File::types($this->allowedAttachmentMimes)->max(50 * 1024)],
        ]);
    }

    private function storeAttachments(Request $request, string $unit, array $module, int $recordId): int
    {
        if (! $request->hasFile('attachments')) {
            return 0;
        }

        $count = 0;
        foreach ((array) $request->file('attachments') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
            $storedName = now()->format('YmdHis').'_'.Str::random(12).'.'.$extension;
            $path = app(SecureMediaStorage::class)->storeAs('badig/arsip/'.$unit.'/'.$recordId, $storedName, $file);

            try {
                DataAttachment::create([
                    'unit' => $unit,
                    'unit_id' => (int) $module['id'],
                    'record_table' => $module['table'],
                    'record_id' => $recordId,
                    'original_name' => app(SecureMediaStorage::class)->safeDownloadName($file->getClientOriginalName()),
                    'stored_name' => $storedName,
                    'file_path' => $path,
                    'extension' => $extension,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize() ?: 0,
                    'uploaded_by' => auth()->id(),
                ]);
            } catch (\Throwable $exception) {
                app(SecureMediaStorage::class)->delete($path);
                throw $exception;
            }
            $count++;
        }

        return $count;
    }

    private function resolveManualLocations(Request $request): void
    {
        $request->validate([
            'kategori_manual' => ['nullable', 'string', 'max:100'],
            'district_name_manual' => ['nullable', 'string', 'max:255'],
            'village_name_manual' => ['nullable', 'string', 'max:255'],
        ]);

        $kategoriManual = trim((string) $request->input('kategori_manual', ''));
        if ($kategoriManual !== '' && (! $request->filled('kategori') || $request->input('kategori') === '__MANUAL__')) {
            $request->merge(['kategori' => $kategoriManual]);
        }

        $districtManual = trim((string) $request->input('district_name_manual', ''));
        if ($districtManual !== '') {
            $district = District::firstOrCreate([
                'name' => $districtManual,
            ]);
            $request->merge(['district_id' => $district->id]);
        }

        $villageManual = trim((string) $request->input('village_name_manual', ''));
        if ($villageManual !== '') {
            $districtId = $request->input('district_id');
            if (! $districtId && $districtManual !== '') {
                $district = District::firstOrCreate([
                    'name' => $districtManual,
                ]);
                $districtId = $district->id;
                $request->merge(['district_id' => $districtId]);
            }

            if ($districtId) {
                $village = Village::firstOrCreate([
                    'district_id' => (int) $districtId,
                    'name' => $villageManual,
                ]);
                $request->merge(['village_id' => $village->id]);
            }
        }
    }

    private function syncReportPdf(string $unit, array $module, object $item): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            $maps = app(StaticMapService::class);
            $media = app(DocumentMediaService::class);

            $attachments = $this->attachmentQueryFor($unit, $module)
                ->where('record_id', $item->id)
                ->where(function ($q) {
                    $q->whereNull('attachment_role')
                        ->orWhere('attachment_role', '!=', 'report_pdf');
                })
                ->latest()
                ->get();

            $mapSnapshot = $maps->snapshot(
                $item->koordinat_lat !== null ? (float) $item->koordinat_lat : null,
                $item->koordinat_lng !== null ? (float) $item->koordinat_lng : null,
            );
            $attachmentImages = $attachments->filter->is_image->map(fn ($file) => [
                'file' => $file,
                'data_uri' => $media->storageDataUri($file->file_path),
            ])->filter(fn ($row) => $row['data_uri'])->values();
            $documentAttachments = $attachments->reject->is_image->values();

            $pdf = Pdf::loadView('units.pdf_cetak', compact('unit', 'module', 'item', 'attachments', 'mapSnapshot', 'attachmentImages', 'documentAttachments'))
                ->setPaper('a4', 'portrait');

            $pdfContent = $pdf->output();

            $storedName = 'report_'.$item->id.'_'.time().'.pdf';
            $path = 'badig/arsip/'.$unit.'/'.$item->id.'/'.$storedName;
            app(SecureMediaStorage::class)->put($path, $pdfContent);

            $existingReport = $this->attachmentQueryFor($unit, $module)
                ->where('record_id', $item->id)
                ->where('attachment_role', 'report_pdf')
                ->first();
            $previousPath = $existingReport?->file_path;

            $report = DataAttachment::updateOrCreate([
                'unit' => $unit,
                'unit_id' => (int) $module['id'],
                'record_table' => $module['table'],
                'record_id' => $item->id,
                'attachment_role' => 'report_pdf',
            ], [
                'original_name' => 'Dokumen '.$module['title'].' - '.($item->{$module['main']} ?: ('#'.$item->id)).'.pdf',
                'stored_name' => $storedName,
                'file_path' => $path,
                'extension' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => strlen($pdfContent),
                'uploaded_by' => $item->operator_id ?: auth()->id(),
            ]);

            if ($previousPath && $previousPath !== $report->file_path) {
                app(SecureMediaStorage::class)->delete($previousPath);
            }
        } catch (\Throwable $e) {
            Log::error('syncReportPdf Error: '.$e->getMessage());
        }
    }

    private function validated(Request $request, string $unit): array
    {
        $base = [
            'tanggal_pembuatan' => ['nullable', 'date'],
            'pelapor_nama' => ['required', 'string', 'max:255'],
            'pelapor_nrp' => ['required', 'string', 'max:80'],
            'kategori' => ['required', 'string', 'max:100'],
            'district_id' => ['required', 'exists:districts,id'],
            'village_id' => [
                'required',
                Rule::exists('villages', 'id')->where(
                    fn ($query) => $query->where('district_id', (int) $request->input('district_id'))
                ),
            ],
            'koordinat_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'koordinat_lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];

        $rules = match ($unit) {
            'unit1' => array_merge($base, [
                'nama_subjek' => ['required', 'string', 'max:255'],
                'pengikut' => ['nullable', 'integer', 'min:0'],
                'status_legal' => ['nullable', 'string', 'max:100'],
                'catatan_rawan' => ['nullable', 'string', 'max:10000'],
            ]),
            'unit2' => array_merge($base, [
                'nama_aset' => ['required', 'string', 'max:255'],
                'alamat' => ['required', 'string', 'max:500'],
                'kapasitas' => ['nullable', 'string', 'max:255'],
            ]),
            'unit3' => array_merge($base, [
                'nama_organisasi' => ['required', 'string', 'max:255'],
                'pimpinan' => ['nullable', 'string', 'max:255'],
                'total_anggota' => ['nullable', 'integer', 'min:0'],
                'legalitas' => ['nullable', 'string', 'max:255'],
                'catatan_aktivitas' => ['nullable', 'string', 'max:10000'],
            ]),
            'unit4' => array_merge($base, [
                'nama_kasus' => ['required', 'string', 'max:255'],
                'status_kasus' => ['required', 'string', 'max:255'],
                'catatan_aktivitas' => ['nullable', 'string', 'max:10000'],
                'modus' => ['nullable', 'string', 'max:10000'],
                'data_enkripsi' => ['nullable', 'string', 'max:20000'],
            ]),
        };

        $messages = [
            'kategori.required' => 'Kategori entitas wajib dipilih atau diisi.',
            'district_id.required' => 'Kecamatan wajib dipilih atau diisi.',
            'district_id.exists' => 'Kecamatan yang dipilih tidak valid.',
            'village_id.required' => 'Kelurahan / Desa wajib dipilih atau diisi.',
            'village_id.exists' => 'Kelurahan / Desa yang dipilih tidak valid.',
            'pelapor_nama.required' => 'Nama pelapor wajib diisi.',
            'pelapor_nrp.required' => 'NRP pelapor wajib diisi.',
            'nama_subjek.required' => 'Nama subjek organisasi wajib diisi.',
            'nama_aset.required' => 'Nama objek/aset ekonomi wajib diisi.',
            'alamat.required' => 'Alamat operasional lengkap wajib diisi.',
            'nama_organisasi.required' => 'Nama ormas/lembaga wajib diisi.',
            'nama_kasus.required' => 'Nama subjek / judul kasus wajib diisi.',
        ];

        return $request->validate($rules, $messages);
    }

    private function validatedListFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
        ];
    }
}
