<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFile;
use Illuminate\Support\Facades\Storage;

class SecureDownloadController extends Controller
{

    public function download($id)
    {
        $file = ArchiveFile::findOrFail($id);

        return Storage::disk('public')
            ->download(
                $file->path_file,
                $file->nama_file
            );
    }

}
