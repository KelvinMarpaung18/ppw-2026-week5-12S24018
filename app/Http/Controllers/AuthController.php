<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login pustakawan.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Otentikasi pustakawan, regenerasi session ID (anti-session fixation),
     * simpan timestamp login ke session, dan antrekan HTTP Cookie HttpOnly.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // WAJIB: Cegah Session Fixation!
            $request->session()->regenerate();

            // Simpan timestamp login di Session server
            session([
                'login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB',
            ]);

            // Simpan cookie email terakhir (durasi 7 hari = 10080 menit) dengan atribut HttpOnly
            Cookie::queue('del_last_user_email', $request->email, 10080);

            return redirect()
                ->intended(route('buku.index'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '! Sesi Anda telah aktif.');
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Kredensial login tidak valid. Silakan periksa email dan kata sandi Anda.');
    }

    /**
     * Logout pustakawan, invalidate session, dan regenerasi token CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate sesi saat ini dan hapus datanya
        $request->session()->invalidate();

        // Regenerasi token CSRF baru
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Sesi server telah dihapus dengan aman.');
    }
}
