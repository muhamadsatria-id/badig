<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\SecureMediaStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()->loadMissing('unit')]);
    }

    public function photo(SecureMediaStorage $storage)
    {
        $user = auth()->user();
        abort_unless($user?->photo, 404, 'Foto profil tidak ditemukan.');

        return $storage->inline($user->photo);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);
        $sensitiveChange = mb_strtolower(trim((string) $request->input('email'))) !== mb_strtolower((string) $user->email)
            || filled($request->input('password'));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nrp' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'current_password' => [Rule::requiredIf($sensitiveChange), 'nullable', 'current_password:web'],
            'password' => ['nullable', 'string', 'ascii', 'max:72', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $emailExists = $user->newQuery()
            ->whereKeyNot($user->getKey())
            ->whereRaw('LOWER(email) = ?', [$data['email']])
            ->exists();

        if ($emailExists) {
            throw ValidationException::withMessages([
                'email' => 'Alamat email sudah digunakan.',
            ]);
        }

        $user->fill([
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            'nrp' => isset($data['nrp']) ? trim($data['nrp']) : null,
            'phone' => isset($data['phone']) ? trim($data['phone']) : null,
            'address' => isset($data['address']) ? trim($data['address']) : null,
        ]);

        $storage = app(SecureMediaStorage::class);
        $oldPhoto = $user->photo;
        $newPhoto = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $extension = strtolower($file->extension() ?: 'jpg');
            $newPhoto = $storage->storeAs(
                'badig/profile-photos',
                Str::uuid().'.'.$extension,
                $file
            );
            $user->photo = $newPhoto;
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        try {
            $user->save();
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                $storage->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto && $oldPhoto) {
            $storage->delete($oldPhoto);
        }

        if ($sensitiveChange) {
            $request->session()->regenerate();

            if (config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'))) {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $request->session()->getId())
                    ->delete();
            }
        }

        AuditLog::record('UPDATE_PROFILE', 'Pengguna memperbarui profil akun sendiri.');

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
