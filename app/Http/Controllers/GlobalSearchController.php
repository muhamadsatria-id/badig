<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use App\Models\ArchiveFile;
use App\Models\DatabaseOrang;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{

    public function search(Request $request)
    {

        $keyword = $request->keyword;


        $archives = Archive::where(
            'judul',
            'like',
            "%$keyword%"
        )->get();


        $files = ArchiveFile::where(
            'nama_file',
            'like',
            "%$keyword%"
        )->get();


        $people = DatabaseOrang::where(
            'nama',
            'like',
            "%$keyword%"
        )->get();


        return view(
            'search.result',
            compact(
                'archives',
                'files',
                'people'
            )
        );

    }

}
