<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DataAttachment;
use App\Models\Unit;
use App\Models\Unit1Ideology;
use App\Models\Unit2Economy;
use App\Models\Unit3Sosbud;
use App\Models\Unit4Security;
use App\Services\DocumentMediaService;
use App\Services\StaticMapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UnitDocumentController extends Controller
{
    private array $modules = [
        'unit1' => ['id' => 1, 'code' => 'IDPOL', 'title' => 'Ideologi Politik', 'model' => Unit1Ideology::class, 'table' => 'unit1_ideology', 'main' => 'nama_subjek', 'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_subjek', 'district_id', 'village_id', 'pengikut', 'status_legal', 'catatan_rawan', 'koordinat_lat', 'koordinat_lng', 'created_at', 'updated_at']],
        'unit2' => ['id' => 2, 'code' => 'EKON', 'title' => 'Ekonomi', 'model' => Unit2Economy::class, 'table' => 'unit2_economy', 'main' => 'nama_aset', 'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_aset', 'district_id', 'village_id', 'alamat', 'kapasitas', 'koordinat_lat', 'koordinat_lng', 'created_at', 'updated_at']],
        'unit3' => ['id' => 3, 'code' => 'SOSBUD', 'title' => 'Sosial Budaya', 'model' => Unit3Sosbud::class, 'table' => 'unit3_sosbud', 'main' => 'nama_organisasi', 'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_organisasi', 'pimpinan', 'district_id', 'village_id', 'total_anggota', 'legalitas', 'catatan_aktivitas', 'koordinat_lat', 'koordinat_lng', 'created_at', 'updated_at']],
        'unit4' => ['id' => 4, 'code' => 'KAMNEG', 'title' => 'Keamanan Negara', 'model' => Unit4Security::class, 'table' => 'unit4_security_crim', 'main' => 'nama_kasus', 'fields' => ['tanggal_pembuatan', 'pelapor_nama', 'pelapor_nrp', 'kategori', 'nama_kasus', 'district_id', 'village_id', 'modus', 'koordinat_lat', 'koordinat_lng', 'status_kasus', 'created_at', 'updated_at']],
    ];

    private function module(string $unit): array
    {
        abort_unless(isset($this->modules[$unit]), 404);
        $module = $this->modules[$unit];
        try {
            if (Schema::hasTable('units') && ($unitRow = Unit::find($module['id']))) {
                $module['title'] = $unitRow->nama_unit;
                $module['code'] = $unitRow->kode ?: $module['code'];
            }
        } catch (\Throwable) {
        }

        return $module;
    }

    private function authorizeUnit(string $unit): void
    {
        $module = $this->module($unit);
        abort_unless(auth()->user()->canAccessUnitId((int) $module['id']), 403, 'Dokumen ini berada di luar unit kerja Anda.');
    }

    private function findRecord(string $unit, int $id): array
    {
        $this->authorizeUnit($unit);
        $module = $this->module($unit);
        $item = $module['model']::with(['district', 'village', 'operator'])->findOrFail($id);
        abort_unless(auth()->user()->canAccessUnitId((int) ($item->unit_id ?: $module['id'])), 403);
        $attachments = DataAttachment::query()
            ->where('unit', $unit)->where('unit_id', (int) $module['id'])
            ->where('record_table', $module['table'])->where('record_id', $id)
            ->where(function ($query): void {
                $query->whereNull('attachment_role')
                    ->orWhere('attachment_role', '!=', 'report_pdf');
            })
            ->latest()->get();

        return [$module, $item, $attachments];
    }

    public function html(string $unit, int $id)
    {
        [$module, $item, $attachments] = $this->findRecord($unit, $id);
        AuditLog::record('PREVIEW_DOCUMENT', 'Membuka pratinjau dokumen '.$module['title'].' ID '.$id);

        return view('documents.unit', compact('unit', 'module', 'item', 'attachments'));
    }

    public function pdf(string $unit, int $id, StaticMapService $maps, DocumentMediaService $media)
    {
        [$module, $item, $attachments] = $this->findRecord($unit, $id);
        $mapSnapshot = $maps->snapshot(
            $item->koordinat_lat !== null ? (float) $item->koordinat_lat : null,
            $item->koordinat_lng !== null ? (float) $item->koordinat_lng : null,
        );
        $attachmentImages = $attachments->filter->is_image->map(fn ($file) => [
            'file' => $file,
            'data_uri' => $media->storageDataUri($file->file_path),
        ])->filter(fn ($row) => $row['data_uri'])->values();
        $documentAttachments = $attachments->reject->is_image->values();

        AuditLog::record('DOWNLOAD_PDF', 'Mengunduh PDF dokumen '.$module['title'].' ID '.$id);
        $filename = Str::slug('BADIG '.$module['title'].' '.$item->{$module['main']}.' '.$item->id).'.pdf';

        return Pdf::loadView('units.pdf_cetak', compact('unit', 'module', 'item', 'attachments', 'mapSnapshot', 'attachmentImages', 'documentAttachments'))
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }
}
