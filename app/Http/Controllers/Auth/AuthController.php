<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     * Jika sudah login, langsung redirect sesuai role.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            if ($resp = $this->redirectByRole(Auth::user()->role ?? null)) {
                return $resp;
            }

            // Role tidak dikenali → logout paksa & kembali ke login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        // Belum login → tampilkan view login
        return view('auth.login', ['title' => 'Login']);
    }

    /**
     * Proses login.
     */
    public function login(Request $request)
    {
        // Validasi input dasar (pesan dalam bahasa Indonesia)
        $credentials = $request->validate(
            [
                'nik'      => ['required', 'string', 'max:100'],
                'password' => ['required', 'string'],
            ],
            [
                'nik.required'      => 'NIK wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        // Ingat saya (checkbox)
        $remember = $request->boolean('remember');

        // Normalisasi NIK: hapus spasi agar konsisten
        $nik = preg_replace('/\s+/', '', $credentials['nik']);

        // Coba autentikasi dengan field 'nik' + 'password'
        if (Auth::attempt(['nik' => $nik, 'password' => $credentials['password']], $remember)) {
            // Regenerasi session untuk mencegah session fixation
            $request->session()->regenerate();

            // Redirect berdasarkan role
            if ($resp = $this->redirectByRole(Auth::user()->role ?? null)) {
                return $resp;
            }

            // Jika role kosong/tidak valid → logout paksa dan beri pesan error
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['auth' => 'Akun Anda belum memiliki role yang valid. Hubungi admin.']);
        }

        // Jika kredensial salah → lempar ValidationException dengan error global 'auth'
        throw ValidationException::withMessages([
            'auth' => 'NIK atau password salah.',
        ])->redirectTo(route('login'));
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        // Hapus autentikasi & invalidasi sesi
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login
        return redirect()->route('login');
    }

    /**
     * Helper: arahkan sesuai role.
     * - admin, co-admin → admin.mode
     * - karyawan        → user.home
     * - lainnya         → null (biar caller yang tentukan fallback)
     */
    private function redirectByRole(?string $role): ?RedirectResponse
    {
        return match ($role) {
            'admin', 'co-admin' => redirect()->route('admin.mode'),
            'karyawan'          => redirect()->route('user.home'),
            default             => null,
        };
    }
}
