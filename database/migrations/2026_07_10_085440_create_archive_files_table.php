<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('archive_files')) {
            return;
        }

        Schema::create('archive_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('unit_id')->nullable()->index();
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('extension', 30)->nullable()->index();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archive_files');
    }
};
