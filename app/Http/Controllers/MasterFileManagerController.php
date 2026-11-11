<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFile;
use App\Models\Unit;
use Illuminate\Http\Request;

class MasterFileManagerController extends Controller
{
    public function index(Request $request)
    {

        $files = ArchiveFile::with('unit')
            ->when(
                $request->unit_id,
                function ($query) use ($request) {

                    $query->where(
                        'unit_id',
                        $request->unit_id
                    );

                }
            )
            ->latest()
            ->get();

        $units = Unit::all();

        return view(
            'super-admin.file-manager.index',
            compact(
                'files',
                'units'
            )
        );

    }
}
