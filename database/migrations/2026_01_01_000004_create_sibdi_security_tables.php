<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action', 120);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->string('nama_file');
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->string('status', 100)->default('PENDING');
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        try {
            DB::unprepared("CREATE TRIGGER trg_audit_logs_no_update BEFORE UPDATE ON audit_logs FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Audit logs cannot be updated'; END");
            DB::unprepared("CREATE TRIGGER trg_audit_logs_no_delete BEFORE DELETE ON audit_logs FOR EACH ROW BEGIN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Audit logs cannot be deleted'; END");
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function down(): void
    {
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_logs_no_update');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_logs_no_delete');
        } catch (Throwable $e) {
            report($e);
        }
        Schema::dropIfExists('system_backups');
        Schema::dropIfExists('audit_logs');
    }
};
