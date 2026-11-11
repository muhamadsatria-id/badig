<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DatabaseOrang;
use App\Models\SystemBackup;
use App\Models\Unit;
use App\Models\Unit1Ideology;
use App\Models\Unit2Economy;
use App\Models\Unit3Sosbud;
use App\Models\Unit4Security;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class SuperAdminController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $stats = [
            'users' => User::count(),
            'super_admins' => User::where('role_id', User::ROLE_SUPER_ADMIN)->count(),
            'audit' => AuditLog::count(),
            'backups' => SystemBackup::count(),
            'unit1' => Unit1Ideology::count(),
            'unit2' => Unit2Economy::count(),
            'unit3' => Unit3Sosbud::count(),
            'unit4' => Unit4Security::count(),
            'units' => Schema::hasTable('units') ? Unit::count() : 0,
            'database_orang' => Schema::hasTable('database_orang') ? DatabaseOrang::count() : 0,
        ];

        $superLogs = AuditLog::with('user')
            ->whereHas('user', fn ($query) => $query->where('role_id', User::ROLE_SUPER_ADMIN))
            ->latest()
            ->limit(20)
            ->get();

        $latestUsers = User::with('unit')->latest()->limit(12)->get();
        $units = Schema::hasTable('units') ? Unit::withCount('users')->orderBy('id')->get() : collect();

        return view('super-admin.index', compact('stats', 'superLogs', 'latestUsers', 'units'));
    }
}
