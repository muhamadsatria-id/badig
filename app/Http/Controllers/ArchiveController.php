<?php

namespace App\Http\Controllers;

use App\Models\Archive;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchiveController extends Controller
{
    public function index()
    {
        $archives = Archive::latest()->get();

        return view(
            'archives.index',
            compact('archives')
        );
    }

    public function store(Request $request)
    {

        $request->validate([

            'judul' => 'required',
            'tanggal_pembuatan_file' => 'required|date',
            'attachment_file' => 'nullable|mimes:jpg,jpeg,pdf,docx,xlsx|max:10240',

        ]);

        $data = $request->only([
            'judul',
            'tanggal_pembuatan_file',
        ]);

        if ($request->hasFile('attachment_file')) {

            $data['attachment_file'] =
                $request->file('attachment_file')
                    ->store('archives', 'public');

        }

        $data['unit_id'] =
            Auth::user()->unit_id;

        Archive::create($data);

        return back()
            ->with('success', 'Arsip berhasil disimpan');

    }

    public function exportSelected(Request $request)
    {

        $request->validate([
            'ids' => 'required|array',
        ]);

        $archives =
            Archive::whereIn(
                'id',
                $request->ids
            )->get();

        $pdf = Pdf::loadView(
            'archives.pdf',
            compact('archives')
        );

        return $pdf->download(
            'BADIG_ARSIP_EXPORT.pdf'
        );

    }
}
