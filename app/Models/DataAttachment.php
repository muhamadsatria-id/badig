<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use App\Services\SecureMediaStorage;
use Illuminate\Database\Eloquent\Model;

class DataAttachment extends Model
{
    use BelongsToUnit;

    protected $fillable = [
        'unit',
        'unit_id',
        'record_table',
        'record_id',
        'original_name',
        'stored_name',
        'file_path',
        'extension',
        'mime_type',
        'file_size',
        'uploaded_by',
        'attachment_role',
        'description',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower((string) $this->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public function getIsViewableAttribute(): bool
    {
        return $this->is_image || strtolower((string) $this->extension) === 'pdf';
    }

    public function getHumanFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    public function existsOnDisk(): bool
    {
        return app(SecureMediaStorage::class)->exists($this->file_path);
    }
}
