<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->tinyIncrements('id');
                $table->string('nama_unit')->nullable()->unique();
                $table->string('kode', 20)->nullable()->unique();
                $table->string('kode_unit', 50)->nullable()->unique();
                $table->string('slug', 80)->nullable()->unique();
                $table->text('deskripsi')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // Data master dinormalisasi oleh migration upgrade final.
        // Migration ini sengaja tidak melakukan insert/update agar tidak
        // menimbulkan benturan unique index pada instalasi lama.
    }

    public function down(): void
    {
        // Table units shared by earlier migrations; never drop here.
    }
};
