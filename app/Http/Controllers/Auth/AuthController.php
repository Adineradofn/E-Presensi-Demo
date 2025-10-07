<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Tampilkan form login
    public function showLoginForm(Request $request)
    {
        // Jika sudah login, arahkan sesuai role (admin/karyawan)
        if (Auth::check()) { // <- sudah diperbaiki (hapus satu ')')
            $role = (string) (Auth::user()->role ?? '');

            // Admin ke dashboard admin
            if ($role === 'admin')   return redirect()->route('admin.mode');
            // Karyawan ke beranda user
            if ($role === 'karyawan') return redirect()->route('user.home');

            // Jika role tidak dikenali → paksa logout & kembali ke login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        // Belum login → tampilkan view login
        return view('auth.login', ['title' => 'Login']);
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input dasar (pesan dalam bahasa Indonesia)
        $credentials = $request->validate(
            [
                'nik'      => ['required','string','max:100'],
                'password' => ['required','string'],
            ],
            [
                'nik.required'      => 'NIK wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]
        );

        // Ingat saya (checkbox)
        $remember = $request->boolean('remember');

        // (Opsional) Normalisasi NIK: hapus spasi agar konsisten
        $nik = preg_replace('/\s+/', '', $credentials['nik']);

        // Coba autentikasi dengan field 'nik' + 'password'
        if (Auth::attempt(['nik' => $nik, 'password' => $credentials['password']], $remember)) {
            // Regenerasi session untuk mencegah session fixation
            $request->session()->regenerate();

            // Arahkan berdasarkan role
            $role = (string) (Auth::user()->role ?? '');
            if ($role === 'admin')   return redirect()->route('admin.mode');
            if ($role === 'karyawan') return redirect()->route('user.home');

            // Jika role kosong/tidak valid → logout paksa dan beri pesan error
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['auth' => 'Akun Anda belum memiliki role yang valid. Hubungi admin.']);
        }

        // Jika kredensial salah → lempar ValidationException dengan error global 'auth'
        throw ValidationException::withMessages([
            'auth' => 'NIK atau password salah.',
        ])->redirectTo(route('login'));

        // Alternatif tanpa exception:
        // return back()->withErrors(['auth' => 'NIK atau password salah.'])->onlyInput('nik');
    }

    // Proses logout
    public function logout(Request $request)
    {
        // Hapus autentikasi & invalidasi sesi
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login
        return redirect()->route('login');
    }
}
