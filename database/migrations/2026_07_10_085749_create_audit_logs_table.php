<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 100)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('action', 120);
                $table->string('module', 120)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (! Schema::hasColumn('audit_logs', 'module')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('module', 120)->nullable()->after('action');
            });
        }

        if (! Schema::hasColumn('audit_logs', 'user_agent')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->text('user_agent')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Audit table is owned by the base security migration.
    }
};
