<?php

namespace App\Http\Controllers;

use App\Imports\DatabaseOrangImport;
use App\Models\AuditLog;
use App\Models\DataAttachment;
use App\Models\DatabaseOrang;
use App\Models\Unit;
use App\Services\DocumentMediaService;
use App\Services\SecureMediaStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class DatabaseOrangController extends Controller
{
    private const MAX_IMPORT_ROWS = 5000;

    private array $allowedAttachmentMimes = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt',
        'ppt', 'pptx',
    ];

    private function validateFiles(Request $request): void
    {
        if ($request->hasFile('attachments')) {
            $files = array_filter((array) $request->file('attachments'), function ($file) {
                return $file instanceof UploadedFile && $file->isValid() && $file->getSize() > 0;
            });
            $request->files->set('attachments', array_values($files));
        }

        if ($request->hasFile('foto_utama')) {
            $request->validate([
                'foto_utama' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            ]);
        }

        $request->validate([
            'attachments' => ['nullable', 'array', 'max:30'],
            'attachments.*' => ['file', File::types($this->allowedAttachmentMimes)->max(50 * 1024)],
        ]);
    }

    private function unitsForForm()
    {
        $user = auth()->user();

        return Unit::active()->whereBetween('id', [1, 4])->orderBy('id')->get()
            ->filter(fn ($unit) => $user->canAccessUnitId((int) $unit->id))
            ->values();
    }

    private function scopedQuery()
    {
        return DatabaseOrang::with(['unit', 'creator'])->withCount('attachments');
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->canAccessDatabaseOrang(), 403, 'Akses terbatas. Master Data Orang hanya dapat diakses oleh Unit 3 dan Super Admin.');
    }

    private function authorizeRecord(DatabaseOrang $orang, bool $write = false): void
    {
        $this->authorizeAccess();
        $user = auth()->user();
        $allowed = $write
            ? $user->canWriteUnitId((int) $orang->unit_id)
            : $user->canAccessUnitId((int) $orang->unit_id);

        abort_unless($allowed, 403, 'Data orang ini berada di luar unit kerja Anda.');
    }

    private function attachmentQuery(DatabaseOrang $orang)
    {
        return DataAttachment::query()
            ->where('unit', 'buronan')
            ->where('unit_id', (int) $orang->unit_id)
            ->where('record_table', 'database_orang')
            ->where('record_id', $orang->id);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $validatedFilters = $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'unit_id' => ['nullable', 'integer', 'between:1,4'],
            'kategori' => ['nullable', 'string', 'max:120'],
        ]);

        $filters = [
            'q' => trim((string) ($validatedFilters['q'] ?? '')),
            'unit_id' => isset($validatedFilters['unit_id']) ? (string) $validatedFilters['unit_id'] : '',
            'kategori' => trim((string) ($validatedFilters['kategori'] ?? '')),
        ];

        $query = $this->scopedQuery();

        if ($filters['q'] !== '') {
            $needle = $filters['q'];
            $query->where(function ($q) use ($needle) {
                $q->where('nama', 'like', '%'.$needle.'%')
                    ->orWhere('nik', 'like', '%'.$needle.'%')
                    ->orWhere('nomor_dpo', 'like', '%'.$needle.'%')
                    ->orWhere('no_reg_kejahatan', 'like', '%'.$needle.'%')
                    ->orWhere('phone', 'like', '%'.$needle.'%')
                    ->orWhere('alamat', 'like', '%'.$needle.'%')
                    ->orWhere('dugaan_tindak_pidana', 'like', '%'.$needle.'%');
            });
        }

        if ($filters['unit_id'] !== '' && auth()->user()->canAccessUnitId((int) $filters['unit_id'])) {
            $query->where('unit_id', (int) $filters['unit_id']);
        }

        if ($filters['kategori'] !== '') {
            $query->where('kategori', $filters['kategori']);
        }

        $people = $query->latest('tanggal_dikeluarkan')->latest()->paginate(15)->withQueryString();
        $units = $this->unitsForForm();
        $categories = $this->scopedQuery()->whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        return view('database-orang.index', compact('people', 'units', 'filters', 'categories'));
    }

    public function create()
    {
        $this->authorizeAccess();

        $units = $this->unitsForForm();
        abort_if($units->isEmpty(), 403, 'Tidak ada unit aktif yang dapat digunakan.');

        $orang = new DatabaseOrang([
            'unit_id' => auth()->user()->unit_id ?: $units->first()?->id,
            'instansi' => 'SATUAN INTELKAM',
            'sifat_dokumen' => 'TERBATAS',
            'jenis_dokumen' => 'Masukan Jenis Dokumen',
            'kewarganegaraan' => 'Indonesia',
            'status_dpo' => 'Aktif',
            'tanggal_dikeluarkan' => now(),
        ]);

        $attachments = collect();

        return view('database-orang.form', compact('orang', 'units', 'attachments'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $this->validateFiles($request);
        $data = $this->validated($request);
        abort_unless(auth()->user()->canWriteUnitId((int) $data['unit_id']), 403, 'Anda tidak dapat menulis data pada unit tersebut.');
        $data['created_by'] = auth()->id();

        $orang = DB::transaction(function () use ($request, $data) {
            $orang = DatabaseOrang::create($data);
            $this->storePrimaryPhoto($request, $orang);
            $this->storeAttachments($request, $orang);

            return $orang;
        });

        AuditLog::record('CREATE_DATABASE_ORANG', 'Menambah Data Orang ID '.$orang->id.' atas nama '.$orang->nama);

        return redirect()->route('database-orang.show', $orang)->with('success', 'Data orang, foto, dan lampiran berhasil disimpan.');
    }

    public function show(DatabaseOrang $database_orang)
    {
        $this->authorizeRecord($database_orang);
        $orang = $database_orang->load(['unit', 'creator']);
        $attachments = $this->attachmentQuery($orang)->with('uploader')->latest()->get();

        return view('database-orang.show', compact('orang', 'attachments'));
    }

    public function edit(DatabaseOrang $database_orang)
    {
        $this->authorizeRecord($database_orang, true);
        $orang = $database_orang;
        $units = $this->unitsForForm();
        $attachments = $this->attachmentQuery($orang)->with('uploader')->latest()->get();

        return view('database-orang.form', compact('orang', 'units', 'attachments'));
    }

    public function update(Request $request, DatabaseOrang $database_orang)
    {
        $this->authorizeRecord($database_orang, true);
        $this->validateFiles($request);
        $data = $this->validated($request, $database_orang);
        abort_unless(auth()->user()->canWriteUnitId((int) $data['unit_id']), 403, 'Anda tidak dapat menulis data pada unit tersebut.');

        DB::transaction(function () use ($request, $database_orang, $data) {
            $database_orang->update($data);
            DataAttachment::query()->where('unit', 'buronan')->where('record_table', 'database_orang')->where('record_id', $database_orang->id)->update(['unit_id' => (int) $database_orang->unit_id]);
            $this->storePrimaryPhoto($request, $database_orang);
            $this->storeAttachments($request, $database_orang);
        });

        AuditLog::record('UPDATE_DATABASE_ORANG', 'Memperbarui Data Orang ID '.$database_orang->id.' atas nama '.$database_orang->nama);

        return redirect()->route('database-orang.show', $database_orang)->with('success', 'Data orang berhasil diperbarui.');
    }

    public function destroy(DatabaseOrang $database_orang)
    {
        $this->authorizeRecord($database_orang, true);
        $name = $database_orang->nama;

        if ($database_orang->foto) {
            app(SecureMediaStorage::class)->delete($database_orang->foto);
        }

        foreach ($this->attachmentQuery($database_orang)->get() as $attachment) {
            app(SecureMediaStorage::class)->delete($attachment->file_path);
            $attachment->delete();
        }

        $database_orang->delete();
        AuditLog::record('DELETE_DATABASE_ORANG', 'Menghapus Data Orang: '.$name);

        return redirect()->route('database-orang.index')->with('success', 'Data orang dan seluruh lampirannya berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $this->authorizeAccess();

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $people = $this->scopedQuery()->latest('tanggal_dikeluarkan')->limit(1000)->get();
        $pdf = Pdf::loadView('database-orang.pdf', ['people' => $people]);
        $pdf->setPaper('a4', 'landscape');
        AuditLog::record('EXPORT_DATABASE_ORANG_LIST_PDF', 'Export daftar Data Orang total '.$people->count().' baris.');

        return $pdf->stream('daftar-orang-'.now()->format('YmdHis').'.pdf');
    }

    public function pdf(DatabaseOrang $database_orang, DocumentMediaService $media)
    {
        $this->authorizeRecord($database_orang);

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $orang = $database_orang->load(['unit', 'creator']);
        $attachments = $this->attachmentQuery($orang)->latest()->get();
        $photoDataUri = $media->storageDataUri($orang->foto);
        $attachmentImages = $attachments->filter->is_image->map(function ($file) use ($media) {
            $dataUri = $media->storageDataUri($file->file_path);

            if (! $dataUri) {
                return null;
            }

            [$width, $height] = $this->fitImageWithin($media->dataUriDimensions($dataUri), 225, 165);

            return [
                'file' => $file,
                'data_uri' => $dataUri,
                'width' => $width,
                'height' => $height,
            ];
        })->filter()->values();
        $documentAttachments = $attachments->reject->is_image->values();

        AuditLog::record('EXPORT_DATABASE_ORANG_PDF', 'Mencetak dokumen Orang '.$orang->nomor_dpo.' atas nama '.$orang->nama);
        $filename = Str::slug('Orang '.($orang->nomor_dpo ?: $orang->id).' '.$orang->nama).'.pdf';

        return Pdf::loadView('database-orang.dpo-pdf', compact('orang', 'photoDataUri', 'attachmentImages', 'documentAttachments'))
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }

    /**
     * Scale intrinsic image dimensions to fit within the given box.
     *
     * @param  array{0:int,1:int}|null  $dimensions
     * @return array{0:int,1:int}
     */
    private function fitImageWithin(?array $dimensions, int $maxWidth, int $maxHeight): array
    {
        $width = (int) ($dimensions[0] ?? 0);
        $height = (int) ($dimensions[1] ?? 0);

        if ($width <= 0 || $height <= 0) {
            return [0, 0];
        }

        $scale = min(1, $maxWidth / $width, $maxHeight / $height);

        return [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];
    }

    public function photo(DatabaseOrang $database_orang, SecureMediaStorage $storage)
    {
        $this->authorizeRecord($database_orang);
        abort_unless($database_orang->foto, 404, 'Foto tidak ditemukan.');

        return $storage->inline($database_orang->foto);
    }

    public function previewAttachment(DatabaseOrang $database_orang, int $attachment, SecureMediaStorage $storage)
    {
        $this->authorizeRecord($database_orang);
        $file = $this->attachmentQuery($database_orang)->findOrFail($attachment);
        abort_unless($file->is_viewable, 415, 'Lampiran tidak dapat ditampilkan.');

        return $storage->inline($file->file_path);
    }

    public function downloadAttachment(DatabaseOrang $database_orang, int $attachment)
    {
        $this->authorizeRecord($database_orang);
        $file = $this->attachmentQuery($database_orang)->findOrFail($attachment);
        AuditLog::record('DOWNLOAD_ORANG_ATTACHMENT', 'Download lampiran '.$file->original_name.' dari Orang #'.$database_orang->id);

        return app(SecureMediaStorage::class)->download($file->file_path, $file->original_name);
    }

    public function deleteAttachment(DatabaseOrang $database_orang, int $attachment)
    {
        $this->authorizeRecord($database_orang, true);
        $file = $this->attachmentQuery($database_orang)->findOrFail($attachment);
        app(SecureMediaStorage::class)->delete($file->file_path);
        $file->delete();
        AuditLog::record('DELETE_ORANG_ATTACHMENT', 'Menghapus lampiran '.$file->original_name.' dari Orang #'.$database_orang->id);

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $this->authorizeAccess();

        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:10240']]);
        // The mimes rule above validates file content; the original extension is
        // used only to select the matching parser after validation succeeds.
        $extension = strtolower((string) $request->file('file')->extension());

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            abort_unless(class_exists(Excel::class), 500, 'Paket maatwebsite/excel belum terpasang.');
            DB::transaction(function () use ($request): void {
                Excel::import(
                    new DatabaseOrangImport(auth()->user()),
                    $request->file('file')
                );
            });
            AuditLog::record('IMPORT_DATABASE_ORANG_EXCEL', 'Import Excel Data Orang.');

            return back()->with('success', 'Import Excel berhasil diproses.');
        }

        $path = $request->file('file')->store('badig/imports', 'local');
        $handle = fopen(Storage::disk('local')->path($path), 'r');

        if (! is_resource($handle)) {
            Storage::disk('local')->delete($path);
            throw ValidationException::withMessages(['file' => 'File CSV tidak dapat dibaca.']);
        }

        $count = 0;
        $skipped = 0;
        $processed = 0;

        try {
            $header = fgetcsv($handle);
            if (! is_array($header)) {
                throw ValidationException::withMessages(['file' => 'Header CSV tidak valid.']);
            }

            $header = array_map(static function ($key): string {
                $key = preg_replace('/^\xEF\xBB\xBF/', '', (string) $key) ?: '';

                return strtolower(trim($key));
            }, $header);

            if (! in_array('nama', $header, true)) {
                throw ValidationException::withMessages(['file' => 'CSV wajib memiliki kolom nama.']);
            }

            DB::transaction(function () use ($handle, $header, &$count, &$skipped, &$processed): void {
                while (($row = fgetcsv($handle)) !== false) {
                    $processed++;
                    if ($processed > self::MAX_IMPORT_ROWS) {
                        throw ValidationException::withMessages([
                            'file' => 'Import dibatasi maksimal '.self::MAX_IMPORT_ROWS.' baris per file.',
                        ]);
                    }

                    $assoc = [];
                    foreach ($header as $i => $key) {
                        if ($key !== '') {
                            $assoc[$key] = $row[$i] ?? null;
                        }
                    }

                    $payload = $this->validatedImportRow($assoc);
                    if ($payload === null) {
                        $skipped++;

                        continue;
                    }

                    DatabaseOrang::create($payload);
                    $count++;
                }
            });
        } finally {
            fclose($handle);
            Storage::disk('local')->delete($path);
        }

        AuditLog::record('IMPORT_DATABASE_ORANG_CSV', 'Import CSV Data Orang: '.$count.' masuk, '.$skipped.' dilewati.');

        return back()->with('success', 'Import CSV selesai. '.$count.' baris masuk dan '.$skipped.' baris tidak valid/diluar otorisasi dilewati.');
    }

    private function storePrimaryPhoto(Request $request, DatabaseOrang $orang): void
    {
        if (! $request->hasFile('foto_utama')) {
            return;
        }

        $storage = app(SecureMediaStorage::class);
        $oldPath = $orang->foto;
        $file = $request->file('foto_utama');
        $extension = strtolower($file->extension() ?: 'jpg');
        $filename = 'foto-'.Str::uuid().'.'.$extension;
        $path = $storage->storeAs('badig/database-buronan/'.$orang->id, $filename, $file);

        try {
            $orang->update(['foto' => $path]);
        } catch (\Throwable $exception) {
            $storage->delete($path);
            throw $exception;
        }

        if ($oldPath) {
            $storage->delete($oldPath);
        }
    }

    private function storeAttachments(Request $request, DatabaseOrang $orang): int
    {
        $count = 0;
        foreach ((array) $request->file('attachments', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'bin');
            $storedName = Str::uuid().'.'.$extension;
            $path = app(SecureMediaStorage::class)->storeAs('badig/database-buronan/'.$orang->id.'/attachments', $storedName, $file);
            try {
                DataAttachment::create([
                    'unit' => 'buronan',
                    'unit_id' => (int) $orang->unit_id,
                    'record_table' => 'database_orang',
                    'record_id' => $orang->id,
                    'original_name' => app(SecureMediaStorage::class)->safeDownloadName($file->getClientOriginalName()),
                    'stored_name' => $storedName,
                    'file_path' => $path,
                    'extension' => $extension,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize() ?: 0,
                    'uploaded_by' => auth()->id(),
                    'attachment_role' => 'dpo_evidence',
                ]);
            } catch (\Throwable $exception) {
                app(SecureMediaStorage::class)->delete($path);
                throw $exception;
            }
            $count++;
        }

        return $count;
    }

    private function validated(Request $request, ?DatabaseOrang $orang = null): array
    {
        return $request->validate([
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')->where('is_active', true)],
            'kategori' => ['nullable', 'string', 'max:120'],
            'status_dpo' => ['nullable', 'string', 'max:80'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'sifat_dokumen' => ['nullable', 'string', 'max:100'],
            'jenis_dokumen' => ['nullable', 'string', 'max:100'],
            'nomor_dpo' => ['nullable', 'string', 'max:120'],
            'tanggal_dikeluarkan' => ['nullable', 'date'],
            'tempat_dikeluarkan' => ['nullable', 'string', 'max:255'],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:40'],
            'nrp' => ['nullable', 'string', 'max:80'],
            'kewarganegaraan' => ['nullable', 'string', 'max:120'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'catatan_hukum' => ['nullable', 'string', 'max:4000'],
            'rumus_dactiloscopy' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:3000'],
            'alamat_alternatif' => ['nullable', 'string', 'max:3000'],
            'tinggi_badan' => ['nullable', 'string', 'max:50'],
            'berat_badan' => ['nullable', 'string', 'max:50'],
            'rambut' => ['nullable', 'string', 'max:120'],
            'bentuk_tubuh' => ['nullable', 'string', 'max:120'],
            'warna_kulit' => ['nullable', 'string', 'max:120'],
            'warna_mata' => ['nullable', 'string', 'max:120'],
            'hidung' => ['nullable', 'string', 'max:120'],
            'bibir' => ['nullable', 'string', 'max:120'],
            'ciri_lainnya' => ['nullable', 'string', 'max:4000'],
            'dugaan_tindak_pidana' => ['nullable', 'string', 'max:6000'],
            'melanggar_pasal' => ['nullable', 'string', 'max:3000'],
            'no_reg_kejahatan' => ['nullable', 'string', 'max:120'],
            'keterangan_lain' => ['nullable', 'string', 'max:6000'],
            'analisis_deskripsi' => ['nullable', 'string', 'max:10000'],
            'keterangan' => ['nullable', 'string', 'max:6000'],
            'nama_penyidik' => ['nullable', 'string', 'max:255'],
            'jabatan_penyidik' => ['nullable', 'string', 'max:255'],
            'no_hp_penyidik' => ['nullable', 'string', 'max:50'],
            'pejabat_penandatangan' => ['nullable', 'string', 'max:255'],
            'nrp_penandatangan' => ['nullable', 'string', 'max:80'],
        ]);
    }

    private function validatedImportRow(array $row): ?array
    {
        $user = auth()->user();
        $defaultUnitId = $user->isSuperAdmin() ? 3 : (int) $user->unit_id;
        $unitId = (int) ($row['unit_id'] ?? $defaultUnitId);

        if (! $user->canWriteUnitId($unitId)) {
            return null;
        }

        $payload = [
            'unit_id' => $unitId,
            'nama' => $row['nama'] ?? null,
            'nik' => $row['nik'] ?? null,
            'kategori' => $row['kategori'] ?? null,
            'nomor_dpo' => $row['nomor_dpo'] ?? null,
            'phone' => $row['phone'] ?? ($row['no_hp'] ?? null),
            'alamat' => $row['alamat'] ?? null,
            'dugaan_tindak_pidana' => $row['dugaan_tindak_pidana'] ?? null,
            'melanggar_pasal' => $row['melanggar_pasal'] ?? null,
            'analisis_deskripsi' => $row['analisis_deskripsi'] ?? ($row['keterangan'] ?? null),
            'created_by' => $user->id,
        ];

        $validator = Validator::make($payload, [
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')->where('is_active', true)],
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:40'],
            'kategori' => ['nullable', 'string', 'max:120'],
            'nomor_dpo' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:3000'],
            'dugaan_tindak_pidana' => ['nullable', 'string', 'max:6000'],
            'melanggar_pasal' => ['nullable', 'string', 'max:3000'],
            'analisis_deskripsi' => ['nullable', 'string', 'max:10000'],
            'created_by' => ['required', 'integer'],
        ]);

        return $validator->fails() ? null : $validator->validated();
    }
}
