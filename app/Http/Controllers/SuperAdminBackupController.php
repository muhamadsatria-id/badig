<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminBackupController extends Controller
{
    public function exportDatabase()
    {
        $filename = 'badig_backup_'.date('Ymd_His').'.sql';

        $path = storage_path('app/'.$filename);

        $db = config('database.connections.mysql');

        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            $db['username'],
            $db['password'],
            $db['database'],
            $path
        );

        exec($command);

        return response()->download($path)
            ->deleteFileAfterSend(true);
    }

    public function importDatabase(Request $request)
    {

        $request->validate([
            'database_file' => 'required|mimes:sql,gz',
        ]);

        $file =
            $request->file('database_file')
                ->store('backup');

        return back()
            ->with(
                'success',
                'File backup berhasil diupload'
            );

    }
}
