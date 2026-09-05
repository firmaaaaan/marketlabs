<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan form permintaan tautan reset.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi ke email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        Password::sendResetLink($request->only('email'));

        // Pesan generik agar tidak terjadi enumerasi user.
        return back()->with('status', 'Jika email terdaftar, tautan reset kata sandi telah dikirim ke email Anda.');
    }

    /**
     * Tampilkan form atur ulang kata sandi.
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'request' => $request]);
    }

    /**
     * Proses atur ulang kata sandi.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();

                // Paksa logout dari semua perangkat setelah kata sandi diubah.
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Invalidate current session if user is logged in.
            if (auth()->check()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')
                ->with('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.');
        }

        return back()->withErrors([
            'email' => 'Tautan reset kata sandi tidak valid atau telah kedaluwarsa. Silakan ulangi permintaan reset.',
        ])->withInput();
    }
}
