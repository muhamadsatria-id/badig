<?php

namespace App\Http\Controllers;

use App\Models\ArchiveFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ArchiveImport;

class FileManagerController extends Controller
{

    public function index(Request $request)
    {

        $files = ArchiveFile::when(
            $request->search,
            function($query) use ($request){

                $query->where(
                    'nama_file',
                    'like',
                    '%'.$request->search.'%'
                );

            }
        )
        ->latest()
        ->get();


        return view(
            'file-manager.index',
            compact('files')
        );

    }


    public function upload(Request $request)
    {

        $request->validate([

            'file'=>'required|file|max:200480'

        ]);


        $file=$request->file('file');


        $path=$file->store(
            'badig-files',
            'public'
        );


        ArchiveFile::create([

            'unit_id'=>Auth::user()->unit_id,

            'nama_file'=>$file->getClientOriginalName(),

            'path_file'=>$path,

            'extension'=>$file->extension(),

            'size'=>$file->getSize()

        ]);


        return back();

    }



    public function download($id)
    {

        $file=ArchiveFile::findOrFail($id);


        return Storage::disk('public')
            ->download(
                $file->path_file,
                $file->nama_file
            );

    }

}
