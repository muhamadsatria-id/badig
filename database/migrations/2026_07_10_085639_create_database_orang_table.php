<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('database_orang')) {
            return;
        }

        Schema::create('database_orang', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('unit_id')->nullable()->index();
            $table->string('nama')->index();
            $table->string('nik', 40)->nullable()->index();
            $table->string('nrp', 80)->nullable()->index();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['unit_id', 'nama']);
        });
    }

    public function down(): void
    {
        // Main table is owned by an earlier migration; do not drop here.
    }
};
