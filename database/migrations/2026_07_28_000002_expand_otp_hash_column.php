<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'otp_code')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('otp_code', 64)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Do not shrink the column because existing OTP hashes would be truncated.
    }
};
