<?php

namespace App\Imports;

use App\Models\DatabaseOrang;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DatabaseOrangImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    private const MAX_IMPORT_ROWS = 5000;

    private int $processed = 0;

    public function __construct(private User $user) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $this->processed++;
            if ($this->processed > self::MAX_IMPORT_ROWS) {
                throw ValidationException::withMessages([
                    'file' => 'Import dibatasi maksimal '.self::MAX_IMPORT_ROWS.' baris per file.',
                ]);
            }

            $data = $row->toArray();
            $defaultUnitId = $this->user->isSuperAdmin() ? 3 : (int) $this->user->unit_id;
            $unitId = (int) ($data['unit_id'] ?? $defaultUnitId);

            if (! $this->user->canWriteUnitId($unitId)) {
                continue;
            }

            $payload = [
                'unit_id' => $unitId,
                'instansi' => $data['instansi'] ?? 'SATUAN INTELKAM',
                'sifat_dokumen' => $data['sifat_dokumen'] ?? 'TERBATAS',
                'jenis_dokumen' => $data['jenis_dokumen'] ?? 'DAFTAR ORANG',
                'nomor_dpo' => $data['nomor_dpo'] ?? null,
                'tanggal_dikeluarkan' => $data['tanggal_dikeluarkan'] ?? null,
                'tempat_dikeluarkan' => $data['tempat_dikeluarkan'] ?? null,
                'kategori' => $data['kategori'] ?? null,
                'status_dpo' => $data['status_dpo'] ?? 'Aktif',
                'nama' => $data['nama'] ?? null,
                'nik' => $data['nik'] ?? null,
                'kewarganegaraan' => $data['kewarganegaraan'] ?? 'Indonesia',
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'phone' => $data['phone'] ?? ($data['no_hp'] ?? null),
                'alamat' => $data['alamat'] ?? null,
                'alamat_alternatif' => $data['alamat_alternatif'] ?? null,
                'dugaan_tindak_pidana' => $data['dugaan_tindak_pidana'] ?? null,
                'melanggar_pasal' => $data['melanggar_pasal'] ?? null,
                'no_reg_kejahatan' => $data['no_reg_kejahatan'] ?? null,
                'analisis_deskripsi' => $data['analisis_deskripsi'] ?? ($data['keterangan'] ?? null),
                'nama_penyidik' => $data['nama_penyidik'] ?? null,
                'jabatan_penyidik' => $data['jabatan_penyidik'] ?? null,
                'no_hp_penyidik' => $data['no_hp_penyidik'] ?? null,
                'created_by' => $this->user->id,
            ];

            $validator = Validator::make($payload, [
                'unit_id' => ['required', 'integer', Rule::exists('units', 'id')->where('is_active', true)],
                'instansi' => ['nullable', 'string', 'max:255'],
                'sifat_dokumen' => ['nullable', 'string', 'max:100'],
                'jenis_dokumen' => ['nullable', 'string', 'max:100'],
                'nomor_dpo' => ['nullable', 'string', 'max:120'],
                'tanggal_dikeluarkan' => ['nullable', 'date'],
                'tempat_dikeluarkan' => ['nullable', 'string', 'max:255'],
                'kategori' => ['nullable', 'string', 'max:120'],
                'status_dpo' => ['nullable', 'string', 'max:80'],
                'nama' => ['required', 'string', 'max:255'],
                'nik' => ['nullable', 'string', 'max:40'],
                'kewarganegaraan' => ['nullable', 'string', 'max:120'],
                'jenis_kelamin' => ['nullable', 'string', 'max:20'],
                'tempat_lahir' => ['nullable', 'string', 'max:255'],
                'tanggal_lahir' => ['nullable', 'date'],
                'pekerjaan' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'alamat' => ['nullable', 'string', 'max:3000'],
                'alamat_alternatif' => ['nullable', 'string', 'max:3000'],
                'dugaan_tindak_pidana' => ['nullable', 'string', 'max:6000'],
                'melanggar_pasal' => ['nullable', 'string', 'max:3000'],
                'no_reg_kejahatan' => ['nullable', 'string', 'max:120'],
                'analisis_deskripsi' => ['nullable', 'string', 'max:10000'],
                'nama_penyidik' => ['nullable', 'string', 'max:255'],
                'jabatan_penyidik' => ['nullable', 'string', 'max:255'],
                'no_hp_penyidik' => ['nullable', 'string', 'max:50'],
                'created_by' => ['required', 'integer'],
            ]);

            if ($validator->fails()) {
                continue;
            }

            DatabaseOrang::create($validator->validated());
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
