<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function common(Blueprint $table): void
    {
        $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('koordinat_lat', 11, 8)->nullable();
        $table->decimal('koordinat_lng', 11, 8)->nullable();
        $table->enum('status_laporan', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
        $table->text('catatan_validasi')->nullable();
        $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    }

    public function up(): void
    {
        Schema::create('unit1_ideology', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100);
            $table->string('nama_subjek');
            $this->common($table);
            $table->unsignedInteger('pengikut')->nullable();
            $table->string('status_legal', 100)->nullable();
            $table->text('catatan_rawan')->nullable();
        });

        Schema::create('unit2_economy', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100);
            $table->string('nama_aset');
            $this->common($table);
            $table->string('alamat', 500);
            $table->string('kapasitas')->nullable();
        });

        Schema::create('unit3_sosbud', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100);
            $table->string('nama_organisasi');
            $table->string('pimpinan')->nullable();
            $this->common($table);
            $table->unsignedInteger('total_anggota')->nullable();
            $table->string('legalitas', 100)->nullable();
            $table->text('catatan_aktivitas')->nullable();
        });

        Schema::create('unit4_security_crim', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 100);
            $table->string('nama_kasus');
            $this->common($table);
            $table->text('modus')->nullable();
            $table->string('status_kasus', 100);
            $table->longText('data_enkripsi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit4_security_crim');
        Schema::dropIfExists('unit3_sosbud');
        Schema::dropIfExists('unit2_economy');
        Schema::dropIfExists('unit1_ideology');
    }
};
