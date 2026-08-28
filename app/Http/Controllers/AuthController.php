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
     * Proses login menggunakan NIM/NIK/NIP.
     */
    public function login(Request $request)
    {
        $request->validate([
            'nim_nip' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $user = User::where('nim_nip', $request->nim_nip)->first();

        // Timing-safe: always run Hash::check to prevent user enumeration via response time.
        $valid = $user && Hash::check($request->password, $user->password);

        if (! $valid) {
            // Run a dummy hash to normalize timing when user does not exist.
            if (! $user) {
                Hash::check($request->password, '$2y$12$000000000000000000000000000000000000000000000000000000X');
            }

            return back()->withErrors([
                'nim_nip' => 'NIM/NIK/NIP atau kata sandi yang Anda masukkan salah.',
            ])->onlyInput('nim_nip');
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'nim_nip' => ['required', 'string', 'max:255', 'unique:users,nim_nip'],
            'institution' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'nim_nip' => $validated['nim_nip'],
            'institution' => $validated['institution'],
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
