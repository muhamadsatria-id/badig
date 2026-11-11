<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitManagementController extends Controller
{
    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengelola identitas unit.');
    }

    private function authorizeFixedUnit(Unit $unit): void
    {
        abort_unless(in_array((int) $unit->id, [1, 2, 3, 4], true), 404);
    }

    public function index()
    {
        $this->authorizeSuperAdmin();
        $units = Unit::withCount(['users', 'databaseOrang'])
            ->whereBetween('id', [1, 4])
            ->orderBy('id')
            ->get();

        return view('super-admin.units.index', compact('units'));
    }

    public function edit(Unit $unit)
    {
        $this->authorizeSuperAdmin();
        $this->authorizeFixedUnit($unit);

        return view('super-admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $this->authorizeSuperAdmin();
        $this->authorizeFixedUnit($unit);

        $data = $request->validate([
            'nama_unit' => ['required', 'string', 'max:255', Rule::unique('units', 'nama_unit')->ignore($unit->id)],
            'kode' => ['required', 'string', 'max:20', Rule::unique('units', 'kode')->ignore($unit->id)],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['kode_unit'] = $data['kode'];
        $data['slug'] = 'unit'.$unit->id;
        $data['is_active'] = true;

        $unit->update($data);
        AuditLog::record('UPDATE_UNIT', 'Memperbarui identitas Unit '.$unit->id.'.');

        return redirect()->route('super-admin.units.index')->with('success', 'Identitas unit berhasil diperbarui.');
    }
}
