<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman lengkapi profil (wajib sebelum akses fitur).
     */
    public function complete()
    {
        return view('auth.profile-complete');
    }

    /**
     * Simpan data profil lengkap.
     */
    public function completeUpdate(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nim_nip' => ['required', 'string', 'max:50'],
            'institution' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil dilengkapi. Anda sekarang dapat mengakses semua fitur.');
    }

    /**
     * Tampilkan halaman profil & ubah kata sandi.
     */
    public function show()
    {
        return view('profile.index');
    }

    /**
     * Perbarui nama & email akun.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nim_nip' => ['required', 'string', 'max:50'],
            'institution' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        unset($validated['current_password']);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Ubah kata sandi akun.
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
