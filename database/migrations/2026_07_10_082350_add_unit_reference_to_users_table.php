<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || Schema::hasColumn('users', 'unit_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // units.id pada skema BADIG memakai tinyIncrements.
            // Hindari foreignId() karena menghasilkan UNSIGNED BIGINT dan
            // tidak kompatibel dengan units.id pada MySQL/MariaDB.
            $table->unsignedTinyInteger('unit_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        // Shared column; do not drop from a compatibility migration.
    }
};
