<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'action', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public static function record(string $action, ?string $description = null, ?User $user = null): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        try {
            self::create([
                'user_id' => $user?->id ?? auth()->id(),
                'ip_address' => request()?->ip(),
                'user_agent' => substr((string) request()?->userAgent(), 0, 1000),
                'action' => $action,
                'description' => $description,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
