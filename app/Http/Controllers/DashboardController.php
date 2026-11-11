<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DataAttachment;
use App\Models\SystemBackup;
use App\Models\Unit;
use App\Models\Unit1Ideology;
use App\Models\Unit2Economy;
use App\Models\Unit3Sosbud;
use App\Models\Unit4Security;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        abort_unless($user->hasValidRoleUnitAssignment(), 403, 'Konfigurasi role dan unit akun tidak valid.');

        $models = [
            1 => ['slug' => 'unit1', 'model' => Unit1Ideology::class],
            2 => ['slug' => 'unit2', 'model' => Unit2Economy::class],
            3 => ['slug' => 'unit3', 'model' => Unit3Sosbud::class],
            4 => ['slug' => 'unit4', 'model' => Unit4Security::class],
        ];

        $counts = [
            'unit1' => 0,
            'unit2' => 0,
            'unit3' => 0,
            'unit4' => 0,
            'users' => $user->isSuperAdmin() ? User::count() : 1,
            'audit' => $user->isSuperAdmin()
                ? AuditLog::count()
                : AuditLog::where('user_id', $user->id)->count(),
            'backup' => $user->isSuperAdmin() ? SystemBackup::count() : 0,
        ];

        foreach ($models as $unitId => $definition) {
            if ($user->canAccessUnitId($unitId)) {
                $counts[$definition['slug']] = $definition['model']::count();
            }
        }

        $unit4Trend = collect();
        if ($user->canAccessUnitId(4)) {
            $unit4Trend = Unit4Security::select('kategori', DB::raw('COUNT(*) as total'))
                ->groupBy('kategori')
                ->orderByDesc('total')
                ->pluck('total', 'kategori');
        }

        $unit2Pie = collect();
        if ($user->canAccessUnitId(2)) {
            $unit2Pie = Unit2Economy::select('kategori', DB::raw('COUNT(*) as total'))
                ->groupBy('kategori')
                ->orderByDesc('total')
                ->pluck('total', 'kategori');
        }

        $points = collect();
        if ($user->canAccessUnitId(1)) {
            $points = $points->merge(Unit1Ideology::with(['district', 'village'])->whereNotNull('koordinat_lat')->whereNotNull('koordinat_lng')->latest()->limit(2000)->get()->map(fn ($x) => $this->mapPoint(
                'unit1', 'Unit 1', $x, $x->nama_subjek, $x->catatan_rawan,
                trim(($x->village?->name ? $x->village->name.', ' : '').($x->district?->name ?: ''))
            )));
        }
        if ($user->canAccessUnitId(2)) {
            $points = $points->merge(Unit2Economy::with(['district', 'village'])->whereNotNull('koordinat_lat')->whereNotNull('koordinat_lng')->latest()->limit(2000)->get()->map(fn ($x) => $this->mapPoint(
                'unit2', 'Unit 2', $x, $x->nama_aset, 'Kapasitas: '.($x->kapasitas ?: '-'),
                $x->alamat ?: trim(($x->village?->name ? $x->village->name.', ' : '').($x->district?->name ?: ''))
            )));
        }
        if ($user->canAccessUnitId(3)) {
            $points = $points->merge(Unit3Sosbud::with(['district', 'village'])->whereNotNull('koordinat_lat')->whereNotNull('koordinat_lng')->latest()->limit(2000)->get()->map(fn ($x) => $this->mapPoint(
                'unit3', 'Unit 3', $x, $x->nama_organisasi, $x->catatan_aktivitas,
                trim(($x->village?->name ? $x->village->name.', ' : '').($x->district?->name ?: ''))
            )));
        }
        if ($user->canAccessUnitId(4)) {
            $points = $points->merge(Unit4Security::with(['district', 'village'])->whereNotNull('koordinat_lat')->whereNotNull('koordinat_lng')->latest()->limit(2000)->get()->map(fn ($x) => $this->mapPoint(
                'unit4', 'Unit 4', $x, $x->nama_kasus, trim(($x->modus ?: '').($x->status_kasus ? ' · Status: '.$x->status_kasus : '')),
                trim(($x->village?->name ? $x->village->name.', ' : '').($x->district?->name ?: ''))
            )));
        }

        $accessibleUnitManagers = Unit::active()->orderBy('id')->get()
            ->filter(fn ($unit) => $user->canAccessUnitId((int) $unit->id))
            ->map(function ($unit) {
                $slug = $unit->slug ?: 'unit'.$unit->id;
                $query = DataAttachment::query()
                    ->where('unit', $slug)
                    ->where('unit_id', (int) $unit->id);

                return [
                    'id' => (int) $unit->id,
                    'slug' => $slug,
                    'name' => $unit->nama_unit,
                    'files' => (clone $query)->count(),
                    'size' => (int) ((clone $query)->sum('file_size') ?: 0),
                    'latest' => (clone $query)->latest()->value('created_at'),
                    'url' => route('units.file-manager', $slug),
                ];
            })->values();

        $recentLogs = $user->isSuperAdmin()
            ? AuditLog::latest()->limit(8)->get()
            : AuditLog::where('user_id', $user->id)->latest()->limit(8)->get();

        return view('dashboard.index', compact('counts', 'unit4Trend', 'unit2Pie', 'points', 'recentLogs', 'accessibleUnitManagers'));
    }

    private function mapPoint(string $unit, string $unitName, object $item, ?string $title, ?string $description, ?string $address): array
    {
        return [
            'lat' => (float) $item->koordinat_lat,
            'lng' => (float) $item->koordinat_lng,
            'unit' => $unitName,
            'title' => $title ?: 'Rekam data #'.$item->id,
            'label' => $unitName.' - '.($title ?: '#'.$item->id),
            'kategori' => $item->kategori ?: '-',
            'description' => $description ?: 'Tidak ada keterangan tambahan.',
            'address' => $address ?: '-',
            'date' => optional($item->tanggal_pembuatan)->format('d/m/Y') ?: '-',
            'detail_url' => route('units.show', [$unit, $item->id]),
        ];
    }
}
