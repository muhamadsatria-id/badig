<?php

namespace App\Services;

use Illuminate\Support\Str;

class SecureFileService
{
    public static function upload($file, $folder)
    {
        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'pdf',
            'docx',
            'xlsx',
        ];

        if (! in_array(
            strtolower($file->extension()),
            $allowed
        )) {
            abort(422, 'Format file tidak diizinkan');
        }

        $filename = Str::uuid()
            .'.'
            .$file->extension();

        return $file->storeAs(
            $folder,
            $filename,
            'public'
        );
    }
}
