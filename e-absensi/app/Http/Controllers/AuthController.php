<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Jika sudah login, langsung lempar ke dashboard sesuai role
        if (session('role') === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if (session('role') === 'karyawan') {
            return redirect()->route('user.home');
        }

        return view('auth.login', ['title' => 'Login']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip'      => 'required',
            'password' => 'required',
        ]);

        $karyawan = Karyawan::where('nip', $request->nip)->first();

        if ($karyawan && Hash::check($request->password, $karyawan->password)) {
            // Regenerasi session ID untuk mencegah session fixation
            $request->session()->regenerate();

            // Simpan data ringan ke session
            session([
                'id_karyawan' => $karyawan->id_karyawan,
                'nama'        => $karyawan->nama,
                'role'        => $karyawan->role,
            ]);

            return $karyawan->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.home');
        }

        return back()->withErrors(['login' => 'NIP atau password salah']);
    }

    public function logout(Request $request)
    {
        // Hapus semua data sesi
        $request->session()->invalidate();
        // Regenerasi CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
