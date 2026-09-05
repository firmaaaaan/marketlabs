<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login menggunakan username.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $user = User::where('username', $request->username)->first();

        // Timing-safe: always run Hash::check to prevent user enumeration via response time.
        $valid = $user && Hash::check($request->password, $user->password);

        if (! $valid) {
            // Run a dummy hash to normalize timing when user does not exist.
            if (! $user) {
                Hash::check($request->password, '$2y$12$000000000000000000000000000000000000000000000000000000X');
            }

            return back()->withErrors([
                'username' => 'Username atau kata sandi yang Anda masukkan salah.',
            ])->onlyInput('username');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLogger::log($user, 'login', 'masuk ke sistem');

        return $user->isAdmin()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('home'));
    }

    /**
     * Tampilkan form registrasi.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['username'].'@pending.local',
            'participant_code' => User::generateParticipantCode(),
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // User baru berperan 'user' → diarahkan ke beranda.
        return redirect()->route('home');
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        Auth::logout();

        if ($user) {
            ActivityLogger::log($user, 'logout', 'keluar dari sistem');
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
