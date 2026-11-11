<?php

namespace App\Imports;

use App\Models\Archive;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;

class ArchiveImport implements ToModel
{
    public function model(array $row)
    {

        return new Archive([

            'judul' => $row[0],

            'tanggal_pembuatan_file' => $row[1],

            'unit_id' => Auth::user()->unit_id,

        ]);

    }
}
